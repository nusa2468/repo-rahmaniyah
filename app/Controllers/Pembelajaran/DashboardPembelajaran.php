<?php

namespace App\Controllers\Pembelajaran;

use App\Controllers\BaseController;
use App\Models\JenjangModel; // Load Model Jenjang

/**
 * Dashboard Pembelajaran Controller
 * Status: FINAL (Dynamic Unit Filter)
 * Perbaikan: Mengambil daftar jenjang aktif dari database untuk filter dropdown.
 */
class DashboardPembelajaran extends BaseController
{
    protected $db;
    protected $jenjangModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->jenjangModel = new JenjangModel(); // Inisialisasi Model
    }

    public function index()
    {
        // 1. Inisialisasi Session & Context
        $userRole = session()->get('role_name') ?? 'guest';
        $userUnit = session()->get('kode_jenjang'); 
        
        $globalRoles = ['superadmin', 'yayasan', 'pusat'];
        $isGlobal = in_array(strtolower($userRole), $globalRoles);

        // Security Guard
        if (!$isGlobal && empty($userUnit)) {
            return redirect()->to(base_url('login'))->with('error', 'Sesi tidak valid.');
        }

        // 2. Filter Unit (Server-Side Logic)
        $filterJenjang = $this->request->getGet('filter_jenjang');
        
        $scopeUnit = null;
        if ($isGlobal) {
            if (!empty($filterJenjang) && $filterJenjang !== 'ALL') {
                $scopeUnit = $filterJenjang;
            }
        } else {
            $scopeUnit = $userUnit;
        }

        // --- AMBIL DATA JENJANG DINAMIS (UNTUK DROPDOWN) ---
        // Mengambil hanya jenjang yang statusnya 'aktif'
        $daftarJenjang = $this->jenjangModel->getDropdownOptions();

        // 3. Definisi Modul
        $modules = [
            'silabus' => [
                'title' => 'Silabus', 'table' => 'pembelajaran_silabus', 'route' => 'app/pembelajaran/silabus',
                'icon' => 'fa-book-reader', 'color' => 'bg-blue-600', 'stat_label' => 'Total Silabus', 'desc' => 'Perencanaan pembelajaran.'
            ],
            'rpp' => [
                'title' => 'Modul Ajar', 'table' => 'pembelajaran_rpp', 'route' => 'app/pembelajaran/rpp',
                'icon' => 'fa-file-signature', 'color' => 'bg-emerald-600', 'stat_label' => 'Total RPP', 'desc' => 'Rencana harian.'
            ],
            'bahanAjar' => [
                'title' => 'Bahan Ajar', 'table' => 'pembelajaran_bahan_ajar', 'route' => 'app/pembelajaran/bahan-ajar',
                'icon' => 'fa-laptop-code', 'color' => 'bg-amber-500', 'stat_label' => 'File Materi', 'desc' => 'Materi digital.'
            ],
            'bankSoal' => [
                'title' => 'Bank Soal', 'table' => 'pembelajaran_bank_soal', 'route' => 'app/pembelajaran/bank-soal',
                'icon' => 'fa-box-open', 'color' => 'bg-violet-600', 'stat_label' => 'Butir Soal', 'desc' => 'Evaluasi.'
            ],
            'evaluasi' => [
                'title' => 'Evaluasi', 'table' => 'pembelajaran_evaluasi_belajar', 'route' => 'app/pembelajaran/evaluasi-belajar',
                'icon' => 'fa-tasks', 'color' => 'bg-rose-600', 'stat_label' => 'Jadwal Ujian', 'desc' => 'Penilaian.'
            ]
        ];

        // 4. Hitung Statistik & Data Preview
        $activeTab = $this->request->getVar('tab') ?? 'silabus';
        if (!array_key_exists($activeTab, $modules)) $activeTab = 'silabus';

        $page    = (int) ($this->request->getVar('page') ?? 1);
        $perPage = 10;
        $offset  = ($page - 1) * $perPage;

        $stats = [];
        $data  = [];

        foreach ($modules as $key => $config) {
            $tableName = $config['table'];
            
            // Count Stats
            $builder = $this->db->table($tableName);
            if ($scopeUnit) $builder->where('kode_jenjang', $scopeUnit);
            $stats[$key] = $builder->countAllResults();

            // Get Data
            $builderData = $this->db->table($tableName);
            if ($this->db->fieldExists('mata_pelajaran_id', $tableName)) {
                $builderData->select("$tableName.*, mata_pelajaran.nama_mapel, mata_pelajaran.kode_mapel");
                $builderData->join('mata_pelajaran', "mata_pelajaran.id = $tableName.mata_pelajaran_id", 'left');
            } else {
                $builderData->select("$tableName.*");
            }

            if ($scopeUnit) $builderData->where("$tableName.kode_jenjang", $scopeUnit);

            if ($key === $activeTab) {
                $result = $builderData->orderBy("$tableName.updated_at", 'DESC')->limit($perPage, $offset)->get()->getResultArray();
            } else {
                $result = $builderData->orderBy("$tableName.updated_at", 'DESC')->limit(3)->get()->getResultArray();
            }
            $data[$key] = $result;
        }

        // 5. Aggregate Logs
        $logs = [];
        foreach ($modules as $key => $mod) {
            $t = $mod['table'];
            $titleCol = ($key == 'silabus') ? 'materi_pokok' : (($key == 'rpp') ? 'topik' : (($key == 'bahanAjar') ? 'judul_bahan' : (($key == 'bankSoal') ? 'pertanyaan' : 'judul_evaluasi')));
            $q = $this->db->table($t)->select("'$key' as type, kode_jenjang, updated_at, created_at, $titleCol as title")->orderBy('updated_at', 'DESC')->limit(3);
            if ($scopeUnit) $q->where('kode_jenjang', $scopeUnit);
            try { $res = $q->get()->getResultArray(); if(!empty($res)) $logs = array_merge($logs, $res); } catch (\Exception $e) { continue; }
        }
        usort($logs, function($a, $b) { return strtotime($b['updated_at']) - strtotime($a['updated_at']); });
        $recentLogs = array_slice($logs, 0, 10);

        $totalActive = $stats[$activeTab] ?? 0;

        return view('pembelajaran/dashboard_pembelajaran', [
            'title'          => 'Dashboard Pembelajaran',
            'modules'        => $modules,
            'stats'          => $stats,
            'data'           => $data,
            'logs'           => $recentLogs,
            'userRole'       => $userRole,
            'userUnit'       => $scopeUnit ? $scopeUnit : 'GLOBAL',
            'filterSelected' => $filterJenjang ?? 'ALL',
            'isGlobal'       => $isGlobal,
            'activeTab'      => $activeTab,
            'daftarJenjang'  => $daftarJenjang, // <--- Data Jenjang Dinamis Dikirim
            'pager'          => [
                'current' => $page,
                'perPage' => $perPage,
                'total'   => ceil($totalActive / $perPage) ?: 1,
                'count'   => $totalActive
            ]
        ]);
    }
}