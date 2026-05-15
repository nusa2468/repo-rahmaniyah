<?php

namespace App\Controllers\Database;

use App\Controllers\BaseController;
// Pastikan library phpoffice/phpspreadsheet sudah terinstall via composer
// composer require phpoffice/phpspreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

class DashboardController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Halaman Utama Dashboard Database
     */
    public function index()
    {
        // Estimasi Ukuran Database
        $size = 0;
        try {
            $query = $this->db->query("SELECT table_schema AS 'Database', 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size' 
                FROM information_schema.TABLES 
                WHERE table_schema = '" . $this->db->database . "' 
                GROUP BY table_schema");
            $row = $query->getRow();
            $size = $row ? $row->Size : 0;
        } catch (\Exception $e) { }

        $data = [
            'title'    => 'Database Maintenance',
            'db_name'  => $this->db->database,
            'db_size'  => $size,
            'platform' => $this->db->getPlatform(),
            'version'  => $this->db->getVersion(),
            'tables'   => $this->db->listTables()
        ];

        return view('database/dashboard', $data);
    }

    /**
     * Proses Backup Database (Download .sql)
     */
    public function backup()
    {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Manual Dump Generator (Safe for Shared Hosting)
        $tables = $this->db->listTables();
        $sql = "-- ERP Sekolah Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Structure
            $createTable = $this->db->query("SHOW CREATE TABLE `$table`")->getRowArray();
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $createTable['Create Table'] . ";\n\n";

            // Data
            $rows = $this->db->table($table)->get()->getResultArray();
            foreach ($rows as $row) {
                $values = array_map(function ($value) {
                    if ($value === null) return "NULL";
                    return "'" . $this->db->escapeString($value) . "'";
                }, $row);
                $sql .= "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $this->response->download($filename, $sql);
    }

    /**
     * Proses Restore Database (Upload .sql)
     */
    public function restore()
    {
        $file = $this->request->getFile('backup_file');

        if ($file && $file->isValid() && $file->getExtension() === 'sql') {
            $sqlContent = file_get_contents($file->getTempName());
            
            // Eksekusi query
            $queries = explode(';', $sqlContent);
            
            $this->db->transStart();
            $this->db->query('SET FOREIGN_KEY_CHECKS=0');
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    $this->db->query($query);
                }
            }
            $this->db->query('SET FOREIGN_KEY_CHECKS=1');
            $this->db->transComplete();

            if ($this->db->transStatus() === FALSE) {
                return redirect()->back()->with('error', 'Gagal restore database. Terjadi kesalahan query.');
            }

            return redirect()->back()->with('success', 'Database berhasil dipulihkan dari file backup.');
        }

        return redirect()->back()->with('error', 'File tidak valid. Harap upload file .sql');
    }

    /**
     * Proses Import Data Massal (Excel/CSV)
     * Target: Siswa, Pegawai, Mata Pelajaran
     */
    public function import()
    {
        $targetTable = $this->request->getPost('target_table');
        $file = $this->request->getFile('import_file');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File import tidak valid.');
        }

        try {
            // Menggunakan PHPSpreadsheet untuk membaca file
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            
            // Hapus baris pertama (Header)
            array_shift($sheetData);

            $insertData = [];
            $count = 0;
            $now = date('Y-m-d H:i:s');

            foreach ($sheetData as $row) {
                // Skip baris kosong
                if (empty(array_filter($row))) continue;

                $data = [];
                // Mapping berdasarkan tabel
                if ($targetTable === 'siswa') {
                    // Format Excel: A:NIS, B:Nama, C:JK(L/P), D:TptLahir, E:TglLahir(Y-m-d), F:Alamat
                    $data = [
                        'nis'           => $row['A'],
                        'nama_lengkap'  => $row['B'],
                        'jenis_kelamin' => strtoupper($row['C']),
                        'tempat_lahir'  => $row['D'],
                        'tanggal_lahir' => $row['E'], // Pastikan format YYYY-MM-DD
                        'alamat'        => $row['F'],
                        'status'        => 'aktif',
                        'created_at'    => $now,
                        'updated_at'    => $now
                    ];
                } elseif ($targetTable === 'pegawai') {
                    // Format Excel: A:NIP, B:Nama, C:Jabatan, D:NoHP
                    $data = [
                        'nip'            => $row['A'],
                        'nama_lengkap'   => $row['B'],
                        'jabatan'        => $row['C'],
                        'no_telepon'     => $row['D'],
                        'status_pegawai' => 'aktif',
                        'created_at'     => $now,
                        'updated_at'     => $now
                    ];
                } elseif ($targetTable === 'mata_pelajaran') {
                    // Format Excel: A:Kode, B:Nama Mapel, C:Kelompok, D:KKM
                    $data = [
                        'kode_mapel' => $row['A'],
                        'nama_mapel' => $row['B'],
                        'kelompok'   => $row['C'],
                        'kkm'        => (int)$row['D'],
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }

                if (!empty($data)) {
                    $insertData[] = $data;
                    $count++;
                }
            }

            if (!empty($insertData)) {
                // Insert Batch dengan Ignore (agar duplikat tidak error)
                $this->db->table($targetTable)->ignore(true)->insertBatch($insertData);
                return redirect()->back()->with('success', "Berhasil mengimport $count data ke tabel $targetTable.");
            }

            return redirect()->back()->with('error', 'Data kosong atau format tidak dikenali.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }

    /**
     * Download Template Import (CSV Sederhana)
     */
    public function downloadTemplate($type)
    {
        $filename = "template_$type.csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $fp = fopen('php://output', 'wb');
        
        if ($type == 'siswa') {
            fputcsv($fp, ['NIS', 'NAMA_LENGKAP', 'JK', 'TEMPAT_LAHIR', 'TGL_LAHIR', 'ALAMAT']);
            fputcsv($fp, ['1001', 'Contoh Siswa', 'L', 'Jakarta', '2010-01-01', 'Jl. Merdeka']);
        } elseif ($type == 'pegawai') {
            fputcsv($fp, ['NIP', 'NAMA_LENGKAP', 'JABATAN', 'NO_TELEPON']);
            fputcsv($fp, ['19800101', 'Contoh Guru', 'Guru Matematika', '08123456789']);
        } elseif ($type == 'mata_pelajaran') {
            fputcsv($fp, ['KODE_MAPEL', 'NAMA_MAPEL', 'KELOMPOK', 'KKM']);
            fputcsv($fp, ['MAT-01', 'Matematika', 'A', '75']);
        }
        
        fclose($fp);
        exit;
    }
}