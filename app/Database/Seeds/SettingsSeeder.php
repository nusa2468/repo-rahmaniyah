<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $now = Time::now();
        
        // Nonaktifkan check foreign key dan bersihkan data lama
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0;');
        $this->db->table('settings')->truncate();
        $this->db->table('jenjang_sekolah')->truncate();

        // --- 1. SEEDING TABEL: jenjang_sekolah (Master Data Unit) ---
        $jenjangData = [
            [
                'nama_jenjang' => 'Pusat Yayasan Rahmaniyah',
                'kode_jenjang' => 'Global',
                'urutan'       => 1,
                'status'       => 'aktif',
                'keterangan'   => 'Kantor pusat administrasi dan manajemen Yayasan Pendidikan Rahmany.',
                'created_at'   => $now, 'updated_at'   => $now
            ],
            [
                'nama_jenjang' => 'SDIT Rahmaniyah',
                'kode_jenjang' => 'SD',
                'urutan'       => 2,
                'status'       => 'aktif',
                'keterangan'   => 'Unit pendidikan tingkat dasar (Elementary School).',
                'created_at'   => $now, 'updated_at'   => $now
            ],
            [
                'nama_jenjang' => 'SMPIT Rahmaniyah',
                'kode_jenjang' => 'SMP',
                'urutan'       => 3,
                'status'       => 'aktif',
                'keterangan'   => 'Unit pendidikan tingkat menengah pertama (Junior High School).',
                'created_at'   => $now, 'updated_at'   => $now
            ],
            [
                'nama_jenjang' => 'SMAIT Rahmaniyah',
                'kode_jenjang' => 'SMA',
                'urutan'       => 4,
                'status'       => 'aktif',
                'keterangan'   => 'Unit pendidikan tingkat menengah atas (Senior High School).',
                'created_at'   => $now, 'updated_at'   => $now
            ],
        ];
        $this->db->table('jenjang_sekolah')->insertBatch($jenjangData);

        // --- 2. SEEDING TABEL: settings (Konfigurasi & Profil) ---
        $data = [];

        // --- DATA GLOBAL (YAYASAN) ---
        $globalConfigs = [
            ['key' => 'nama_yayasan',    'value' => 'Yayasan Pendidikan Rahmany'],
            ['key' => 'nama_sekolah',    'value' => 'Perguruan Rahmaniyah Depok'],
            ['key' => 'motto',           'value' => 'Membangun Generasi Qur\'ani, Unggul, dan Berkarakter Islami'],
            ['key' => 'email',           'value' => 'info@rahmaniyah.sch.id'],
            ['key' => 'telepon',         'value' => '+62 021-77833598'],
            ['key' => 'alamat',          'value' => 'Jl. Lapangan Member Blok C No. 11 RT.004/001, Kel. Sukmajaya, Kec. Sukmajaya, Kota Depok, Jawa Barat 16412'],
            ['key' => 'kepala_sekolah',  'value' => 'Drs. H. Misbah Rosyadi (Ketua Yayasan)'],
            ['key' => 'sejarah',         'value' => 'Didirikan pada tahun 2003 oleh Bapak Sofyan Abdurrachman, berawal dari TKIT dan terus berkembang ke jenjang SDIT, SMPIT, hingga SMAIT dengan konsep pendidikan Islam terpadu berkelanjutan.'],
            ['key' => 'visi',            'value' => 'Membina generasi Qur\'ani, unggul, berkarakter Islami, dan melahirkan pemimpin muslim yang memiliki jiwa kepemimpinan Qur\'ani.'],
            ['key' => 'misi',            'value' => "1. Menyelenggarakan pendidikan Islam terpadu yang ramah anak.\n2. Mengintegrasikan kurikulum nasional dengan nilai-nilai Al-Qur'an.\n3. Mewujudkan lingkungan sekolah yang sehat dan hijau."],
        ];

        // --- DATA SD ---
        $sdConfigs = [
            ['key' => 'nama_sekolah',    'value' => 'SDIT Rahmaniyah'],
            ['key' => 'motto',           'value' => 'Cerdas, Kreatif, Berakhlak Mulia'],
            ['key' => 'email',           'value' => 'sdit@rahmaniyah.sch.id'],
            ['key' => 'telepon',         'value' => '021-77833598'],
            ['key' => 'alamat',          'value' => 'Jl. Lapangan Member Blok C No. 11 RT.004/001, Kel. Sukmajaya, Kec. Sukmajaya, Kota Depok, Jawa Barat 16412'],
            ['key' => 'kepala_sekolah',  'value' => 'Ust. Asep Koswara'],
            ['key' => 'sejarah',         'value' => 'Beroperasi sejak tahun 2003, menggunakan sistem Full Day School dan aktif membina kemandirian siswa serta program Tahfiz Al-Qur\'an.'],
            ['key' => 'visi',            'value' => 'Terwujudnya generasi hafidz, mandiri, dan cinta lingkungan.'],
            ['key' => 'misi',            'value' => "1. Pembiasaan ibadah harian.\n2. Pembelajaran aktif berbasis alam dan lingkungan.\n3. Penanaman kemandirian siswa."],
        ];

        // --- DATA SMP ---
        $smpConfigs = [
            ['key' => 'nama_sekolah',    'value' => 'SMPIT Rahmaniyah'],
            ['key' => 'motto',           'value' => 'Unggul dalam Prestasi, Santun dalam Perilaku'],
            ['key' => 'email',           'value' => 'smpit@rahmaniyah.sch.id'],
            ['key' => 'telepon',         'value' => '021-1234569'],
            ['key' => 'alamat',          'value' => 'Jl. M. Natsir No. 46, RT 008/RW 001, Kelurahan Cilodong, Kecamatan Cilodong, Kota Depok, Jawa Barat, 16474'],
            ['key' => 'kepala_sekolah',  'value' => 'Ferry Veronika, S.E.'],
            ['key' => 'sejarah',         'value' => 'Didirikan pada 17 Oktober 2010 (Ma\'had Rahmaniyah Al-Islamy) untuk memperkuat transisi remaja dengan disiplin adab, ilmu agama, dan tahfiz Qur\'an.'],
            ['key' => 'visi',            'value' => 'Terbaik dalam pembinaan adab, ilmu agama, dan literasi modern.'],
            ['key' => 'misi',            'value' => "1. Optimalisasi program tahfidz dan bahasa Arab.\n2. Pembinaan karakter disiplin ala santri.\n3. Integrasi sains dan agama."],
        ];

        // --- DATA SMA ---
        $smaConfigs = [
            ['key' => 'nama_sekolah',    'value' => 'SMAIT Rahmaniyah'],
            ['key' => 'motto',           'value' => 'Menyiapkan Pemimpin Masa Depan Berintegritas'],
            ['key' => 'email',           'value' => 'smait@rahmaniyah.sch.id'],
            ['key' => 'telepon',         'value' => '021-1234570'],
            ['key' => 'alamat',          'value' => 'Jl. Siri Prada No. 01 (Gedung C), Cilodong, Kota Depok'],
            ['key' => 'kepala_sekolah',  'value' => 'Purwanita Jayanti'],
            ['key' => 'sejarah',         'value' => 'Didirikan pada tahun 2019 sebagai jenjang lanjutan untuk inkubasi kepemimpinan, persiapan pendidikan tinggi, dan pematangan karakter Islami.'],
            ['key' => 'visi',            'value' => 'Pusat unggulan kepemimpinan Qur\'ani dan sains berlandaskan iman.'],
            ['key' => 'misi',            'value' => "1. Intensif persiapan masuk PTN/Luar Negeri.\n2. Penguatan karakter kepemimpinan Islam.\n3. Pengembangan minat bakat dan riset."],
        ];

        // Pemrosesan batch insert
        $this->processConfigs($data, $globalConfigs, 'Global', $now);
        $this->processConfigs($data, $sdConfigs, 'SD', $now);
        $this->processConfigs($data, $smpConfigs, 'SMP', $now);
        $this->processConfigs($data, $smaConfigs, 'SMA', $now);

        $this->db->table('settings')->insertBatch($data);
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1;');
    }

    /**
     * Helper untuk memproses array data settings
     */
    private function processConfigs(array &$target, array $source, string $jenjang, $time)
    {
        foreach ($source as $item) {
            $target[] = [
                'jenjang'    => $jenjang,
                'key'        => $item['key'],
                'value'      => $item['value'],
                'created_at' => $time,
                'updated_at' => $time
            ];
        }
    }
}