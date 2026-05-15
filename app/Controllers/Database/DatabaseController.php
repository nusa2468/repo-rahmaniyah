<?php

namespace App\Controllers\Database;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * DatabaseController (Main Dispatcher)
 * Fungsi:
 * 1. Menangani Halaman Utama (Dashboard, Menu Import/Export).
 * 2. Menangani Backup & Restore Database (SQL).
 * 3. Mengarahkan (Dispatch) proses data spesifik ke controller pecahannya.
 */
class DatabaseController extends BaseController
{
    protected $db;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
    }

    // --- 1. DASHBOARD & MENU ---

    private function getContext() { return session()->get('kode_jenjang') ?? session()->get('kode_unit'); }
    private function isGlobalAccess(?string $context): bool {
        $globalScopes = ['GLOBAL', 'YAYASAN', 'ALL', 'ROOT', 'PUSAT'];
        return empty($context) || in_array(strtoupper($context), $globalScopes);
    }

    public function index()
    {
        $size = 0;
        try {
            $query = $this->db->query("SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size' FROM information_schema.TABLES WHERE table_schema = '" . $this->db->database . "' GROUP BY table_schema");
            $row = $query->getRow();
            $size = $row ? $row->Size : 0;
        } catch (\Exception $e) { }

        $data = [
            'title'    => 'Maintenance Database',
            'db_name'  => $this->db->database,
            'db_size'  => $size,
            'tables'   => $this->db->listTables(),
            'isGlobal' => $this->isGlobalAccess($this->getContext()),
            'platform' => $this->db->getPlatform(),
            'version'  => $this->db->getVersion(),
        ];
        return view('database/index', $data);
    }

    public function import_form() { return view('database/import', ['title' => 'Import Data Master']); }
    public function export_menu() { return view('database/export', ['title' => 'Export Data Master']); }


    // --- 2. DISPATCHER (PENGATUR LALU LINTAS) ---
    // Fungsi ini memanggil controller pecahan sesuai tipe data

    /**
     * Helper untuk memanggil sub-controller
     */
    private function callSubController($class)
    {
        $controller = new $class();
        // Penting: Inisialisasi request/response agar fitur CI4 (seperti $this->request) jalan di sub-controller
        $controller->initController($this->request, $this->response, \Config\Services::logger());
        return $controller;
    }

    public function download_template($type)
    {
        if ($type == 'siswa') return $this->callSubController(SiswaDatabaseController::class)->template();
        if ($type == 'pegawai') return $this->callSubController(PegawaiDatabaseController::class)->template();
        if ($type == 'mata_pelajaran') return $this->callSubController(KurikulumDatabaseController::class)->template();
        
        return redirect()->back()->with('error', 'Template tidak ditemukan.');
    }

    public function import_process()
    {
        $targetTable = $this->request->getPost('target_table');

        if ($targetTable == 'siswa') return $this->callSubController(SiswaDatabaseController::class)->import();
        if ($targetTable == 'pegawai') return $this->callSubController(PegawaiDatabaseController::class)->import();
        if ($targetTable == 'mata_pelajaran') return $this->callSubController(KurikulumDatabaseController::class)->import();

        return redirect()->back()->with('error', 'Target tabel tidak valid.');
    }

    public function export_data($table)
    {
        if ($table == 'siswa') return $this->callSubController(SiswaDatabaseController::class)->export();
        if ($table == 'pegawai') return $this->callSubController(PegawaiDatabaseController::class)->export();
        if ($table == 'mata_pelajaran') return $this->callSubController(KurikulumDatabaseController::class)->export();

        return redirect()->back()->with('error', 'Tabel tidak valid untuk export.');
    }


    // --- 3. FITUR UMUM (BACKUP & RESTORE) ---
    // Tetap di sini karena bersifat global database

    public function backup()
    {
        if (!$this->isGlobalAccess($this->getContext())) return redirect()->back()->with('error', 'Akses Ditolak.');
        
        $filename = 'backup_full_erp_' . date('Y-m-d_H-i-s') . '.sql';
        $tables = $this->db->listTables();
        $sql = "-- ERP Backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";
        foreach ($tables as $table) {
            $createTable = $this->db->query("SHOW CREATE TABLE `$table`")->getRowArray();
            $sql .= "DROP TABLE IF EXISTS `$table`;\n" . $createTable['Create Table'] . ";\n\n";
            $rows = $this->db->table($table)->get()->getResultArray();
            foreach ($rows as $row) {
                $values = array_map(function ($value) { return ($value === null) ? "NULL" : "'" . $this->db->escapeString($value) . "'"; }, $row);
                $sql .= "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n";
            }
            $sql .= "\n";
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $this->response->download($filename, $sql);
    }

    public function restore()
    {
        if (!$this->isGlobalAccess($this->getContext())) return redirect()->back()->with('error', 'Akses Ditolak.');
        $file = $this->request->getFile('backup_file');
        if ($file && $file->isValid() && $file->getExtension() === 'sql') {
            $sql = file_get_contents($file->getTempName());
            $queries = explode(';', $sql);
            $this->db->transStart();
            $this->db->query('SET FOREIGN_KEY_CHECKS=0');
            foreach ($queries as $query) { if (trim($query)) $this->db->query($query); }
            $this->db->query('SET FOREIGN_KEY_CHECKS=1');
            $this->db->transComplete();
            return $this->db->transStatus() ? redirect()->back()->with('success', 'Database pulih.') : redirect()->back()->with('error', 'Gagal restore.');
        }
        return redirect()->back()->with('error', 'File tidak valid.');
    }
}