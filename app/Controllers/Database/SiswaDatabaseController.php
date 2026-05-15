<?php

namespace App\Controllers\Database;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SiswaDatabaseController extends BaseController
{
    protected $db;
    
    // Header 73 Kolom - Diupdate dengan ID KELAS
    private $headers = [
        'NIS (KEY)', 'NISN', 'NIK', 'NAMA LENGKAP', 'JK (L/P)', 'TEMPAT LAHIR', 'TGL LAHIR (YYYY-MM-DD)', 'AGAMA', 'KODE JENJANG', 'ANGKATAN', 'STATUS', 'NAMA IBU KANDUNG',
        'NAMA PANGGILAN', 'KEWARGANEGARAAN', 'NO AKTA LAHIR', 'STATUS ANAK', 'ALAMAT DOMISILI', 'RT', 'RW', 'DUSUN', 'KELURAHAN', 'KECAMATAN', 'KODE POS',
        'LINTANG', 'BUJUR', 'TELEPON', 'EMAIL PRIBADI', 'NO KK', 'JENIS PENDAFTARAN', 'ASAL SEKOLAH', 'NO SERI IJAZAH', 'NOMOR IJAZAH', 'TANGGAL LULUS',
        'ALASAN KELUAR', 'PENERIMA KPS (1/0)', 'NO KPS', 'PENERIMA KIP (1/0)', 'NO KIP', 'PENERIMA KKS (1/0)', 'NO KKS',
        'NAMA AYAH', 'NIK AYAH', 'PEKERJAAN AYAH', 'PENDIDIKAN AYAH', 'PENGHASILAN AYAH', 'NO HP AYAH',
        'NAMA IBU', 'NIK IBU', 'PEKERJAAN IBU', 'PENDIDIKAN IBU', 'PENGHASILAN IBU', 'NO HP IBU',
        'NAMA WALI', 'NIK WALI', 'PEKERJAAN WALI', 'NO HP WALI',
        'ID TAHUN AJARAN', 'SEMESTER', 'ID KELAS', 'ID ROMBEL',
        'ID JURUSAN', 'TAHUN KELUAR', 'EMAIL LOGIN', 'PASSWORD LOGIN',
        'JALUR PENERIMAAN', 'NO PENDAFTARAN (AKADEMIK)', 'TANGGAL DITERIMA', 'PROGRAM PEMINATAN', 'SK YUDISIUM', 'NILAI MASUK', 'CATATAN KHUSUS',
        'TANGGAL MASUK KELAS', 'STATUS AKADEMIK KELAS'
    ];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    private function getContext()
    {
        return session()->get('kode_jenjang') ?? session()->get('kode_unit');
    }

    private function isGlobalAccess(?string $context): bool
    {
        $globalScopes = ['GLOBAL', 'YAYASAN', 'ALL', 'ROOT', 'PUSAT'];
        return empty($context) || in_array(strtoupper($context), $globalScopes);
    }

    private function parseDate($value)
    {
        if (empty($value) || trim($value) == '-' || trim($value) == '') return null;
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            $value = trim($value);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return $value;
            $time = strtotime(str_replace(['/', '.'], '-', $value));
            return $time ? date('Y-m-d', $time) : null;
        } catch (\Exception $e) { return null; }
    }

    private function getSafeNumeric($value)
    {
        $val = trim($value ?? '');
        return ($val === '' || !is_numeric($val)) ? null : $val;
    }

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $col = 'A';
        foreach ($this->headers as $header) {
            $sheet->setCellValue($col . '1', strtoupper($header));
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }
        
        // Dummy Data
        // fputcsv(fopen('php://output', 'w'), $this->headers); // Placeholder for download stream logic
        
        $filename = "template_full_siswa.csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $fp = fopen('php://output', 'wb');
        fputcsv($fp, $this->headers);
        fputcsv($fp, ['1001', '0012345678', '320101...', 'BUDI SANTOSO', 'L', 'JAKARTA', '2010-05-20', 'ISLAM', 'SD', '2024', 'AKTIF', 'SITI AMINAH', 'BUDI', 'WNI', 'AKTA123', 'KANDUNG', 'JL MERDEKA', '01', '02', 'DUSUN', 'KEL', 'KEC', '17111', '', '', '0812...', 'email@mail', 'KK123', 'BARU', 'TK', 'NOIJZ', 'NOSERI', '', '', '0', '', '0', '', '0', '', 'AHMAD', '3201...', 'WIRASWASTA', 'S1', '5000000', '081...', 'SITI', '3201...', 'IRT', 'SMA', '0', '081...', '', '', '', '', '1', 'GANJIL', '1', '1', '1', '', 'login@sis', '123456', 'ZONASI', 'REG001', '2024-07-01', 'IPA', '', '85.5', '', '2024-07-15', 'AKTIF']);
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

        try {
            $extension = strtolower($file->getClientExtension());
            $reader = null;
            if ($extension === 'csv' || $extension === 'txt') {
                $reader = new Csv();
                if (($handle = fopen($file->getTempName(), "r")) !== FALSE) {
                    $firstLine = fgets($handle); fclose($handle);
                    if ($firstLine) {
                        $delimiters = [';' => substr_count($firstLine, ';'), ',' => substr_count($firstLine, ','), "\t" => substr_count($firstLine, "\t"), '|' => substr_count($firstLine, '|')];
                        arsort($delimiters);
                        $best = array_key_first($delimiters);
                        $reader->setDelimiter(($delimiters[$best] > 0) ? $best : ',');
                    }
                }
            } elseif ($extension === 'xls') $reader = new Xls(); else $reader = new Xlsx();

            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getTempName());
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            if (count($sheetData) > 0) array_shift($sheetData);

            $colMap = []; for ($i = 1; $i <= 73; $i++) $colMap[] = Coordinate::stringFromColumnIndex($i);
            $stats = ['total' => count($sheetData), 'inserted' => 0, 'updated' => 0, 'skipped' => 0, 'enrollment_ok' => 0, 'enrollment_fail' => 0, 'family_count' => 0];
            $failReport = []; $enrollmentErrors = [];

            $this->db->query('SET FOREIGN_KEY_CHECKS = 0');

            foreach ($sheetData as $index => $row) {
                $rowNum = $index + 2; $idSiswa = null;

                // Smart Fix: Split Tab/Space if csv malformed
                if (!empty($row['A']) && empty($row['D'])) {
                    $parts = preg_split('/[\t\s]{2,}/', trim($row['A']));
                    if (count($parts) >= 4) {
                        foreach ($parts as $k => $val) { if (isset($colMap[$k])) $row[$colMap[$k]] = trim($val, '" '); }
                    }
                }
                
                // Smart Fix: Column Shift (Deteksi jika Semester menggeser kolom)
                // BG harusnya ID Kelas, jika berisi Ganjil/Genap berarti geser
                if (isset($row['BG']) && in_array(strtoupper(trim($row['BG'])), ['GANJIL', 'GENAP'])) {
                    $row['BF'] = $row['BG']; // Semester
                    $row['BG'] = $row['BH'] ?? null; // ID Kelas
                    $row['BH'] = $row['BI'] ?? null; // ID Rombel
                }

                if (empty(trim($row['A'] ?? ''))) continue; 
                if (empty(trim($row['D'] ?? ''))) { $stats['skipped']++; $failReport[] = "Baris $rowNum: Nama Kosong."; continue; }

                $this->db->transBegin();

                try {
                    $jenjangInput = $row['I'] ?? 'GLOBAL';
                    $finalJenjang = $isGlobal ? $jenjangInput : $context;

                    // 1. SISWA
                    $siswaData = [
                        'nis' => trim($row['A']), 'nisn' => trim($row['B']), 'nik' => trim($row['C']),
                        'nama_lengkap' => trim($row['D']), 'jenis_kelamin' => strtoupper(trim($row['E'])),
                        'tempat_lahir' => trim($row['F']), 'tanggal_lahir' => $this->parseDate($row['G']),
                        'agama' => strtoupper(trim($row['H'])), 'kode_jenjang' => $finalJenjang,
                        'angkatan' => $this->getSafeNumeric($row['J']) ?: date('Y'),
                        'status' => strtoupper(trim($row['K']) ?: 'AKTIF'), 'nama_ibu_kandung' => trim($row['L']),
                        'alamat' => trim($row['Q']), 'id_jurusan' => $this->getSafeNumeric($row['BI']),
                        'tahun_keluar' => $this->getSafeNumeric($row['BJ']), 'email' => trim($row['BK']),
                        'updated_at' => $now
                    ];
                    if (!empty($row['BL'])) $siswaData['password'] = password_hash(trim($row['BL']), PASSWORD_DEFAULT);

                    $existingSiswa = $this->db->table('siswa')->where('nis', $siswaData['nis'])->get()->getRow();
                    if ($existingSiswa) {
                        $this->db->table('siswa')->where('id', $existingSiswa->id)->update($siswaData);
                        $idSiswa = $existingSiswa->id; $stats['updated']++;
                    } else {
                        $siswaData['created_at'] = $now;
                        $this->db->table('siswa')->insert($siswaData);
                        $idSiswa = $this->db->insertID(); $stats['inserted']++;
                    }

                    // 2. DEMOGRAFI 
                    $demografiData = [
                        'id_siswa' => $idSiswa, 'nama_panggilan' => $row['M'], 'kewarganegaraan' => strtoupper(trim($row['N'])),
                        'no_akta_lahir' => $row['O'], 'status_anak' => strtoupper(trim($row['P'])), 'alamat' => $row['Q'], 'rt' => $row['R'], 'rw' => $row['S'], 'dusun' => $row['T'], 'kelurahan' => $row['U'], 'kecamatan' => $row['V'], 'kode_pos' => $row['W'], 'lintang' => $this->getSafeNumeric($row['X']), 'bujur' => $this->getSafeNumeric($row['Y']), 'telepon' => $row['Z'], 'email' => $row['AA'], 'no_kk' => $row['AB'], 'jenis_pendaftaran' => strtoupper(trim($row['AC'])), 'asal_sekolah' => $row['AD'], 'no_seri_ijazah' => $row['AE'], 'nomor_ijazah' => $row['AF'], 'tanggal_lulus' => $this->parseDate($row['AG']), 'alasan_keluar' => $row['AH'], 'penerimaan_kps' => (int)$row['AI'], 'no_kps' => $row['AJ'], 'penerimaan_kip' => (int)$row['AK'], 'no_kip' => $row['AL'], 'penerimaan_kks' => (int)$row['AM'], 'no_kks' => $row['AN'], 'nama_ayah' => $row['AO'], 'nama_ibu' => $row['AU'], 'updated_at' => $now
                    ];
                    if ($this->db->table('siswa_demografi')->where('id_siswa', $idSiswa)->countAllResults() > 0) $this->db->table('siswa_demografi')->where('id_siswa', $idSiswa)->update($demografiData); else { $demografiData['created_at'] = $now; $this->db->table('siswa_demografi')->insert($demografiData); }

                    // 3. KELUARGA
                    $this->db->table('siswa_keluarga')->where('id_siswa', $idSiswa)->delete();
                    if (!empty($row['AO'])) { $this->db->table('siswa_keluarga')->insert(['id_siswa' => $idSiswa, 'hubungan' => 'AYAH', 'nama_lengkap' => $row['AO'], 'nik' => $row['AP'], 'pekerjaan' => $row['AQ'], 'pendidikan' => $row['AR'], 'penghasilan' => $this->getSafeNumeric($row['AS']), 'no_telepon' => $row['AT'], 'created_at' => $now]); $stats['family_count']++; }
                    if (!empty($row['AU'])) { $this->db->table('siswa_keluarga')->insert(['id_siswa' => $idSiswa, 'hubungan' => 'IBU', 'nama_lengkap' => $row['AU'], 'nik' => $row['AV'], 'pekerjaan' => $row['AW'], 'pendidikan' => $row['AX'], 'penghasilan' => $this->getSafeNumeric($row['AY']), 'no_telepon' => $row['AZ'], 'created_at' => $now]); $stats['family_count']++; }
                    if (!empty($row['BA'])) { $this->db->table('siswa_keluarga')->insert(['id_siswa' => $idSiswa, 'hubungan' => 'WALI', 'nama_lengkap' => $row['BA'], 'nik' => $row['BB'], 'pekerjaan' => $row['BC'], 'no_telepon' => $row['BD'], 'is_wali' => 1, 'created_at' => $now]); $stats['family_count']++; }

                    // 4. AKADEMIK
                    $akademikData = [
                        'id_siswa' => $idSiswa, 'jalur_penerimaan' => strtoupper(trim($row['BM'])), 'nomor_pendaftaran' => $row['BN'], 'tanggal_diterima' => $this->parseDate($row['BO']), 'program_peminatan' => strtoupper(trim($row['BP'])), 'sk_yudisium_masuk' => $row['BQ'], 'nilai_masuk' => $this->getSafeNumeric($row['BR']), 'catatan_khusus' => $row['BS'], 'updated_at' => $now
                    ];
                    if ($this->db->table('siswa_akademik')->where('id_siswa', $idSiswa)->countAllResults() > 0) $this->db->table('siswa_akademik')->where('id_siswa', $idSiswa)->update($akademikData); else { $akademikData['created_at'] = $now; $this->db->table('siswa_akademik')->insert($akademikData); }

                    // 5. ENROLLMENT (SMART LOOKUP)
                    // BE: ID Tahun Ajaran, BG: ID Kelas (Nama Kelas jika teks)
                    $rawTA = trim($row['BE'] ?? ''); 
                    $rawKelas = trim($row['BG'] ?? '');
                    
                    // Lookup ID jika input berupa nama
                    $idTa = is_numeric($rawTA) ? $rawTA : (empty($rawTA) ? null : ($this->db->table('tahun_ajaran')->like('tahun_ajaran', $rawTA)->get()->getRow()->id ?? null));
                    $idKelas = is_numeric($rawKelas) ? $rawKelas : (empty($rawKelas) ? null : ($this->db->table('kelas')->where('nama_kelas', $rawKelas)->get()->getRow()->id ?? null));

                    if ($idTa && $idKelas) {
                        $enrollData = [
                            'id_siswa' => $idSiswa, 
                            'id_tahun_ajaran' => $idTa, 
                            'semester' => strtoupper(trim($row['BF'] ?? 'GANJIL')), 
                            'id_kelas' => $idKelas, 
                            'id_grup_siswa' => $this->getSafeNumeric($row['BH']), 
                            'tanggal_masuk' => $this->parseDate($row['BT']) ?: date('Y-m-d'), 
                            'status_akademik' => strtoupper(trim($row['BU'] ?? 'AKTIF')), 
                            'updated_at' => $now
                        ];
                        
                        $existEnroll = $this->db->table('siswa_enrollment')->where('id_siswa', $idSiswa)->where('id_tahun_ajaran', $idTa)->get()->getRow();
                        
                        if ($existEnroll) {
                            $this->db->table('siswa_enrollment')->where('id', $existEnroll->id)->update($enrollData); 
                        } else { 
                            $enrollData['created_at'] = $now; 
                            $this->db->table('siswa_enrollment')->insert($enrollData); 
                        }
                        $stats['enrollment_ok']++;
                    } else {
                        // Hanya hitung gagal jika field kelas tidak kosong tapi tidak ditemukan
                        if (!empty($rawKelas)) {
                            $stats['enrollment_fail']++;
                            if (!$existingSiswa) $enrollmentErrors[] = "NIS {$row['A']}: TA='$rawTA' / Kls='$rawKelas'";
                        }
                    }

                    $this->db->transCommit();

                } catch (\Exception $e) { $this->db->transRollback(); $stats['skipped']++; $failReport[] = "NIS " . trim($row['A']) . ": " . $e->getMessage(); }
            }

            $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
            
            $msg = "<b>Import Selesai.</b><br>Total: {$stats['total']} | Insert: {$stats['inserted']} | Update: {$stats['updated']}<br>Enrollment: {$stats['enrollment_ok']} | Gagal: {$stats['enrollment_fail']}";
            if (!empty($enrollmentErrors)) {
                $msg .= "<br>Detail Gagal Enrollment:<ul class='list-disc pl-4 text-[10px]'>";
                foreach (array_slice($enrollmentErrors, 0, 5) as $err) $msg .= "<li>$err</li>";
                $msg .= "</ul>";
            }
            return redirect()->back()->with(($stats['inserted'] + $stats['updated']) > 0 ? 'success' : 'error', $msg);

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Critical Error: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $table = 'siswa';
        $context = $this->getContext();
        $isGlobal = $this->isGlobalAccess($context);
        $builder = $this->db->table($table);

        if (!$isGlobal && $this->db->fieldExists('kode_jenjang', $table)) $builder->where("$table.kode_jenjang", $context);

        $builder->select('
            siswa.nis as "NIS (KEY)", siswa.nisn as NISN, siswa.nik as NIK, siswa.nama_lengkap as "NAMA LENGKAP", siswa.jenis_kelamin as "JK (L/P)", 
            siswa.tempat_lahir as "TEMPAT LAHIR", siswa.tanggal_lahir as "TGL LAHIR (YYYY-MM-DD)", siswa.agama as AGAMA, siswa.kode_jenjang as "KODE JENJANG", 
            siswa.angkatan as ANGKATAN, siswa.status as STATUS, siswa.nama_ibu_kandung as "NAMA IBU KANDUNG",
            sd.nama_panggilan as "NAMA PANGGILAN", sd.kewarganegaraan as KEWARGANEGARAAN, sd.no_akta_lahir as "NO AKTA LAHIR", sd.status_anak as "STATUS ANAK", 
            sd.alamat as "ALAMAT DOMISILI", sd.rt as RT, sd.rw as RW, sd.dusun as DUSUN, sd.kelurahan as KELURAHAN, sd.kecamatan as KECAMATAN, 
            sd.kode_pos as "KODE POS", sd.lintang as LINTANG, sd.bujur as BUJUR, sd.telepon as TELEPON, sd.email as "EMAIL PRIBADI", 
            sd.no_kk as "NO KK", sd.jenis_pendaftaran as "JENIS PENDAFTARAN", sd.asal_sekolah as "ASAL SEKOLAH", sd.no_seri_ijazah as "NO SERI IJAZAH", sd.nomor_ijazah as "NOMOR IJAZAH", 
            sd.tanggal_lulus as "TANGGAL LULUS", sd.alasan_keluar as "ALASAN KELUAR", sd.penerimaan_kps as "PENERIMA KPS (1/0)", sd.no_kps as "NO KPS", sd.penerimaan_kip as "PENERIMA KIP (1/0)", 
            sd.no_kip as "NO KIP", sd.penerimaan_kks as "PENERIMA KKS (1/0)", sd.no_kks as "NO KKS",
            sk_ayah.nama_lengkap as "NAMA AYAH", sk_ayah.nik as "NIK AYAH", sk_ayah.pekerjaan as "PEKERJAAN AYAH", 
            sk_ayah.pendidikan as "PENDIDIKAN AYAH", sk_ayah.penghasilan as "PENGHASILAN AYAH", sk_ayah.no_telepon as "NO HP AYAH",
            sk_ibu.nama_lengkap as "NAMA IBU", sk_ibu.nik as "NIK IBU", sk_ibu.pekerjaan as "PEKERJAAN IBU", 
            sk_ibu.pendidikan as "PENDIDIKAN IBU", sk_ibu.penghasilan as "PENGHASILAN IBU", sk_ibu.no_telepon as "NO HP IBU",
            sk_wali.nama_lengkap as "NAMA WALI", sk_wali.nik as "NIK WALI", sk_wali.pekerjaan as "PEKERJAAN WALI", 
            sk_wali.no_telepon as "NO HP WALI",
            se.id_tahun_ajaran as "ID TAHUN AJARAN", se.semester as SEMESTER, se.id_kelas as "ID KELAS", se.id_grup_siswa as "ID ROMBEL",
            siswa.id_jurusan as "ID JURUSAN", siswa.tahun_keluar as "TAHUN KELUAR", siswa.email as "EMAIL LOGIN", "" as "PASSWORD LOGIN",
            sa.jalur_penerimaan as "JALUR PENERIMAAN", sa.nomor_pendaftaran as "NO PENDAFTARAN (AKADEMIK)", sa.tanggal_diterima as "TANGGAL DITERIMA",
            sa.program_peminatan as "PROGRAM PEMINATAN", sa.sk_yudisium_masuk as "SK YUDISIUM", sa.nilai_masuk as "NILAI MASUK", sa.catatan_khusus as "CATATAN KHUSUS",
            se.tanggal_masuk as "TANGGAL MASUK KELAS", se.status_akademik as "STATUS AKADEMIK KELAS"
        ');
        $builder->join('siswa_demografi sd', 'sd.id_siswa = siswa.id', 'left');
        $builder->join('siswa_keluarga sk_ayah', 'sk_ayah.id_siswa = siswa.id AND sk_ayah.hubungan = "AYAH"', 'left');
        $builder->join('siswa_keluarga sk_ibu', 'sk_ibu.id_siswa = siswa.id AND sk_ibu.hubungan = "IBU"', 'left');
        $builder->join('siswa_keluarga sk_wali', 'sk_wali.id_siswa = siswa.id AND sk_wali.hubungan = "WALI"', 'left');
        $builder->join('siswa_akademik sa', 'sa.id_siswa = siswa.id', 'left');
        // Join ke enrollment aktif saja
        $builder->join('siswa_enrollment se', 'se.id_siswa = siswa.id AND se.status_akademik = "AKTIF"', 'left');
        $builder->groupBy('siswa.id');

        $data = $builder->get()->getResultArray();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        if (!empty($data)) {
            $col = 'A'; foreach ($this->headers as $h) { $sheet->setCellValue($col++.'1', $h); }
            $rowIdx = 2;
            foreach ($data as $row) { $col = 'A'; foreach ($row as $cell) { $sheet->setCellValue($col++.$rowIdx, $cell); } $rowIdx++; }
        }
        
        $filename = 'export_full_siswa_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output'); exit;
    }
}