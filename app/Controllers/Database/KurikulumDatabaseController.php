<?php

namespace App\Controllers\Database;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class KurikulumDatabaseController extends BaseController
{
    protected $db;
    
    // UPDATE: Menambahkan Header 'SEMESTER' setelah 'TINGKAT'
    private $headers = [
        'KODE JENJANG', 'KODE KURIKULUM (LOOKUP)', 'KODE MAPEL (KEY)', 'NAMA MAPEL', 'KELOMPOK (A/B/C)', 
        'TINGKAT', 
        'SEMESTER', // Kolom Baru
        'STATUS (AKTIF)', 'JUMLAH JP',
        'BOBOT TUGAS', 'BOBOT UTS', 'BOBOT UAS', 'BOBOT ABSENSI'
    ];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function template()
    {
        $filename = "template_mapel_kurikulum.csv";
        header('Content-Type: text/csv'); 
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $fp = fopen('php://output', 'wb');
        
        fputcsv($fp, $this->headers);
        
        // UPDATE: Contoh data dengan Tingkat & Semester
        fputcsv($fp, ['SMA', 'MERDEKA', 'MTK-W', 'MATEMATIKA WAJIB', 'A', '10', 'Ganjil', 'AKTIF', '4', '0.3', '0.3', '0.4', '0']);
        fputcsv($fp, ['SMA', 'K13', 'PAI', 'PENDIDIKAN AGAMA', 'A', '11', 'Genap', 'AKTIF', '3', '0.3', '0.3', '0.4', '0']);
        
        fclose($fp); 
        exit;
    }

    public function import()
    {
        ini_set('memory_limit', '-1'); 
        set_time_limit(0);
        
        $file = $this->request->getFile('file_excel');
        
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid atau belum diunggah.');
        }

        $now = date('Y-m-d H:i:s');
        $ext = $file->getClientExtension();

        try {
            if ('xls' == $ext) {
                $reader = new Xls();
            } elseif ('xlsx' == $ext) {
                $reader = new Xlsx();
            } else {
                $reader = new Csv();
                $reader->setDelimiter(',');
            }

            $spreadsheet = $reader->load($file->getTempName());
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            
            if (count($sheetData) > 0) array_shift($sheetData);

            $stats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0];
            
            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

            foreach ($sheetData as $row) {
                if (empty(trim($row['C'] ?? '')) || empty(trim($row['D'] ?? ''))) { 
                    $stats['skipped']++; 
                    continue; 
                }

                $this->db->transBegin();
                try {
                    $kurikulumKode = trim($row['B'] ?? '');
                    $jenjang = strtoupper(trim($row['A'] ?? 'GLOBAL')); 
                    $kurikulumId = null;

                    if ($kurikulumKode) {
                        $findKur = $this->db->table('kurikulum')
                            ->where('kode_kurikulum', $kurikulumKode)
                            ->get()->getRow();
                        if ($findKur) $kurikulumId = $findKur->id;
                    }
                    
                    if (!$kurikulumId) {
                         $defKur = $this->db->table('kurikulum')
                            ->where('kode_jenjang', $jenjang)
                            ->where('status', 'aktif')
                            ->orderBy('id', 'DESC')
                            ->get()->getRow();
                         if ($defKur) $kurikulumId = $defKur->id;
                    }

                    // Normalisasi Semester (Ganjil/Genap/Null)
                    $rawSemester = ucfirst(strtolower(trim($row['G'] ?? '')));
                    $validSemester = in_array($rawSemester, ['Ganjil', 'Genap']) ? $rawSemester : null;

                    // UPDATE: Mapping Data (Kolom bergeser mulai dari H)
                    $mapelData = [
                        'kode_jenjang'  => $jenjang, 
                        'kurikulum_id'  => $kurikulumId,
                        'kode_mapel'    => trim($row['C']),
                        'nama_mapel'    => trim($row['D']), 
                        'kelompok'      => strtoupper(trim($row['E'] ?? 'A')),
                        'tingkat'       => (int)($row['F'] ?? 0), 
                        'semester'      => $validSemester, // Kolom Baru: G
                        
                        // Kolom Bergeser:
                        'status'        => strtolower(trim($row['H'] ?? 'aktif')), 
                        'jumlah_jp'     => (int)($row['I'] ?? 2),
                        'bobot_tugas'   => $this->parseDecimal($row['J'] ?? 0), 
                        'bobot_uts'     => $this->parseDecimal($row['K'] ?? 0), 
                        'bobot_uas'     => $this->parseDecimal($row['L'] ?? 0), 
                        'bobot_absensi' => $this->parseDecimal($row['M'] ?? 0),
                        'updated_at'    => $now
                    ];

                    $builder = $this->db->table('mata_pelajaran');
                    $builder->where('kode_mapel', $mapelData['kode_mapel']);
                    
                    if ($kurikulumId) {
                        $builder->where('kurikulum_id', $kurikulumId);
                    } else {
                        $builder->where('kode_jenjang', $jenjang);
                    }
                    
                    $existing = $builder->get()->getRow();

                    if ($existing) { 
                        $this->db->table('mata_pelajaran')->where('id', $existing->id)->update($mapelData); 
                        $stats['updated']++; 
                    } else { 
                        $mapelData['created_at'] = $now; 
                        $this->db->table('mata_pelajaran')->insert($mapelData); 
                        $stats['inserted']++; 
                    }
                    
                    $this->db->transCommit();
                } catch (\Exception $e) { 
                    $this->db->transRollback(); 
                    $stats['skipped']++; 
                }
            }
            
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            return redirect()->back()->with('success', "Import Mapel Selesai. Masuk: {$stats['inserted']}, Update: {$stats['updated']}, Gagal/Skip: {$stats['skipped']}");

        } catch (\Throwable $e) { 
            return redirect()->back()->with('error', 'Gagal Membaca File: ' . $e->getMessage()); 
        }
    }

    public function export()
    {
        $builder = $this->db->table('mata_pelajaran');
        
        // UPDATE: Menambahkan kolom SEMESTER di Select
        $builder->select('
            mata_pelajaran.kode_jenjang as "KODE JENJANG", 
            kurikulum.kode_kurikulum as "KODE KURIKULUM (LOOKUP)",
            mata_pelajaran.kode_mapel as "KODE MAPEL (KEY)", 
            mata_pelajaran.nama_mapel as "NAMA MAPEL",
            mata_pelajaran.kelompok as "KELOMPOK (A/B/C)", 
            mata_pelajaran.tingkat as "TINGKAT",
            mata_pelajaran.semester as "SEMESTER",
            mata_pelajaran.status as "STATUS (AKTIF)",
            mata_pelajaran.jumlah_jp as "JUMLAH JP", 
            mata_pelajaran.bobot_tugas as "BOBOT TUGAS",
            mata_pelajaran.bobot_uts as "BOBOT UTS", 
            mata_pelajaran.bobot_uas as "BOBOT UAS",
            mata_pelajaran.bobot_absensi as "BOBOT ABSENSI"
        ');
        
        $builder->join('kurikulum', 'kurikulum.id = mata_pelajaran.kurikulum_id', 'left');
        $builder->orderBy('mata_pelajaran.kode_jenjang', 'ASC');
        $builder->orderBy('mata_pelajaran.tingkat', 'ASC');
        $builder->orderBy('mata_pelajaran.semester', 'ASC');
        
        $data = $builder->get()->getResultArray();

        $spreadsheet = new Spreadsheet(); 
        $sheet = $spreadsheet->getActiveSheet();
        
        $col = 'A'; 
        foreach ($this->headers as $h) { 
            $sheet->setCellValue($col++.'1', strtoupper($h)); 
            $sheet->getColumnDimension(chr(ord($col)-1))->setAutoSize(true); 
        }
        
        $rowIdx = 2; 
        foreach ($data as $row) { 
            $col = 'A'; 
            foreach ($row as $cell) { 
                $sheet->setCellValue($col++.$rowIdx, $cell); 
            } 
            $rowIdx++; 
        }
        
        $filename = 'export_full_mapel_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); 
        $writer->save('php://output'); 
        exit;
    }
    
    private function parseDecimal($val) {
        if (is_string($val)) {
            $val = str_replace(',', '.', $val);
        }
        return (float)$val;
    }
}