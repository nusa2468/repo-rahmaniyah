<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseController;
use App\Models\JabatanModel;
use App\Models\JenjangModel;

class Jabatan extends BaseController
{
    protected $jabatanModel;
    protected $jenjangModel;
    protected $userRole;
    protected $userJenjang;

    public function __construct()
    {
        $this->jabatanModel = new JabatanModel();
        
        // Fail-safe untuk JenjangModel
        if (file_exists(APPPATH . 'Models/JenjangModel.php')) {
            $this->jenjangModel = new JenjangModel();
        } else {
            // Fallback dummy jika model tidak ditemukan
            $this->jenjangModel = new class extends \CodeIgniter\Model {
                public function asArray(){ return $this; }
                public function where($k, $v){ return $this; }
                public function orderBy($k, $d){ return $this; }
                public function findAll(){ return []; }
            };
        }

        // Normalisasi Role & Unit
        $this->userRole    = strtolower(session()->get('role_name') ?? session()->get('role') ?? ''); 
        $this->userJenjang = session()->get('kode_jenjang') ?? session()->get('kode_unit');
    }

    public function index()
    {
        $isGlobalUser = in_array($this->userRole, ['superadmin', 'yayasan']);

        // 1. Siapkan List Jenjang untuk Dropdown Filter (Hanya Superadmin)
        $listJenjang = [];
        if ($isGlobalUser) {
            $listJenjang = $this->jenjangModel->asArray()
                                              ->where('status', 'aktif')
                                              ->orderBy('urutan', 'ASC')
                                              ->findAll();
        }

        // 2. Tangkap Filter dari URL
        $filterJenjang = $this->request->getGet('kode_jenjang');

        // 3. Ambil Data Jabatan dengan Scoping
        $builder = $this->jabatanModel->asArray();

        if ($isGlobalUser) {
            // Superadmin: Filter manual jika ada, jika tidak tampilkan semua
            if (!empty($filterJenjang) && $filterJenjang !== 'Semua') {
                $builder->where('kode_jenjang', $filterJenjang);
            }
        } else {
            // Admin Unit: Paksa filter ke unit sendiri
            $builder->where('kode_jenjang', $this->userJenjang);
        }

        $jabatan = $builder->orderBy('kode_jenjang', 'ASC')
                           ->orderBy('level', 'ASC')
                           ->findAll();

        // 4. Statistik Ringkas
        $stats = [
            'total'       => count($jabatan),
            'unit_aktif'  => !empty($jabatan) ? count(array_unique(array_column($jabatan, 'kode_jenjang'))) : 0,
            'level_count' => !empty($jabatan) ? count(array_unique(array_column($jabatan, 'level'))) : 0,
            'personel'    => 0 // Placeholder
        ];

        // 5. Data Grafik
        $chartUnit = [];
        foreach ($jabatan as $j) {
            $u = $j['kode_jenjang'] ?? 'Lainnya';
            $chartUnit[$u] = ($chartUnit[$u] ?? 0) + 1;
        }

        $data = [
            'title'          => 'Manajemen Data Jabatan',
            'role'           => $this->userRole,
            'jenjang'        => $this->userJenjang,
            'jabatan'        => $jabatan,
            'listJenjang'    => $listJenjang,   // Dropdown Filter (Index)
            'filter_jenjang' => $filterJenjang,
            'stats'          => $stats,
            'chartUnit'      => $chartUnit
        ];

        return view('masterdata/jabatan/index', $data);
    }

    public function new()
    {
        $isGlobalUser = in_array($this->userRole, ['superadmin', 'yayasan']);
        $listJenjang  = [];

        // 1. LOGIKA JENJANG DINAMIS (ANTI BOCOR)
        if ($isGlobalUser) {
            // Superadmin: Ambil semua unit aktif + Global
            $jenjangAktif = $this->jenjangModel->asArray()
                                               ->where('status', 'aktif')
                                               ->orderBy('urutan', 'ASC')
                                               ->findAll();
            $globalItem = ['kode_jenjang' => 'Global', 'nama_jenjang' => 'Global / Yayasan'];
            $listJenjang = array_merge([$globalItem], $jenjangAktif);
        } else {
            // Admin Unit: Hanya unitnya sendiri
            // Ambil nama unit agar tampil cantik
            $unitInfo = $this->jenjangModel->asArray()->where('kode_jenjang', $this->userJenjang)->first();
            $namaUnit = $unitInfo['nama_jenjang'] ?? 'Unit ' . $this->userJenjang;

            $listJenjang = [
                ['kode_jenjang' => $this->userJenjang, 'nama_jenjang' => $namaUnit]
            ];
        }

        // 2. LOGIKA ATASAN (Parent Jabatan)
        $atasanBuilder = $this->jabatanModel->asArray()->orderBy('level', 'ASC');
        if (!$isGlobalUser) {
            // Admin Unit boleh memilih atasan dari unitnya sendiri ATAU dari Global (Yayasan)
            // Contoh: Kepala Sekolah (Unit) melapor ke Ketua Yayasan (Global)
            $atasanBuilder->groupStart()
                          ->where('kode_jenjang', $this->userJenjang)
                          ->orWhere('kode_jenjang', 'Global')
                          ->groupEnd();
        }
        $listAtasan = $atasanBuilder->findAll();

        $data = [
            'title'        => 'Tambah Jabatan',
            'jabatan'      => [],
            'list_jenjang' => $listJenjang,
            'list_atasan'  => $listAtasan,
            'userRole'     => $this->userRole, // Untuk helper view
        ];
        return view('masterdata/jabatan/form', $data);
    }

    public function edit($id)
    {
        $isGlobalUser = in_array($this->userRole, ['superadmin', 'yayasan']);
        $item = $this->jabatanModel->asArray()->find($id);

        if (!$item) {
            return redirect()->to('app/masterdata/jabatan')->with('error', 'Data tidak ditemukan.');
        }

        // Proteksi Edit Lintas Unit
        if (!$isGlobalUser && $item['kode_jenjang'] !== $this->userJenjang) {
            return redirect()->to('app/masterdata/jabatan')->with('error', 'Akses Ditolak: Anda tidak dapat mengedit jabatan unit lain.');
        }

        // 1. Siapkan List Jenjang (Logic sama dengan new)
        $listJenjang = [];
        if ($isGlobalUser) {
            $jenjangAktif = $this->jenjangModel->asArray()
                                               ->where('status', 'aktif')
                                               ->orderBy('urutan', 'ASC')
                                               ->findAll();
            $globalItem = ['kode_jenjang' => 'Global', 'nama_jenjang' => 'Global / Yayasan'];
            $listJenjang = array_merge([$globalItem], $jenjangAktif);

            // Fallback: Jika unit jabatan ini sudah NON-AKTIF, tetap tampilkan agar tidak error
            $isCurrentFound = false;
            foreach ($listJenjang as $lj) {
                if ($lj['kode_jenjang'] == $item['kode_jenjang']) {
                    $isCurrentFound = true; break;
                }
            }
            if (!$isCurrentFound) {
                $currentUnit = $this->jenjangModel->asArray()->where('kode_jenjang', $item['kode_jenjang'])->first();
                if ($currentUnit) {
                    $currentUnit['nama_jenjang'] .= ' (Non-Aktif)';
                    $listJenjang[] = $currentUnit;
                }
            }
        } else {
             // Admin Unit
             $unitInfo = $this->jenjangModel->asArray()->where('kode_jenjang', $this->userJenjang)->first();
             $listJenjang = [['kode_jenjang' => $this->userJenjang, 'nama_jenjang' => $unitInfo['nama_jenjang'] ?? $this->userJenjang]];
        }

        // 2. List Atasan (Exclude diri sendiri)
        $atasanBuilder = $this->jabatanModel->asArray()
                                            ->where('id !=', $id)
                                            ->orderBy('level', 'ASC');
        
        if (!$isGlobalUser) {
            $atasanBuilder->groupStart()
                          ->where('kode_jenjang', $this->userJenjang)
                          ->orWhere('kode_jenjang', 'Global')
                          ->groupEnd();
        }
        $listAtasan = $atasanBuilder->findAll();

        $data = [
            'title'        => 'Edit Jabatan',
            'jabatan'      => $item,
            'list_jenjang' => $listJenjang,
            'list_atasan'  => $listAtasan,
            'userRole'     => $this->userRole,
        ];
        return view('masterdata/jabatan/form', $data);
    }

    public function save()
    {
        $id = $this->request->getPost('id');
        $isGlobalUser = in_array($this->userRole, ['superadmin', 'yayasan']);

        // Validasi Unit Target
        $targetJenjang = $this->request->getPost('kode_jenjang');
        
        // Security: Paksa Admin Unit hanya bisa simpan ke unitnya
        if (!$isGlobalUser) {
            $targetJenjang = $this->userJenjang;
        }

        $data = [
            'nama_jabatan' => $this->request->getPost('nama_jabatan'),
            'kode_jenjang' => $targetJenjang,
            'level'        => $this->request->getPost('level'),
            'atasan'       => $this->request->getPost('atasan') ?: null,
        ];

        // Validasi Model (Optional: bisa dipindah ke Model)
        // $validationRules = ...

        if ($id) {
            // Cek akses sebelum update
            if (!$isGlobalUser) {
                $existing = $this->jabatanModel->find($id);
                // Jika jabatan existing BUKAN milik unit ini, tolak
                if ($existing && $existing['kode_jenjang'] !== $this->userJenjang) {
                    return redirect()->to('app/masterdata/jabatan')->with('error', 'Akses Ilegal.');
                }
            }

            $this->jabatanModel->update($id, $data);
            $message = 'Data jabatan berhasil diperbarui.';
        } else {
            $this->jabatanModel->insert($data);
            $message = 'Data jabatan berhasil ditambahkan.';
        }

        return redirect()->to('app/masterdata/jabatan')->with('success', $message);
    }

    public function delete($id)
    {
        $isGlobalUser = in_array($this->userRole, ['superadmin', 'yayasan']);
        
        $item = $this->jabatanModel->find($id);
        if (!$item) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        // Proteksi Hapus Lintas Unit
        if (!$isGlobalUser && $item['kode_jenjang'] !== $this->userJenjang) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak dapat menghapus jabatan unit lain.');
        }

        $this->jabatanModel->delete($id);
        return redirect()->to('app/masterdata/jabatan')->with('success', 'Data jabatan berhasil dihapus.');
    }
}