<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseMasterDataController;
use App\Models\JenjangModel;
use App\Models\KurikulumModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Controller Kurikulum (Enterprise Edition)
 * Mengelola standar kurikulum pendidikan per unit dengan proteksi scope unit.
 */
class Kurikulum extends BaseMasterDataController
{
    private string $redirectBaseUrl = 'app/masterdata/kurikulum';
    private array $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT'];

    /**
     * Menampilkan daftar Kurikulum dengan dukungan Scoping Unit & Search.
     */
    public function index(): string
    {
        // 1. Otoritas Unit dari Session
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        // 2. Ambil Parameter Request
        $filterUnit = $this->request->getGet('unit');
        $search     = $this->request->getGet('search');
        $perPage    = $this->request->getGet('per_page') ?? 10;

        // Paksa filter jika bukan Super Admin
        if (!$isSuperAdmin) {
            $filterUnit = $userJenjang;
        }

        $unitParam = (empty($filterUnit) || in_array(strtoupper($filterUnit), $this->globalIdentifiers)) ? null : $filterUnit;

        // 3. Query Building dengan Scoping (Menggunakan Model Scoping Pattern)
        $model = $this->kurikulumModel;
        $model->select('kurikulum.*, js.nama_jenjang as unit_sekolah, js.urutan as urutan_jenjang');
        $model->join('jenjang_sekolah js', 'js.kode_jenjang = kurikulum.kode_jenjang', 'left');

        if ($unitParam) {
            $model->where('kurikulum.kode_jenjang', $unitParam);
        }

        if (!empty($search)) {
            $model->groupStart()
                  ->like('kurikulum.nama_kurikulum', $search)
                  ->orLike('kurikulum.kode_kurikulum', $search)
                  ->groupEnd();
        }

        // Urutkan: Aktif di atas, lalu berdasarkan urutan jenjang
        $model->orderBy('kurikulum.status', 'ASC')
              ->orderBy('js.urutan', 'ASC')
              ->orderBy('kurikulum.nama_kurikulum', 'ASC');

        // 4. Ambil daftar jenjang untuk filter (Hanya yang diizinkan)
        $jenjangModel = new JenjangModel();
        $allJenjangs = $jenjangModel->getAktifForIdentitas();
        $jenjangFiltered = array_filter($allJenjangs, function($j) use ($isSuperAdmin, $userJenjang) {
            $kode = strtoupper(is_object($j) ? $j->kode_jenjang : $j['kode_jenjang']);
            if (in_array($kode, $this->globalIdentifiers)) return false;
            return $isSuperAdmin || $kode === $userJenjang;
        });

        $data = [
            'title'          => 'Master Kurikulum - ' . ($unitParam ? 'Unit '.strtoupper($unitParam) : 'Seluruh Unit'),
            'kurikulum_list' => $model->paginate((int)$perPage, 'kurikulum'),
            'pager'          => $model->pager,
            'jenjang_list'   => $jenjangFiltered,
            'is_restricted'  => !$isSuperAdmin,
            'current_filter' => [
                'unit'     => $filterUnit ?? ($isSuperAdmin ? 'GLOBAL' : $userJenjang),
                'per_page' => $perPage,
                'search'   => $search
            ]
        ];

        return view('masterdata/kurikulum/index', $this->loadViewData($data));
    }

    /**
     * Form tambah kurikulum baru.
     */
    public function new(): string
    {
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);

        $jenjangModel = new JenjangModel();
        $allJenjang = $jenjangModel->getAktifForIdentitas();

        $filteredJenjang = array_filter($allJenjang, function($j) use ($isSuperAdmin, $userJenjang) {
            $kode = strtoupper(is_object($j) ? $j->kode_jenjang : $j['kode_jenjang']);
            if (in_array($kode, $this->globalIdentifiers)) return false;
            return $isSuperAdmin || $kode === $userJenjang;
        });

        $data = [
            'title'        => 'Tambah Kurikulum Baru',
            'kurikulum'    => [],
            'jenjang_list' => $filteredJenjang,
            'validation'   => \Config\Services::validation(),
        ];

        return view('masterdata/kurikulum/form', $this->loadViewData($data));
    }

    /**
     * Simpan data baru.
     */
    public function create(): RedirectResponse
    {
        $post = $this->request->getPost();
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');

        if ($userJenjang !== 'GLOBAL' && !in_array($userJenjang, $this->globalIdentifiers)) {
            if (strtoupper($post['kode_jenjang'] ?? '') !== $userJenjang) {
                return redirect()->back()->withInput()->with('error', 'Otoritas Ditolak.');
            }
        }
        
        if (!$this->kurikulumModel->save($post)) {
            return redirect()->back()->withInput()->with('errors', $this->kurikulumModel->errors());
        }

        return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Data Kurikulum berhasil ditambahkan.');
    }
    
    public function show($id = null): RedirectResponse
    {
        return redirect()->to(base_url($this->redirectBaseUrl . '/edit/' . $id));
    }

    /**
     * Form edit.
     */
    public function edit($id = null): string
    {
        if ($id === null) throw PageNotFoundException::forPageNotFound();
        
        $kurikulum = $this->kurikulumModel->find($id);
        if (!$kurikulum) throw PageNotFoundException::forPageNotFound();

        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);
        if (!$isSuperAdmin && strtoupper($kurikulum['kode_jenjang']) !== $userJenjang) {
            throw new \CodeIgniter\Router\Exceptions\RedirectException(base_url($this->redirectBaseUrl), 302, ['error' => 'Akses Ditolak.']);
        }

        $jenjangModel = new JenjangModel();
        $allJenjang = $jenjangModel->getAktifForIdentitas();
        $filteredJenjang = array_filter($allJenjang, function($j) use ($isSuperAdmin, $userJenjang) {
            $kode = strtoupper(is_object($j) ? $j->kode_jenjang : $j['kode_jenjang']);
            return $isSuperAdmin || $kode === $userJenjang;
        });

        $data = [
            'title'        => 'Edit Kurikulum: ' . $kurikulum['nama_kurikulum'],
            'kurikulum'    => $kurikulum,
            'jenjang_list' => $filteredJenjang,
            'validation'   => \Config\Services::validation(),
        ];
        return view('masterdata/kurikulum/form', $this->loadViewData($data));
    }

    /**
     * Update data.
     */
    public function update($id = null): RedirectResponse
    {
        if ($id === null) throw PageNotFoundException::forPageNotFound();

        $post = $this->request->getPost();
        $post['id'] = $id;

        $existing = $this->kurikulumModel->find($id);
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        if (!in_array($userJenjang, $this->globalIdentifiers) && strtoupper($existing['kode_jenjang'] ?? '') !== $userJenjang) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('error', 'Akses Ditolak.');
        }
        
        if (!$this->kurikulumModel->save($post)) {
            return redirect()->back()->withInput()->with('errors', $this->kurikulumModel->errors());
        }

        return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Data Kurikulum berhasil diperbarui.');
    }

    /**
     * Hapus data.
     */
    public function delete($id = null): RedirectResponse
    {
        if ($id === null) return redirect()->to(base_url($this->redirectBaseUrl));

        $kurikulum = $this->kurikulumModel->find($id);
        if (!$kurikulum) throw PageNotFoundException::forPageNotFound();

        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        if (!in_array($userJenjang, $this->globalIdentifiers) && strtoupper($kurikulum['kode_jenjang']) !== $userJenjang) {
            return redirect()->back()->with('error', 'Otoritas Ditolak.');
        }

        if (strtolower($kurikulum['status'] ?? '') === 'aktif') {
            return redirect()->back()->with('error', 'Kurikulum AKTIF tidak dapat dihapus.');
        }

        if ($this->kurikulumModel->delete($id)) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Data berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus data.');
    }
}