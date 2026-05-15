<?php

namespace App\Controllers\Kesiswaan;

use App\Controllers\BaseController;
use App\Models\Kesiswaan\EkskulModel;
use App\Models\Kesiswaan\OrganisasiModel;
use App\Models\Kesiswaan\BkModel;
use App\Models\Kesiswaan\AlumniModel;
use App\Models\Kesiswaan\PrestasiSiswaModel; 

class KesiswaanController extends BaseController
{
    protected $ekskulModel;
    protected $organisasiModel;
    protected $bkModel;
    protected $alumniModel;
    protected $prestasiModel;
    protected $session;

    public function __construct()
    {
        $this->ekskulModel     = new EkskulModel();
        $this->organisasiModel = new OrganisasiModel();
        $this->bkModel         = new BkModel();
        $this->alumniModel     = new AlumniModel();
        $this->prestasiModel   = new PrestasiSiswaModel(); 
        $this->session         = session();
    }

    private function getJenjangContext()
    {
        return $this->session->get('kode_jenjang') ?? $this->session->get('kode_unit');
    }

    // --- EKSKUL MASTER ---
    public function store_ekskul()
    {
        $data = $this->request->getPost();
        $data['kode_jenjang'] = $this->getJenjangContext();
        
        if($this->ekskulModel->saveEkskul($data)){
            return redirect()->to('app/kesiswaan?tab=ekskul')->with('success', 'Data Ekskul berhasil disimpan.');
        }
        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data.');
    }

    public function delete_ekskul($id)
    {
        $this->ekskulModel->delete($id);
        return redirect()->to('app/kesiswaan?tab=ekskul')->with('success', 'Data Ekskul berhasil dihapus.');
    }

    // --- ANGGOTA EKSKUL ---
    public function store_anggota_ekskul()
    {
        $data = $this->request->getPost();
        $data['kode_jenjang'] = $this->getJenjangContext();
        
        if(!isset($data['tahun_ajar_id'])) $data['tahun_ajar_id'] = 1;

        if($this->ekskulModel->saveAnggota($data)){
            return redirect()->to('app/kesiswaan?tab=ekskul_anggota')->with('success', 'Anggota berhasil ditambahkan.');
        }
        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan anggota.');
    }

    public function delete_anggota_ekskul($id)
    {
        $this->ekskulModel->deleteAnggota($id);
        return redirect()->to('app/kesiswaan?tab=ekskul_anggota')->with('success', 'Anggota berhasil dihapus.');
    }

    // --- PRESENSI EKSKUL ---
    public function store_presensi_ekskul()
    {
        $data = $this->request->getPost();
        if(empty($data['data_presensi'])) {
            $data['data_presensi'] = json_encode([]); 
        }

        if($this->ekskulModel->savePresensi($data)){
            return redirect()->to('app/kesiswaan?tab=ekskul_presensi')->with('success', 'Presensi berhasil dicatat.');
        }
        return redirect()->back()->withInput()->with('error', 'Gagal mencatat presensi.');
    }

    public function delete_presensi_ekskul($id)
    {
        $this->ekskulModel->deletePresensi($id);
        return redirect()->to('app/kesiswaan?tab=ekskul_presensi')->with('success', 'Data presensi berhasil dihapus.');
    }

    // --- BK (BIMBINGAN KONSELING) ---
    public function store_kasus_bk()
    {
        $data = $this->request->getPost();
        $data['kode_jenjang'] = $this->getJenjangContext();

        if(!isset($data['status_penyelesaian'])) $data['status_penyelesaian'] = 'Open';
        if(!isset($data['tahun_ajar_id'])) $data['tahun_ajar_id'] = 1; 
        
        if($this->bkModel->saveKasusBK($data)){
            return redirect()->to('app/kesiswaan?tab=bk')->with('success', 'Kasus BK berhasil dicatat.');
        }
        return redirect()->back()->withInput()->with('error', 'Gagal mencatat kasus.');
    }

    public function delete_kasus_bk($id)
    {
        $this->bkModel->delete($id);
        return redirect()->to('app/kesiswaan?tab=bk')->with('success', 'Data kasus berhasil dihapus.');
    }

    // --- ORGANISASI (OSIS/MPK) ---
    public function store_organisasi()
    {
        $data = $this->request->getPost();
        $data['kode_jenjang'] = $this->getJenjangContext();

        if(!isset($data['tahun_ajar_id'])) $data['tahun_ajar_id'] = 1; 
        $data['status_aktif'] = isset($data['status_aktif']) ? 1 : 0; 

        if($this->organisasiModel->saveOrganisasi($data)){
            return redirect()->to('app/kesiswaan?tab=organisasi')->with('success', 'Pengurus berhasil disimpan.');
        }
        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data.');
    }

    public function delete_organisasi($id)
    {
        $this->organisasiModel->delete($id);
        return redirect()->to('app/kesiswaan?tab=organisasi')->with('success', 'Data pengurus berhasil dihapus.');
    }

    // --- ALUMNI (TRACER STUDY) ---
    public function store_alumni()
    {
        $data = $this->request->getPost();
        $data['kode_jenjang'] = $this->getJenjangContext();

        if($this->alumniModel->saveAlumni($data)){
            return redirect()->to('app/kesiswaan?tab=alumni')->with('success', 'Data Alumni berhasil disimpan.');
        }
        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data.');
    }

    public function delete_alumni($id)
    {
        $this->alumniModel->delete($id);
        return redirect()->to('app/kesiswaan?tab=alumni')->with('success', 'Data alumni berhasil dihapus.');
    }

    // --- PRESTASI (NEW FEATURE) ---
    public function store_prestasi()
    {
        $data = $this->request->getPost();
        
        // FIX: Pastikan key yang digunakan adalah 'tahun_ajar_id', bukan 'id_tahun_ajaran'
        if (!isset($data['tahun_ajar_id']) || empty($data['tahun_ajar_id'])) {
            $data['tahun_ajar_id'] = 1; // Fallback default
        }
        
        // Jika ada id_tahun_ajaran yang terkirim (legacy), konversi ke tahun_ajar_id
        if (isset($data['id_tahun_ajaran']) && !isset($data['tahun_ajar_id'])) {
            $data['tahun_ajar_id'] = $data['id_tahun_ajaran'];
            unset($data['id_tahun_ajaran']);
        }

        if($this->prestasiModel->save($data)){
             return redirect()->to('app/kesiswaan?tab=prestasi')->with('success', 'Prestasi berhasil dicatat.');
        }
        
        $errors = $this->prestasiModel->errors();
        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data. ' . implode(', ', $errors));
    }

    public function delete_prestasi($id)
    {
        $this->prestasiModel->delete($id);
        return redirect()->to('app/kesiswaan?tab=prestasi')->with('success', 'Data prestasi berhasil dihapus.');
    }
}