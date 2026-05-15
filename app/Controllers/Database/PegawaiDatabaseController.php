<?php

namespace App\Controllers\Database;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PegawaiDatabaseController extends BaseController
{
    protected $db;
    
    // Header Pegawai (50+ Kolom)
    private $headers = [
        'NIK (KEY)', 'NAMA LENGKAP', 'GELAR DEPAN', 'GELAR BELAKANG', 'JK (L/P)', 'TEMPAT LAHIR', 'TGL LAHIR', 'IBU KANDUNG', 'AGAMA', 'STATUS PERKAWINAN',
        'NUPTK', 'NIP', 'NIPY', 'STATUS KEPEGAWAIAN', 'JENIS PTK', 'TUGAS TAMBAHAN', 'SK PENGANGKATAN', 'TMT PENGANGKATAN', 'SUMBER GAJI', 'PENDIDIKAN TERAKHIR', 'JENIS PEGAWAI', 'STATUS AKTIF', 'KODE JENJANG',
        'EMAIL', 'NO HP', 'ALAMAT JALAN', 'RT', 'RW', 'DUSUN', 'KELURAHAN', 'KECAMATAN', 'KODE POS',
        'RP JENJANG', 'RP NAMA SEKOLAH', 'RP JURUSAN', 'RP THN MASUK', 'RP THN LULUS', 'RP NILAI AKHIR',
        'RK JENIS SK', 'RK NO SK', 'RK TGL SK', 'RK TMT SK', 'RK MASA KERJA THN', 'RK MASA KERJA BLN', 'RK STATUS PEG', 'RK PANGKAT GOL', 'RK JABATAN', 'RK IS AKTIF (1/0)'
    ];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    private function getContext() { return session()->get('kode_jenjang') ?? session()->get('kode_unit'); }
    private function isGlobalAccess(?string $context): bool { $globalScopes = ['GLOBAL', 'YAYASAN', 'ALL', 'ROOT', 'PUSAT']; return empty($context) || in_array(strtoupper($context), $globalScopes); }
    private function parseDate($value) { try { if (is_numeric($value)) return Date::excelToDateTimeObject($value)->format('Y-m-d'); $value = trim($value ?? ''); if (empty($value) || $value == '-') return null; $time = strtotime(str_replace(['/', '.'], '-', $value)); return $time ? date('Y-m-d', $time) : null; } catch (\Exception $e) { return null; } }
    private function getSafeNumeric($value) { $val = trim($value ?? ''); return ($val === '' || !is_numeric($val)) ? null : $val; }

    public function template()
    {
        $filename = "template_full_pegawai.csv";
        header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="' . $filename . '"');
        $fp = fopen('php://output', 'wb');
        fputcsv($fp, $this->headers);
        fputcsv($fp, ['320101...', 'DRS. H. AHMAD FAUZI, M.PD', 'DRS. H.', 'M.PD', 'L', 'BEKASI', '1980-01-01', 'FATIMAH', 'ISLAM', 'KAWIN', '12345678', '19800101...', '001', 'GTY/PTY', 'GURU MAPEL', 'WAKASEK', 'SK-YAYASAN-01', '2010-01-01', 'YAYASAN', 'S2', 'GURU', 'AKTIF', 'SMA', 'ahmad@sekolah.id', '08123456789', 'JL. RAYA NO 1', '01', '05', 'DUSUN A', 'DESA B', 'KEC C', '17510', 'S2', 'UNJ', 'PENDIDIKAN', '2008', '2010', '3.75', 'PENGANGKATAN', 'SK-2024', '2024-01-01', '2024-01-01', '14', '0', 'TETAP', 'III/A', 'GURU MUDA', '1']);
        fclose($fp); exit;
    }

    public function import()
    {
        ini_set('memory_limit', '-1'); set_time_limit(0);
        $file = $this->request->getFile('file_excel');
        if (!$file || !$file->isValid()) return redirect()->back()->with('error', 'File tidak valid.');

        $context  = $this->getContext(); 
        $isGlobal = $this->isGlobalAccess($context); 
        $now = date('Y-m-d H:i:s');

        // PERBAIKAN 1: Pindahkan file sementara agar ekstensi file terbaca oleh PhpSpreadsheet
        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads', $newName);
        $filePath = WRITEPATH . 'uploads/' . $newName;

        try {
            // Karena memiliki ekstensi, PhpSpreadsheet akan otomatis tahu cara membacanya (CSV/XLSX)
            $spreadsheet = IOFactory::load($filePath); 
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            if (count($sheetData) > 0) array_shift($sheetData); // Hapus baris header

            $stats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];
            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

            foreach ($sheetData as $index => $row) {
                // PERBAIKAN 2: Gunakan ?? '' di seluruh mapping agar PHP 8+ tidak error jika ada kolom kosong di ujung file
                $nik = trim($row['A'] ?? '');
                $namaLengkap = trim($row['B'] ?? '');

                if (empty($nik) || empty($namaLengkap)) { 
                    $stats['skipped']++; 
                    continue; 
                }

                $this->db->transBegin();
                try {
                    $finalJenjang = $isGlobal ? (trim($row['W'] ?? '') ?: 'GLOBAL') : $context;
                    
                    // 1. DATA UTAMA
                    $pegawaiData = [
                        'nik'                 => $nik, 
                        'nama_lengkap'        => $namaLengkap, 
                        'gelar_depan'         => trim($row['C'] ?? ''), 
                        'gelar_belakang'      => trim($row['D'] ?? ''),
                        'jenis_kelamin'       => strtoupper(trim($row['E'] ?? '')), 
                        'tempat_lahir'        => trim($row['F'] ?? ''), 
                        'tanggal_lahir'       => $this->parseDate($row['G'] ?? ''),
                        'nama_ibu_kandung'    => trim($row['H'] ?? ''), 
                        'agama'               => trim($row['I'] ?? ''), 
                        'status_perkawinan'   => trim($row['J'] ?? ''),
                        'nuptk'               => trim($row['K'] ?? ''), 
                        'nip'                 => trim($row['L'] ?? ''), 
                        'nipy'                => trim($row['M'] ?? ''), 
                        'status_kepegawaian'  => trim($row['N'] ?? ''),
                        'jenis_ptk'           => trim($row['O'] ?? ''), 
                        'tugas_tambahan'      => trim($row['P'] ?? ''), 
                        'sk_pengangkatan'     => trim($row['Q'] ?? ''), 
                        'tmt_pengangkatan'    => $this->parseDate($row['R'] ?? ''),
                        'sumber_gaji'         => trim($row['S'] ?? ''), 
                        'pendidikan_terakhir' => trim($row['T'] ?? ''), 
                        'jenis_pegawai'       => strtolower(trim($row['U'] ?? '') ?: 'staff'),
                        'status_aktif'        => strtolower(trim($row['V'] ?? '') ?: 'aktif'), 
                        'kode_jenjang'        => $finalJenjang, 
                        'email'               => trim($row['X'] ?? ''), 
                        'no_hp'               => trim($row['Y'] ?? ''),
                        'alamat_jalan'        => trim($row['Z'] ?? ''), 
                        'rt'                  => trim($row['AA'] ?? ''), 
                        'rw'                  => trim($row['AB'] ?? ''), 
                        'nama_dusun'          => trim($row['AC'] ?? ''),
                        'desa_kelurahan'      => trim($row['AD'] ?? ''), 
                        'kecamatan'           => trim($row['AE'] ?? ''), 
                        'kode_pos'            => trim($row['AF'] ?? ''), 
                        'updated_at'          => $now
                    ];

                    $existing = $this->db->table('pegawai')->where('nik', $pegawaiData['nik'])->get()->getRow();
                    if ($existing) { 
                        $this->db->table('pegawai')->where('id', $existing->id)->update($pegawaiData); 
                        $idPegawai = $existing->id; 
                        $stats['updated']++; 
                    } else { 
                        $pegawaiData['created_at'] = $now; 
                        $this->db->table('pegawai')->insert($pegawaiData); 
                        $idPegawai = $this->db->insertID(); 
                        $stats['inserted']++; 
                    }

                    // 2. RIWAYAT PENDIDIKAN
                    if (!empty(trim($row['AG'] ?? ''))) {
                        $pendData = [
                            'id_pegawai'   => $idPegawai, 
                            'jenjang'      => trim($row['AG'] ?? ''), 
                            'nama_sekolah' => trim($row['AH'] ?? ''), 
                            'jurusan'      => trim($row['AI'] ?? ''), 
                            'tahun_masuk'  => $this->getSafeNumeric($row['AJ'] ?? ''), 
                            'tahun_lulus'  => $this->getSafeNumeric($row['AK'] ?? ''), 
                            'nilai_akhir'  => trim($row['AL'] ?? ''), 
                            'updated_at'   => $now
                        ];
                        $existP = $this->db->table('riwayat_pendidikan')->where('id_pegawai', $idPegawai)->where('jenjang', $pendData['jenjang'])->get()->getRow();
                        if ($existP) { 
                            $this->db->table('riwayat_pendidikan')->where('id', $existP->id)->update($pendData); 
                        } else { 
                            $pendData['created_at'] = $now; 
                            $this->db->table('riwayat_pendidikan')->insert($pendData); 
                        }
                    }

                    // 3. RIWAYAT KEPEGAWAIAN
                    if (!empty(trim($row['AM'] ?? ''))) {
                        $rkData = [
                            'id_pegawai'         => $idPegawai, 
                            'jenis_sk'           => trim($row['AM'] ?? ''), 
                            'no_sk'              => trim($row['AN'] ?? ''), 
                            'tanggal_sk'         => $this->parseDate($row['AO'] ?? ''), 
                            'tmt_sk'             => $this->parseDate($row['AP'] ?? ''), 
                            'masa_kerja_tahun'   => $this->getSafeNumeric($row['AQ'] ?? ''), 
                            'masa_kerja_bulan'   => $this->getSafeNumeric($row['AR'] ?? ''), 
                            'status_kepegawaian' => trim($row['AS'] ?? ''), 
                            'pangkat_golongan'   => trim($row['AT'] ?? ''), 
                            'jabatan_fungsional' => trim($row['AU'] ?? ''), 
                            'is_aktif'           => (int)($row['AV'] ?? 0), 
                            'updated_at'         => $now
                        ];
                        $existR = $this->db->table('riwayat_kepegawaian')->where('id_pegawai', $idPegawai)->where('no_sk', $rkData['no_sk'])->get()->getRow();
                        if ($existR) { 
                            $this->db->table('riwayat_kepegawaian')->where('id', $existR->id)->update($rkData); 
                        } else { 
                            $rkData['created_at'] = $now; 
                            $this->db->table('riwayat_kepegawaian')->insert($rkData); 
                        }
                    }
                    
                    $this->db->transCommit();
                } catch (\Exception $e) { 
                    $this->db->transRollback(); 
                    $stats['skipped']++; 
                    $stats['errors'][] = "Baris " . ($index + 1) . ": " . $e->getMessage();
                }
            }
            
            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            
            // Hapus file sementara setelah selesai
            if (file_exists($filePath)) unlink($filePath);
            
            // Tampilkan pesan
            $msg = "Import Pegawai Selesai. Ditambahkan: {$stats['inserted']}, Diperbarui: {$stats['updated']}.";
            if ($stats['skipped'] > 0) {
                $msg .= " Dilewati: {$stats['skipped']} data.";
                log_message('error', 'Pegawai Import Errors: ' . print_r($stats['errors'], true));
            }
            
            return redirect()->back()->with('success', $msg);

        } catch (\Throwable $e) { 
            if (isset($filePath) && file_exists($filePath)) unlink($filePath);
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage()); 
        }
    }

    public function export()
    {
        $context = $this->getContext(); $isGlobal = $this->isGlobalAccess($context);
        $builder = $this->db->table('pegawai');
        if (!$isGlobal) $builder->where('kode_jenjang', $context);
        
        $builder->select('
            pegawai.nik as "NIK (KEY)", pegawai.nama_lengkap as "NAMA LENGKAP", pegawai.gelar_depan as "GELAR DEPAN", pegawai.gelar_belakang as "GELAR BELAKANG", 
            pegawai.jenis_kelamin as "JK (L/P)", pegawai.tempat_lahir as "TEMPAT LAHIR", pegawai.tanggal_lahir as "TGL LAHIR", 
            pegawai.nama_ibu_kandung as "IBU KANDUNG", pegawai.agama as AGAMA, pegawai.status_perkawinan as "STATUS PERKAWINAN",
            pegawai.nuptk as NUPTK, pegawai.nip as NIP, pegawai.nipy as NIPY, pegawai.status_kepegawaian as "STATUS KEPEGAWAIAN", 
            pegawai.jenis_ptk as "JENIS PTK", pegawai.tugas_tambahan as "TUGAS TAMBAHAN", pegawai.sk_pengangkatan as "SK PENGANGKATAN", 
            pegawai.tmt_pengangkatan as "TMT PENGANGKATAN", pegawai.sumber_gaji as "SUMBER GAJI", pegawai.pendidikan_terakhir as "PENDIDIKAN TERAKHIR", 
            pegawai.jenis_pegawai as "JENIS PEGAWAI", pegawai.status_aktif as "STATUS AKTIF", pegawai.kode_jenjang as "KODE JENJANG",
            pegawai.email as EMAIL, pegawai.no_hp as "NO HP", pegawai.alamat_jalan as "ALAMAT JALAN", pegawai.rt as RT, pegawai.rw as RW, 
            pegawai.nama_dusun as DUSUN, pegawai.desa_kelurahan as KELURAHAN, pegawai.kecamatan as KECAMATAN, pegawai.kode_pos as "KODE POS",
            rp.jenjang as "RP JENJANG", rp.nama_sekolah as "RP NAMA SEKOLAH", rp.jurusan as "RP JURUSAN", 
            rp.tahun_masuk as "RP THN MASUK", rp.tahun_lulus as "RP THN LULUS", rp.nilai_akhir as "RP NILAI AKHIR",
            rk.jenis_sk as "RK JENIS SK", rk.no_sk as "RK NO SK", rk.tanggal_sk as "RK TGL SK", rk.tmt_sk as "RK TMT SK", 
            rk.masa_kerja_tahun as "RK MASA KERJA THN", rk.masa_kerja_bulan as "RK MASA KERJA BLN", rk.status_kepegawaian as "RK STATUS PEG", 
            rk.pangkat_golongan as "RK PANGKAT GOL", rk.jabatan_fungsional as "RK JABATAN", rk.is_aktif as "RK IS AKTIF (1/0)"
        ');
        $builder->join('riwayat_pendidikan rp', 'rp.id_pegawai = pegawai.id', 'left'); 
        $builder->join('riwayat_kepegawaian rk', 'rk.id_pegawai = pegawai.id AND rk.is_aktif = 1', 'left');
        $builder->groupBy('pegawai.id');

        $data = $builder->get()->getResultArray();
        $spreadsheet = new Spreadsheet(); $sheet = $spreadsheet->getActiveSheet();
        $col = 'A'; foreach ($this->headers as $h) { $sheet->setCellValue($col++.'1', strtoupper($h)); $sheet->getColumnDimension($col)->setAutoSize(true); }
        $rowIdx = 2; foreach ($data as $row) { $col = 'A'; foreach ($row as $cell) { $sheet->setCellValue($col++.$rowIdx, $cell); } $rowIdx++; }
        
        $filename = 'export_full_pegawai_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); $writer->save('php://output'); exit;
    }
}