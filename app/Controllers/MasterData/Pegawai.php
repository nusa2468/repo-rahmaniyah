<?php

namespace App\Controllers\MasterData;

use App\Controllers\BaseController; 
use App\Models\GuruModel;
use App\Models\KaryawanModel;
use App\Models\JenjangModel;
use App\Models\PegawaiDokumenModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Controller Pegawai (Unified - Full Version)
 * STATUS: FIXED V4 (Sinkronisasi Filter Penunjang & Unit Yayasan)
 */
class Pegawai extends BaseController
{
    protected $pegawaiDokumenModel;
    protected $guruModel;
    protected $karyawanModel;
    protected $jenjangModel;
    
    private $redirectBaseUrl = 'app/masterdata/pegawai';
    private $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT'];

    public function __construct()
    {
        $this->db                  = \Config\Database::connect();
        $this->guruModel           = new GuruModel();      
        $this->karyawanModel       = new KaryawanModel(); 
        $this->pegawaiDokumenModel = new PegawaiDokumenModel(); 
        
        // Fail-safe JenjangModel
        if (file_exists(APPPATH . 'Models/MasterData/JenjangModel.php')) {
            $this->jenjangModel = model('App\Models\MasterData\JenjangModel');
        } elseif (file_exists(APPPATH . 'Models/JenjangModel.php')) {
            $this->jenjangModel = new JenjangModel();
        } else {
            $this->jenjangModel = new class extends \CodeIgniter\Model {
                protected $table = 'jenjang_sekolah';
                protected $returnType = 'array';
                public function findAll(?int $limit = null, int $offset = 0) { return []; }
            };
        }
    }

    public function index()
    {
        $session = session();
        $userJenjang  = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $userRole     = strtolower($session->get('role_name') ?? session()->get('role') ?? '');
        $isSuperAdmin = in_array($userRole, ['superadmin', 'yayasan']);

        $filterJenis = $this->request->getGet('jenis') ?? 'all'; 
        $filterUnit  = $this->request->getGet('unit'); 
        $search      = $this->request->getGet('search');
        $perPage     = $this->request->getGet('per_page') ?? 10;

        // Otorisasi: Admin Unit hanya bisa lihat unitnya sendiri
        if (!$isSuperAdmin) {
            $filterUnit = $userJenjang;
        }
        
        // FIX: Pisahkan antara "Semua Unit" (null/kosong) dengan "Unit Yayasan/Pusat" (GLOBAL)
        $unitParam = ($filterUnit === '' || $filterUnit === null) ? null : $filterUnit;

        // FIX: Gunakan db->table langsung agar filter Penunjang bisa dieksekusi murni
        $builder = $this->db->table('pegawai')->where('deleted_at', null);
        
        if ($filterJenis === 'guru') {
            $builder->where('jenis_pegawai', 'guru');
        } elseif ($filterJenis === 'staff') {
            $builder->where('jenis_pegawai', 'staff');
        } elseif ($filterJenis === 'penunjang') {
            $builder->where('jenis_pegawai', 'penunjang');
        }

        if ($unitParam) {
            $builder->where('kode_jenjang', $unitParam);
        }

        if ($search) {
            $builder->groupStart()
                    ->like('nama_lengkap', $search)
                    ->orLike('nip', $search)
                    ->orLike('nik', $search)
                    ->orLike('nuptk', $search)
                    ->orLike('nipy', $search)
                    ->groupEnd();
        }

        $countQuery = clone $builder;
        $totalRows  = $countQuery->countAllResults();
        $pager      = \Config\Services::pager();
        $page       = $this->request->getVar('page_pegawai') ?? 1;
        
        $pegawaiData = $builder->orderBy('nama_lengkap', 'ASC')
                               ->limit($perPage, ($page - 1) * $perPage)
                               ->get()
                               ->getResultArray();

        $statsBuilder = $this->db->table('pegawai')->where('deleted_at', null);
        if ($unitParam) {
            $statsBuilder->where('kode_jenjang', $unitParam);
        }
        
        $statsData = $statsBuilder->select('
                COUNT(*) as total,
                SUM(CASE WHEN jenis_pegawai = "guru" THEN 1 ELSE 0 END) as total_guru,
                SUM(CASE WHEN jenis_pegawai IN ("staff", "penunjang") THEN 1 ELSE 0 END) as total_staff,
                SUM(CASE WHEN status_aktif = "aktif" THEN 1 ELSE 0 END) as total_aktif
            ')->get()->getRowArray();

        $jenjangList = [];
        if ($isSuperAdmin) {
            // FIX: Sertakan unit Yayasan/GLOBAL agar bisa dipilih di filter dropdown
            $jenjangList = $this->jenjangModel->asArray()->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
        }

        $data = [
            'title'          => 'Data Pegawai Terpadu',
            'role'           => $userRole,
            'jenjang'        => $userJenjang,
            'pegawai_data'   => $pegawaiData,
            'pager_obj'      => $pager,
            'total_rows'     => $totalRows,
            'current_page'   => $page,
            'per_page'       => $perPage,
            'jenjang_list'   => $jenjangList,   
            'is_restricted'  => !$isSuperAdmin,
            'stats'          => $statsData,
            'current_filter' => [
                'unit'     => $filterUnit ?? '',
                'jenis'    => $filterJenis,
                'search'   => $search,
                'per_page' => $perPage
            ]
        ];

        return view('masterdata/pegawai/index', $data);
    }

    public function show($id = null)
    {
        if (!$id) throw PageNotFoundException::forPageNotFound();

        $pegawai = $this->db->table('pegawai')->where('id', $id)->get()->getRowArray();
        if (!$pegawai) throw PageNotFoundException::forPageNotFound();

        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        if (!in_array($userJenjang, $this->globalIdentifiers) && strtoupper($pegawai['kode_jenjang']) !== $userJenjang) {
             return redirect()->back()->with('error', 'Akses data pegawai berbeda unit ditolak.');
        }

        $dokumen = $this->pegawaiDokumenModel->getDokumenByPegawai($id);

        $pendidikan = [];
        if (file_exists(APPPATH . 'Models/RiwayatPendidikanModel.php') && $this->db->tableExists('riwayat_pendidikan')) {
            $pendModel = new \App\Models\RiwayatPendidikanModel();
            $pendidikan = $pendModel->where('id_pegawai', $id)->orderBy('tahun_lulus', 'DESC')->findAll();
        }

        $riwayatKepegawaian = [];
        if (file_exists(APPPATH . 'Models/RiwayatKepegawaianModel.php') && $this->db->tableExists('riwayat_kepegawaian')) {
            $rkModel = new \App\Models\RiwayatKepegawaianModel();
            $riwayatKepegawaian = $rkModel->where('id_pegawai', $id)->orderBy('tmt_sk', 'DESC')->findAll();
        }

        $data = [
            'title'              => 'Profil Pegawai: ' . $pegawai['nama_lengkap'],
            'pegawai'            => $pegawai,
            'dokumen'            => $dokumen,
            'pendidikan'         => $pendidikan,
            'riwayat_kepegawaian'=> $riwayatKepegawaian
        ];

        return view('masterdata/pegawai/show', $data);
    }

    public function new()
    {
        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        $isSuperAdmin = in_array($userJenjang, $this->globalIdentifiers);
        
        $allJenjangs = $this->jenjangModel->asArray()->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
        
        $filteredJenjangs = array_filter($allJenjangs, function($j) use ($isSuperAdmin, $userJenjang) {
            $kode = strtoupper($j['kode_jenjang']);
            // FIX: Tidak perlu memblokir GLOBAL di form, karena Yayasan boleh merekrut staf
            return $isSuperAdmin || $kode === $userJenjang;
        });

        $data = [
            'title'        => 'Tambah Pegawai Baru',
            'pegawai'      => [],
            'dokumen'      => [], 
            'jenjang_list' => $filteredJenjangs,
            'validation'   => \Config\Services::validation(),
        ];
        
        return view('masterdata/pegawai/form', $data);
    }

    public function create()
    {
        $dataPost = $this->request->getPost();
        $targetModel = ($dataPost['jenis_pegawai'] == 'guru') ? $this->guruModel : $this->karyawanModel;

        // --- VALIDASI CUSTOM DENGAN BAHASA INDONESIA ---
        $rules = [
            'kode_jenjang' => [
                'rules'  => 'required',
                'errors' => ['required' => 'Unit Penempatan wajib dipilih.']
            ],
            'nama_lengkap' => [
                'rules'  => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required'   => 'Nama Lengkap wajib diisi.',
                    'min_length' => 'Nama Lengkap minimal 3 karakter.'
                ]
            ],
            'nik' => [
                'rules'  => 'required|numeric|exact_length[16]|is_unique[pegawai.nik]',
                'errors' => [
                    'required'     => 'NIK KTP wajib diisi.',
                    'numeric'      => 'NIK harus berupa angka bulat.',
                    'exact_length' => 'NIK harus tepat 16 digit angka.',
                    'is_unique'    => 'NIK ini sudah terdaftar pada sistem.'
                ]
            ],
            'email' => [
                'rules'  => 'permit_empty|valid_email',
                'errors' => [
                    'valid_email' => 'Format email tidak valid. Pastikan menggunakan format yang benar (contoh: pegawai@sekolah.com).'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        if (!in_array($userJenjang, $this->globalIdentifiers) && strtoupper($dataPost['kode_jenjang']) !== $userJenjang) {
            return redirect()->back()->withInput()->with('error', 'Otoritas Ditolak: Anda tidak dapat mendaftarkan ke unit lain.');
        }

        // --- UPLOAD FOTO PROFIL (FORM UTAMA) ---
        $namaFoto = 'default.png'; 
        $fileFoto = $this->request->getFile('foto');

        if ($fileFoto && $fileFoto->isValid() && !$fileFoto->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads/pegawai')) {
                mkdir(FCPATH . 'uploads/pegawai', 0755, true);
            }
            $namaFoto = $fileFoto->getRandomName();
            $fileFoto->move(FCPATH . 'uploads/pegawai', $namaFoto);
        }

        $saveData = [
            'kode_jenjang'        => $dataPost['kode_jenjang'],
            'jenis_pegawai'       => $dataPost['jenis_pegawai'], 
            'nama_lengkap'        => $dataPost['nama_lengkap'],
            'gelar_depan'         => $dataPost['gelar_depan'] ?? null,
            'gelar_belakang'      => $dataPost['gelar_belakang'] ?? null,
            'nik'                 => $dataPost['nik'],
            'nip'                 => $dataPost['nip'] ?? null,
            'nuptk'               => $dataPost['nuptk'] ?? null,
            'nipy'                => $dataPost['nipy'] ?? null,
            'jenis_kelamin'       => $dataPost['jenis_kelamin'] ?? 'L',
            'tempat_lahir'        => $dataPost['tempat_lahir'] ?? null,
            'tanggal_lahir'       => $dataPost['tanggal_lahir'] ?? null,
            'nama_ibu_kandung'    => $dataPost['nama_ibu_kandung'] ?? null,
            'agama'               => $dataPost['agama'] ?? null,
            'status_perkawinan'   => $dataPost['status_perkawinan'] ?? null,
            'email'               => $dataPost['email'] ?? null,
            'no_hp'               => $dataPost['no_hp'] ?? null,
            'alamat_jalan'        => $dataPost['alamat_jalan'] ?? null,
            'rt'                  => $dataPost['rt'] ?? null,
            'rw'                  => $dataPost['rw'] ?? null,
            'nama_dusun'          => $dataPost['nama_dusun'] ?? null,
            'desa_kelurahan'      => $dataPost['desa_kelurahan'] ?? null,
            'kecamatan'           => $dataPost['kecamatan'] ?? null,
            'kode_pos'            => $dataPost['kode_pos'] ?? null,
            'status_kepegawaian'  => $dataPost['status_kepegawaian'] ?? 'GTY/PTY',
            'jenis_ptk'           => $dataPost['jenis_ptk'] ?? null,
            'tugas_tambahan'      => $dataPost['tugas_tambahan'] ?? null,
            'sk_pengangkatan'     => $dataPost['sk_pengangkatan'] ?? null,
            'tmt_pengangkatan'    => $dataPost['tmt_pengangkatan'] ?? null,
            'sumber_gaji'         => $dataPost['sumber_gaji'] ?? null,
            'pendidikan_terakhir' => $dataPost['pendidikan_terakhir'] ?? null,
            'status_aktif'        => $dataPost['status_aktif'] ?? 'aktif',
            'foto'                => $namaFoto, 
        ];

        // Skip validation karena kita sudah melakukan validasi secara manual di atas
        if ($targetModel->skipValidation(true)->insert($saveData)) {
            $newId = $targetModel->getInsertID();
            return redirect()->to(base_url($this->redirectBaseUrl . '/edit/' . $newId))->with('success', 'Data berhasil disimpan. Silakan upload dokumen pendukung.');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data ke database.');
    }

    public function edit($id)
    {
        $pegawai = $this->db->table('pegawai')->where('id', $id)->get()->getRowArray();
        if (!$pegawai) throw PageNotFoundException::forPageNotFound();

        $userJenjang = strtoupper(session()->get('kode_jenjang') ?? 'GLOBAL');
        if (!in_array($userJenjang, $this->globalIdentifiers) && strtoupper($pegawai['kode_jenjang']) !== $userJenjang) {
             return redirect()->back()->with('error', 'Akses ditolak: Unit berbeda.');
        }

        $allJenjangs = $this->jenjangModel->asArray()->where('status', 'aktif')->orderBy('urutan', 'ASC')->findAll();
        
        $dokumen = $this->pegawaiDokumenModel->getDokumenByPegawai($id);
        
        $pendidikan = [];
        if (file_exists(APPPATH . 'Models/RiwayatPendidikanModel.php') && $this->db->tableExists('riwayat_pendidikan')) {
            $pendModel = new \App\Models\RiwayatPendidikanModel();
            $pendidikan = $pendModel->where('id_pegawai', $id)->orderBy('tahun_lulus', 'DESC')->findAll();
        }

        $riwayatKepegawaian = [];
        if (file_exists(APPPATH . 'Models/RiwayatKepegawaianModel.php') && $this->db->tableExists('riwayat_kepegawaian')) {
            $rkModel = new \App\Models\RiwayatKepegawaianModel();
            $riwayatKepegawaian = $rkModel->where('id_pegawai', $id)->orderBy('tmt_sk', 'DESC')->findAll();
        }
        
        $data = [
            'title'              => 'Edit Pegawai: ' . $pegawai['nama_lengkap'],
            'pegawai'            => $pegawai,
            'dokumen'            => $dokumen,
            'pendidikan'         => $pendidikan,
            'riwayat_kepegawaian'=> $riwayatKepegawaian,
            'jenjang_list'       => $allJenjangs, 
            'validation'         => \Config\Services::validation(),
        ];
        
        return view('masterdata/pegawai/form', $data);
    }

    public function update($id)
    {
        $existing = $this->db->table('pegawai')->where('id', $id)->get()->getRowArray();
        
        if (!$existing) {
            return redirect()->back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        $targetModel = ($existing['jenis_pegawai'] == 'guru') ? $this->guruModel : $this->karyawanModel;
        $dataPost = $this->request->getPost();

        // --- VALIDASI CUSTOM DENGAN BAHASA INDONESIA ---
        $rules = [
            'nama_lengkap' => [
                'rules'  => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required'   => 'Nama Lengkap wajib diisi.',
                    'min_length' => 'Nama Lengkap minimal 3 karakter.'
                ]
            ],
            'nik' => [
                'rules'  => "required|numeric|exact_length[16]|is_unique[pegawai.nik,id,{$id}]",
                'errors' => [
                    'required'     => 'NIK KTP wajib diisi.',
                    'numeric'      => 'NIK harus berupa angka bulat.',
                    'exact_length' => 'NIK harus tepat 16 digit angka.',
                    'is_unique'    => 'NIK ini sudah digunakan oleh pegawai lain.'
                ]
            ],
            'email' => [
                'rules'  => 'permit_empty|valid_email',
                'errors' => [
                    'valid_email' => 'Format email tidak valid. Pastikan menggunakan format yang benar (contoh: pegawai@sekolah.com).'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'kode_jenjang'        => $dataPost['kode_jenjang'],
            'jenis_pegawai'       => $dataPost['jenis_pegawai'],
            'nama_lengkap'        => $dataPost['nama_lengkap'],
            'gelar_depan'         => $dataPost['gelar_depan'] ?? null,
            'gelar_belakang'      => $dataPost['gelar_belakang'] ?? null,
            'nik'                 => $dataPost['nik'],
            'nip'                 => $dataPost['nip'] ?? null,
            'nuptk'               => $dataPost['nuptk'] ?? null,
            'nipy'                => $dataPost['nipy'] ?? null,
            'jenis_kelamin'       => $dataPost['jenis_kelamin'] ?? 'L',
            'tempat_lahir'        => $dataPost['tempat_lahir'] ?? null,
            'tanggal_lahir'       => $dataPost['tanggal_lahir'] ?? null,
            'nama_ibu_kandung'    => $dataPost['nama_ibu_kandung'] ?? null,
            'agama'               => $dataPost['agama'] ?? null,
            'status_perkawinan'   => $dataPost['status_perkawinan'] ?? null,
            'email'               => $dataPost['email'] ?? null,
            'no_hp'               => $dataPost['no_hp'] ?? null,
            'alamat_jalan'        => $dataPost['alamat_jalan'] ?? null,
            'rt'                  => $dataPost['rt'] ?? null,
            'rw'                  => $dataPost['rw'] ?? null,
            'nama_dusun'          => $dataPost['nama_dusun'] ?? null,
            'desa_kelurahan'      => $dataPost['desa_kelurahan'] ?? null,
            'kecamatan'           => $dataPost['kecamatan'] ?? null,
            'kode_pos'            => $dataPost['kode_pos'] ?? null,
            'status_kepegawaian'  => $dataPost['status_kepegawaian'] ?? 'GTY/PTY',
            'jenis_ptk'           => $dataPost['jenis_ptk'] ?? null,
            'tugas_tambahan'      => $dataPost['tugas_tambahan'] ?? null,
            'sk_pengangkatan'     => $dataPost['sk_pengangkatan'] ?? null,
            'tmt_pengangkatan'    => $dataPost['tmt_pengangkatan'] ?? null,
            'sumber_gaji'         => $dataPost['sumber_gaji'] ?? null,
            'pendidikan_terakhir' => $dataPost['pendidikan_terakhir'] ?? null,
            'status_aktif'        => $dataPost['status_aktif'] ?? 'aktif',
        ];

        // --- UPLOAD FOTO PROFIL ---
        $fileFoto = $this->request->getFile('foto');
        if ($fileFoto && $fileFoto->isValid() && !$fileFoto->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/pegawai';
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);

            if (!empty($existing['foto']) && $existing['foto'] !== 'default.png' && file_exists($uploadPath . '/' . $existing['foto'])) {
                unlink($uploadPath . '/' . $existing['foto']);
            }
            
            $namaFoto = $fileFoto->getRandomName();
            $fileFoto->move($uploadPath, $namaFoto);
            $updateData['foto'] = $namaFoto; 
        }

        if ($targetModel->skipValidation(true)->update($id, $updateData)) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Data pegawai diperbarui.');
        }

        return redirect()->back()->withInput()->with('errors', $targetModel->errors());
    }

    public function delete($id)
    {
        $pegawai = $this->db->table('pegawai')->where('id', $id)->get()->getRowArray();
        if (!$pegawai) throw PageNotFoundException::forPageNotFound();

        $targetModel = ($pegawai['jenis_pegawai'] == 'guru') ? $this->guruModel : $this->karyawanModel;

        if ($targetModel->delete($id)) {
            return redirect()->to(base_url($this->redirectBaseUrl))->with('success', 'Pegawai berhasil dihapus (Arsip).');
        }
        return redirect()->back()->with('error', 'Gagal menghapus data.');
    }

    // --- DOKUMEN HANDLER (SYNC FOTO UTAMA) ---
    public function upload_dokumen()
    {
        $id_pegawai = $this->request->getPost('id_pegawai');
        $jenis_dok  = $this->request->getPost('jenis_dokumen');
        
        $validationRule = [
            'file_dokumen' => ['label' => 'File Dokumen', 'rules' => 'uploaded[file_dokumen]|max_size[file_dokumen,5120]'],
        ];

        if (!$this->validate($validationRule)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('file_dokumen');

        if ($file->isValid() && ! $file->hasMoved()) {
            $newName = $file->getRandomName();
            
            // Simpan Dokumen (Path Privat)
            $docPath = 'uploads/pegawai/' . $id_pegawai; 
            if (!is_dir(WRITEPATH . $docPath)) mkdir(WRITEPATH . $docPath, 0777, true);
            $file->move(WRITEPATH . $docPath, $newName);

            // Simpan ke DB Dokumen
            $this->pegawaiDokumenModel->insert([
                'id_pegawai'    => $id_pegawai,
                'jenis_dokumen' => $jenis_dok,
                'nama_file'     => $file->getClientName(),
                'file_path'     => $docPath . '/' . $newName,
                'tipe_file'     => $file->getClientExtension(),
                'ukuran_file'   => $file->getSizeByUnit('kb'),
            ]);

            // --- SYNC: JIKA FOTO, JADIKAN FOTO PROFIL UTAMA ---
            if (in_array(strtoupper($jenis_dok), ['FOTO', 'PAS FOTO', 'PAS FOTO RESMI'])) {
                $publicPath = FCPATH . 'uploads/pegawai';
                if (!is_dir($publicPath)) mkdir($publicPath, 0755, true);
                
                // Salin dari Writable ke Public
                if (copy(WRITEPATH . $docPath . '/' . $newName, $publicPath . '/' . $newName)) {
                    // Update tabel pegawai
                    $this->db->table('pegawai')->where('id', $id_pegawai)->update(['foto' => $newName]);
                }
            }

            return redirect()->back()->with('success', 'Dokumen berhasil diunggah.');
        }

        return redirect()->back()->with('error', 'Gagal mengunggah file.');
    }

    public function download_dokumen($id)
    {
        $dokumen = $this->pegawaiDokumenModel->find($id);
        if (!$dokumen) throw PageNotFoundException::forPageNotFound();

        $path = WRITEPATH . $dokumen['file_path'];
        if (!file_exists($path)) {
             return redirect()->back()->with('error', 'File fisik tidak ditemukan di server.');
        }

        return $this->response->download($path, null)->setFileName($dokumen['jenis_dokumen'] . '_' . $dokumen['nama_file']);
    }

    public function delete_dokumen($id)
    {
        $dokumen = $this->pegawaiDokumenModel->find($id);
        if ($dokumen) {
            $path = WRITEPATH . $dokumen['file_path'];
            if (file_exists($path)) unlink($path);
            
            $this->pegawaiDokumenModel->delete($id);
            return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'Data dokumen tidak ditemukan.');
    }
    
    // --- RIWAYAT PENDIDIKAN & KEPEGAWAIAN (IMPLEMENTASI DASAR) ---
    public function save_pendidikan() { return redirect()->back(); }
    public function delete_pendidikan($id) { return redirect()->back(); }
    public function save_kepegawaian() { return redirect()->back(); }
    public function delete_kepegawaian($id) { return redirect()->back(); }
}