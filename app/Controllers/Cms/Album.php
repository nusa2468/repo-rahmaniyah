<?php

namespace App\Controllers\Cms;

use App\Controllers\BaseController;
use App\Models\Cms\AlbumModel; // Pastikan Model ini ada
use App\Models\Cms\FotoModel;  // Pastikan Model ini ada

/**
 * Controller Album (Galeri)
 * Mengelola Galeri Foto dengan dukungan sistem Multi-Unit.
 */
class Album extends BaseController
{
    protected $albumModel;
    protected $fotoModel;
    protected $db;

    public function __construct()
    {
        $this->albumModel = new AlbumModel();
        $this->fotoModel  = new FotoModel();
        $this->db         = \Config\Database::connect();
    }

    /**
     * Helper: Ambil Daftar Unit dari Database (Dinamis)
     */
    private function getDaftarUnit()
    {
        $daftarUnit = [];
        try {
            if ($this->db->tableExists('jenjang_sekolah')) {
                $query = $this->db->table('jenjang_sekolah')->get();
                foreach ($query->getResultArray() as $row) {
                    $val = $row['kode_jenjang'];
                    $label = $row['nama'] ?? $row['nama_jenjang'] ?? $row['kode_jenjang'];
                    $daftarUnit[$val] = $label;
                }
            }
        } catch (\Exception $e) { }
        
        // Fallback jika tabel kosong
        if (empty($daftarUnit)) {
            $daftarUnit = ['TK' => 'TK', 'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA'];
        }
        return $daftarUnit;
    }

    /**
     * Helper: Cek Akses Global
     */
    private function isGlobalAccess(?string $context): bool
    {
        $globalScopes = ['GLOBAL', 'YAYASAN', 'ALL', 'ROOT'];
        return empty($context) || in_array(strtoupper($context), $globalScopes);
    }

    /**
     * Menampilkan daftar album foto.
     */
    public function index()
    {
        // 1. Setup Data Dinamis
        $daftarUnit = $this->getDaftarUnit();
        $sessionJenjang = session('kode_jenjang');
        $isGlobal = $this->isGlobalAccess($sessionJenjang);

        // 2. Filter Logic
        $filterJenjang = $this->request->getGet('jenjang');
        
        // Tentukan Scope Query
        $scopeQuery = $isGlobal ? $filterJenjang : $sessionJenjang;

        // Terapkan Filter pada Model
        if (!empty($scopeQuery)) {
            $this->albumModel->where('kode_jenjang', $scopeQuery);
        }

        // Ambil Data
        $albums = $this->albumModel->orderBy('created_at', 'DESC')->findAll(); // Bisa diganti paginate

        $data = [
            'title'          => 'Kelola Galeri Album',
            'albums'         => $albums,
            
            // UI Helpers
            'sessionJenjang' => $sessionJenjang,
            'isGlobal'       => $isGlobal,
            'filterJenjang'  => $filterJenjang,
            'daftarUnit'     => $daftarUnit
        ];
        return view('cms/album/index', $data);
    }

    /**
     * Form tambah album baru.
     */
    public function new()
    {
        $data = [
            'title'      => 'Buat Album Baru',
            'album'      => null,
            'daftarUnit' => $this->getDaftarUnit()
        ];
        return view('cms/album/form', $data);
    }

    /**
     * Form edit metadata album.
     */
    public function edit($id)
    {
        $album = $this->albumModel->find($id);
        if (!$album) {
            return redirect()->to(base_url('app/cms/album'))->with('error', 'Album tidak ditemukan.');
        }

        // Cek Hak Akses Edit
        $sessionJenjang = session('kode_jenjang');
        if (!$this->isGlobalAccess($sessionJenjang)) {
            $albumUnit = is_array($album) ? $album['kode_jenjang'] : $album->kode_jenjang;
            if (!empty($albumUnit) && $albumUnit !== $sessionJenjang) {
                return redirect()->to(base_url('app/cms/album'))->with('error', 'Anda tidak memiliki akses mengubah album unit lain.');
            }
        }

        $data = [
            'title'      => 'Edit Metadata Album',
            'album'      => (object) $album,
            'daftarUnit' => $this->getDaftarUnit()
        ];
        return view('cms/album/form', $data);
    }

    /**
     * Halaman Manajemen Foto di dalam album.
     */
    public function managePhotos($id)
    {
        $album = $this->albumModel->find($id);
        if (!$album) {
            return redirect()->to(base_url('app/cms/album'))->with('error', 'Album tidak ditemukan.');
        }

        // Cek Hak Akses Manage
        $sessionJenjang = session('kode_jenjang');
        if (!$this->isGlobalAccess($sessionJenjang)) {
            $albumUnit = is_array($album) ? $album['kode_jenjang'] : $album->kode_jenjang;
            if (!empty($albumUnit) && $albumUnit !== $sessionJenjang) {
                return redirect()->to(base_url('app/cms/album'))->with('error', 'Akses ditolak.');
            }
        }

        $data = [
            'title'  => 'Kelola Foto: ' . (is_array($album) ? $album['judul'] : $album->judul),
            'album'  => $album,
            'photos' => $this->fotoModel->getPhotosByAlbum($id) // Pastikan method ini ada di FotoModel
        ];
        return view('cms/album/manage', $data);
    }

    /**
     * Proses Simpan Metadata Album.
     */
    public function save()
    {
        $id = $this->request->getPost('id');
        $judul = $this->request->getPost('judul');
        
        // Logika Penentuan Unit
        $sessionJenjang = session('kode_jenjang');
        $inputJenjang   = $this->request->getPost('kode_jenjang');
        $isGlobal       = $this->isGlobalAccess($sessionJenjang);
        $finalJenjang   = $isGlobal ? $inputJenjang : $sessionJenjang;
        
        if (empty($finalJenjang) || strtoupper($finalJenjang) === 'GLOBAL') {
            $finalJenjang = null;
        }

        $data = [
            'kode_jenjang' => $finalJenjang,
            'judul'        => $judul,
            'slug'         => url_title($judul, '-', true),
            'deskripsi'    => $this->request->getPost('deskripsi'),
            'status'       => $this->request->getPost('status') ?: 'publik',
        ];

        // Upload Cover Album
        $file = $this->request->getFile('cover');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if ($id) {
                $old = $this->albumModel->find($id);
                $oldCover = is_array($old) ? ($old['cover'] ?? null) : ($old->cover ?? null);
                if ($oldCover && file_exists('uploads/galeri/covers/' . $oldCover)) {
                    unlink('uploads/galeri/covers/' . $oldCover);
                }
            }
            $newName = $file->getRandomName();
            $file->move('uploads/galeri/covers', $newName);
            $data['cover'] = $newName;
        }

        try {
            if ($id) {
                $this->albumModel->update($id, $data);
                $msg = 'Album berhasil diperbarui.';
                $redirectId = $id;
            } else {
                $redirectId = $this->albumModel->insert($data);
                $msg = 'Album berhasil dibuat. Silakan tambahkan foto.';
            }
            // Redirect langsung ke halaman manage photos agar user bisa upload foto
            return redirect()->to(base_url('app/cms/album/manage/' . $redirectId))->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan album: ' . $e->getMessage());
        }
    }

    /**
     * Upload foto ke dalam album (Multi-upload).
     */
    public function uploadPhotos()
    {
        $id_album = $this->request->getPost('id_album');
        
        // Security Check: Pastikan user punya hak akses ke album ini
        $album = $this->albumModel->find($id_album);
        if (!$album) return redirect()->back()->with('error', 'Album tidak valid.');
        
        $sessionJenjang = session('kode_jenjang');
        if (!$this->isGlobalAccess($sessionJenjang)) {
            $albumUnit = is_array($album) ? $album['kode_jenjang'] : $album->kode_jenjang;
            if (!empty($albumUnit) && $albumUnit !== $sessionJenjang) {
                return redirect()->back()->with('error', 'Akses ditolak.');
            }
        }

        $files = $this->request->getFiles();

        if ($files && isset($files['photos'])) {
            $count = 0;
            foreach ($files['photos'] as $img) {
                if ($img->isValid() && !$img->hasMoved()) {
                    $originalName = $img->getClientName();
                    $newName = $img->getRandomName();
                    $img->move('uploads/galeri/photos', $newName);

                    $this->fotoModel->insert([
                        'id_album'  => $id_album,
                        'file_foto' => $newName,
                        'caption'   => $originalName
                    ]);
                    $count++;
                }
            }
            if ($count > 0) {
                return redirect()->back()->with('success', "$count foto berhasil ditambahkan.");
            }
        }
        return redirect()->back()->with('error', 'Gagal mengunggah foto. Pastikan format sesuai.');
    }

    /**
     * MENGHAPUS FOTO INDIVIDUAL
     */
    public function deletePhoto($id)
    {
        $photo = $this->fotoModel->find($id);
        if ($photo) {
            // Cek Hak Akses via Parent Album
            $idAlbum = is_array($photo) ? $photo['id_album'] : $photo->id_album;
            $album = $this->albumModel->find($idAlbum);
            
            $sessionJenjang = session('kode_jenjang');
            if (!$this->isGlobalAccess($sessionJenjang)) {
                $albumUnit = is_array($album) ? $album['kode_jenjang'] : $album->kode_jenjang;
                if (!empty($albumUnit) && $albumUnit !== $sessionJenjang) {
                    return redirect()->back()->with('error', 'Akses ditolak.');
                }
            }

            // Hapus Fisik
            $fileName = is_array($photo) ? $photo['file_foto'] : $photo->file_foto;
            if (file_exists('uploads/galeri/photos/' . $fileName)) {
                unlink('uploads/galeri/photos/' . $fileName);
            }
            
            $this->fotoModel->delete($id);
            return redirect()->back()->with('success', 'Foto berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'Foto tidak ditemukan.');
    }

    /**
     * Menghapus seluruh Album beserta seluruh foto di dalamnya.
     */
    public function delete($id)
    {
        $album = $this->albumModel->find($id);
        if ($album) {
            // Cek Hak Akses
            $sessionJenjang = session('kode_jenjang');
            if (!$this->isGlobalAccess($sessionJenjang)) {
                $albumUnit = is_array($album) ? $album['kode_jenjang'] : $album->kode_jenjang;
                if (!empty($albumUnit) && $albumUnit !== $sessionJenjang) {
                    return redirect()->to(base_url('app/cms/album'))->with('error', 'Akses ditolak.');
                }
            }

            // Hapus Cover
            $cover = is_array($album) ? ($album['cover'] ?? null) : ($album->cover ?? null);
            if ($cover && file_exists('uploads/galeri/covers/' . $cover)) {
                unlink('uploads/galeri/covers/' . $cover);
            }

            // Hapus Semua Foto di dalamnya (Looping manual untuk hapus file fisik)
            // Asumsi model punya method getPhotosByAlbum atau where
            $fotos = $this->fotoModel->where('id_album', $id)->findAll();
            foreach ($fotos as $f) {
                $fName = is_array($f) ? $f['file_foto'] : $f->file_foto;
                if (file_exists('uploads/galeri/photos/' . $fName)) {
                    unlink('uploads/galeri/photos/' . $fName);
                }
            }
            
            // Hapus data foto di DB (Cascade delete di DB biasanya handle ini, tapi manual lebih aman untuk file)
            $this->fotoModel->where('id_album', $id)->delete();
            
            // Hapus Album
            $this->albumModel->delete($id);

            return redirect()->to(base_url('app/cms/album'))->with('success', 'Album dan seluruh foto berhasil dihapus.');
        }
        return redirect()->to(base_url('app/cms/album'))->with('error', 'Album tidak ditemukan.');
    }
}