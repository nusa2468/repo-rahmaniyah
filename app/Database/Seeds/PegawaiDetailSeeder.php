<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * PegawaiDetailSeeder
 * Mengisi data operasional kepegawaian berdasarkan migrasi CreatePegawaiDetailTable.
 * Mencakup: Master Komponen, Setting Gaji Individu, Log Absensi, dan Riwayat Slip Gaji.
 */
class PegawaiDetailSeeder extends Seeder
{
    public function run()
    {
        $db    = \Config\Database::connect();
        $faker = \Faker\Factory::create('id_ID');
        $time  = Time::now()->toDateTimeString();

        echo "Memulai sinkronisasi data detail kepegawaian...\n";

        // ---------------------------------------------------------------------
        // 0. PEMBERSIHAN DATA (SAFE TRUNCATE)
        // ---------------------------------------------------------------------
        $db->query('SET FOREIGN_KEY_CHECKS = 0');
        $db->table('riwayat_gaji_pegawai')->truncate();
        $db->table('gaji_pegawai')->truncate();
        $db->table('absensi_pegawai')->truncate();
        $db->table('komponen_gaji')->truncate();
        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        // ---------------------------------------------------------------------
        // 1. SEED MASTER KOMPONEN GAJI (Sesuai Struktur Migrasi)
        // ---------------------------------------------------------------------
        $jenjangs = ['SD', 'SMP', 'SMA', 'GLOBAL'];
        $komponenMasterIds = [];

        foreach ($jenjangs as $jenjang) {
            $listKomponen = [
                [
                    'nama' => 'Gaji Pokok', 
                    'code' => 'GAPOK', 
                    'tipe' => 1, // Pendapatan
                    'metode' => 'fixed', 
                    'nom' => match($jenjang){'SD'=>3000000,'SMP'=>3250000,'SMA'=>3500000,default=>4000000}
                ],
                [
                    'nama' => 'Tunjangan Struktural', 
                    'code' => 'TUNJ_STR', 
                    'tipe' => 1, 
                    'metode' => 'fixed', 
                    'nom' => 750000
                ],
                [
                    'nama' => 'Uang Makan Harian', 
                    'code' => 'MAKAN', 
                    'tipe' => 1, 
                    'metode' => 'variabel', 
                    'nom' => 25000
                ],
                [
                    'nama' => 'Potongan BPJS', 
                    'code' => 'POT_BPJS', 
                    'tipe' => 2, // Potongan
                    'metode' => 'fixed', 
                    'nom' => 150000
                ],
            ];

            foreach ($listKomponen as $k) {
                $kodeUnik = $k['code'] . '_' . $jenjang;
                $db->table('komponen_gaji')->insert([
                    'kode_komponen'   => substr($kodeUnik, 0, 50),
                    'kode_jenjang'    => $jenjang,
                    'nama_komponen'   => $k['nama'],
                    'tipe'            => $k['tipe'],
                    'metode_hitung'   => $k['metode'],
                    'nominal_default' => $k['nom'],
                    'is_default'      => 1,
                    'is_aktif'        => 1,
                    'created_at'      => $time,
                    'updated_at'      => $time
                ]);
                
                $komponenMasterIds[$jenjang][] = [
                    'id'      => $db->insertID(),
                    'nominal' => $k['nom'],
                    'tipe'    => $k['tipe']
                ];
            }
        }
        echo " [OK] Master Komponen Gaji berhasil dibuat.\n";

        // ---------------------------------------------------------------------
        // 2. AMBIL DATA PEGAWAI AKTIF
        // ---------------------------------------------------------------------
        $pegawaiList = $db->table('pegawai')->where('status_aktif', 'aktif')->get()->getResultArray();
        if (empty($pegawaiList)) {
            echo " [ERR] Tabel pegawai kosong! Seeding dibatalkan.\n";
            return;
        }

        $gajiBatch    = [];
        $absensiBatch = [];
        $riwayatBatch = [];

        // Setup Tanggal Log (7 Hari Terakhir)
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = date('Y-m-d', strtotime("-$i days"));
        }

        foreach ($pegawaiList as $p) {
            $unit = $p['kode_jenjang'] ?? 'GLOBAL';
            $assignedComps = $komponenMasterIds[$unit] ?? $komponenMasterIds['GLOBAL'];

            $sumPendapatan = 0;
            $sumPotongan   = 0;

            // --- A. SETTING GAJI INDIVIDU (Tabel: gaji_pegawai) ---
            foreach ($assignedComps as $c) {
                $gajiBatch[] = [
                    'id_pegawai'   => $p['id'],
                    'kode_jenjang' => $unit,
                    'id_komponen'  => $c['id'],
                    'jumlah_set'   => $c['nominal'],
                    'is_active'    => 1,
                    'created_at'   => $time,
                    'updated_at'   => $time
                ];
                if ($c['tipe'] == 1) $sumPendapatan += $c['nominal']; else $sumPotongan += $c['nominal'];
            }

            // --- B. LOG ABSENSI TERPADU (Tabel: absensi_pegawai) ---
            foreach ($dates as $d) {
                if (date('N', strtotime($d)) == 7) continue; // Libur Minggu

                $rand = rand(1, 100);
                $status = 'hadir';
                $in     = '07:30:00';
                $out    = '16:00:00';

                // Variasi status sesuai Enum Migrasi
                if ($rand > 95) { $status = 'alpa'; $in = null; $out = null; }
                elseif ($rand > 90) { $status = 'sakit'; $in = null; $out = null; }
                elseif ($rand > 85) { $status = 'terlambat'; $in = '08:15:00'; }
                elseif ($rand > 80) { $status = 'izin'; $in = null; $out = null; }
                elseif ($rand > 78) { $status = 'dinas_luar'; $in = '09:00:00'; $out = '17:00:00'; }

                $absensiBatch[] = [
                    'id_pegawai'   => $p['id'],
                    'kode_jenjang' => $unit, // Snapshot unit kerja saat absen
                    'tanggal'      => $d,
                    'jam_masuk'    => $in,
                    'jam_keluar'   => $out,
                    'status'       => $status,
                    'metode_absen' => $faker->randomElement(['manual', 'fingerprint', 'face_id']),
                    'keterangan'   => $status == 'hadir' ? 'Hadir normal' : 'Keterangan ' . $status,
                    'id_user_admin'=> 1,
                    'created_at'   => $time,
                    'updated_at'   => $time
                ];
            }

            // --- C. RIWAYAT SLIP GAJI (Tabel: riwayat_gaji_pegawai) ---
            $riwayatBatch[] = [
                'no_transaksi'     => 'SLIP/' . date('Ym') . '/' . str_pad($p['id'], 4, '0', STR_PAD_LEFT),
                'id_pegawai'       => $p['id'],
                'nama_pegawai'     => $p['nama_lengkap'],
                'jabatan_pegawai'  => $p['jenis_pegawai'] == 'guru' ? 'Tenaga Pendidik' : 'Staff Operasional',
                'kode_jenjang'     => $unit,
                'bulan'            => date('m'),
                'tahun'            => date('Y'),
                'total_pendapatan' => $sumPendapatan,
                'total_potongan'   => $sumPotongan,
                'gaji_bersih'      => $sumPendapatan - $sumPotongan,
                'status_bayar'     => $faker->randomElement(['Dibayar', 'Belum Dibayar']),
                'tanggal_bayar'    => date('Y-m-25'),
                'metode_bayar'     => 'Transfer Bank',
                'catatan'          => 'Gaji periode ' . date('F Y'),
                'created_at'       => $time,
                'updated_at'       => $time
            ];
        }

        // ---------------------------------------------------------------------
        // 3. EKSEKUSI BATCH INSERT
        // ---------------------------------------------------------------------
        if (!empty($gajiBatch)) {
            $db->table('gaji_pegawai')->insertBatch($gajiBatch);
            echo " [OK] Setting gaji individu terpetakan.\n";
        }

        if (!empty($absensiBatch)) {
            foreach (array_chunk($absensiBatch, 100) as $chunk) {
                $db->table('absensi_pegawai')->insertBatch($chunk);
            }
            echo " [OK] Log absensi harian berhasil digenerate.\n";
        }

        if (!empty($riwayatBatch)) {
            $db->table('riwayat_gaji_pegawai')->insertBatch($riwayatBatch);
            echo " [OK] Histori slip gaji (riwayat) berhasil dibuat.\n";
        }

        echo ">>> SEEDING DETAIL PEGAWAI SELESAI SEMPURNA.\n";
    }
}