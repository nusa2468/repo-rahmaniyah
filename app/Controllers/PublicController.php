<?php

namespace App\Controllers;

use App\Models\Cms\BeritaModel;
use App\Models\Cms\PengumumanModel;
use App\Models\Cms\AgendaModel;
use App\Models\Cms\AlbumModel;
use App\Models\JenjangModel; 
use App\Models\SettingsModel; // Tambahkan Model Settings

/**
 * Controller untuk Halaman Publik (Landing Page & Portal)
 * Mengelola tampilan depan untuk unit SD, SMP, SMA, dan Portal Yayasan.
 */
class PublicController extends BaseController
{
    protected $beritaModel;
    protected $pengumumanModel;
    protected $agendaModel;
    protected $albumModel;
    protected $jenjangModel;
    protected $settingsModel;
    protected $db;

    public function __construct()
    {
        $this->beritaModel     = new BeritaModel();
        $this->pengumumanModel = new PengumumanModel();
        $this->agendaModel     = new AgendaModel();
        $this->albumModel      = new AlbumModel();
        $this->jenjangModel    = new JenjangModel();
        $this->settingsModel   = new SettingsModel(); // Instansiasi SettingsModel
        
        $this->db = \Config\Database::connect();
    }

    /**
     * Halaman Utama Pemilihan Unit (Portal Yayasan)
     */
    public function portal()
    {
        // 1. Mengambil identitas Global untuk footer/header portal
        $globalSettings = $this->settingsModel->getSettingsAsArray('Global');

        // 2. Mengambil Unit yang AKTIF
        $activeUnits = $this->jenjangModel->where('status', 'aktif')
                                          ->orderBy('urutan', 'ASC')
                                          ->findAll();

        // 3. Inject Settings per Unit (Motto, Deskripsi, dll) secara Dinamis
        foreach ($activeUnits as &$unit) {
            $kode = strtoupper($unit['kode_jenjang']);
            // Ambil setting khusus unit ini
            $unitSettings = $this->settingsModel->getSettingsAsArray($kode);
            
            // Gabungkan ke dalam data unit dengan key 'config'
            $unit['config'] = $unitSettings;
        }

        $data = [
            'title'    => 'Portal Utama ' . ($globalSettings['nama_yayasan'] ?? 'Yayasan Pendidikan'),
            'settings' => $globalSettings,
            'units'    => $activeUnits 
        ];
        return view('public/portal_yayasan', $data);
    }

    /**
     * Landing Page Dinamis per Unit (SD/SMP/SMA)
     */
    public function index($jenjang = 'SD')
    {
        $jenjang = strtoupper($jenjang);
        
        // Cek keberadaan unit
        $isExist = $this->jenjangModel->where('kode_jenjang', $jenjang)
                                      ->where('status', 'aktif')
                                      ->countAllResults();

        if ($isExist == 0) {
            return redirect()->to(base_url('/'));
        }

        // Ambil data identitas unit dinamis dari database
        $settings = $this->settingsModel->getSettingsAsArray($jenjang);

        // Konfigurasi identitas visual (Warna tetap di hardcode karena ini style UI)
        $themes = [
            'TK'  => '#d63384', 
            'SD'  => '#e74c3c', 
            'SMP' => '#27ae60', 
            'SMA' => '#2980b9', 
            'SMK' => '#e67e22', 
        ];
        $themeColor = $themes[$jenjang] ?? '#4b5563'; 

        $data = [
            'jenjang'     => $jenjang,
            'unit_name'   => $settings['nama_sekolah'] ?? "Unit $jenjang",
            'theme_color' => $themeColor,
            'settings'    => $settings, // Data dinamis (Motto, Visi, Misi, Kontak)
            
            'berita'      => $this->beritaModel->getBeritaWithAuthor($jenjang), 
            'pengumuman'  => $this->pengumumanModel->getPengumumanWithAuthor($jenjang),
            'agenda'      => $this->agendaModel->getAgendaByJenjang($jenjang),
            'albums'      => $this->albumModel->getAlbumsByJenjang($jenjang),
        ];

        return view('public/landing_page', $data);
    }

    /**
     * Detail Berita
     */
    public function beritaDetail($jenjang, $slug)
    {
        $berita = $this->beritaModel->getBeritaBySlug($slug);
        
        if (!$berita) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Berita tidak ditemukan.");
        }

        // Ambil settings dinamis
        $settings = $this->settingsModel->getSettingsAsArray(strtoupper($jenjang));

        $data = [
            'title'    => $berita['judul'],
            'berita'   => $berita,
            'jenjang'  => strtoupper($jenjang),
            'settings' => $settings
        ];

        return view('public/landing_page', array_merge($data, ['show_detail' => true])); 
    }
}