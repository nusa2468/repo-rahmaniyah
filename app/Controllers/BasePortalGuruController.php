<?php 

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;
// Services
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\Services;
use Exception; // Diperlukan untuk try-catch

// Import semua Models yang dibutuhkan
use App\Models\GuruModel;
use App\Models\JadwalPelajaranModel;
use App\Models\Elearning\VirtualClassModel; 
use App\Models\UserModel;
use App\Models\KelasModel;
use App\Models\MataPelajaranModel;
use App\Models\SiswaModel;
use App\Models\AbsensiSiswaModel;
use App\Models\NilaiModel;
use App\Models\TahunAjaranModel;


/**
 * Class BasePortalGuruController
 *
 * Kontroler Induk untuk semua modul di Portal Guru.
 * Berisi inisialisasi Model dan logika otorisasi dasar.
 * Semua kontroler lain di PortalGuru HARUS mewarisi (extend) dari kelas ini.
 */
abstract class BasePortalGuruController extends Controller
{
    // Deklarasi properti model dengan Type Hinting.
    protected GuruModel $guruModel;
    protected JadwalPelajaranModel $jadwalModel;
    protected VirtualClassModel $virtualClassModel;
    protected UserModel $userModel;
    protected KelasModel $kelasModel;
    protected MataPelajaranModel $mapelModel;
    protected SiswaModel $siswaModel;
    protected AbsensiSiswaModel $absensiModel;
    protected NilaiModel $nilaiModel;
    protected TahunAjaranModel $taModel;    
    
    // Properti CodeIgniter 4
    protected $session;
    protected $helpers = ['url', 'form', 'session']; // Helper dimuat otomatis melalui properti ini

    /**
     * Metode initController CI4.
     * Digunakan untuk inisialisasi services dan models.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Panggil parent initController terlebih dahulu
        parent::initController($request, $response, $logger);
        
        // 1. Load Services
        $this->session = Services::session();

        // 2. Inisialisasi semua Model dalam blok try-catch
        try {
            $this->guruModel = new GuruModel();
            $this->jadwalModel = new JadwalPelajaranModel();
            // Penting: Pastikan path ini benar (app/Models/Elearning/VirtualClassModel.php)
            $this->virtualClassModel = new VirtualClassModel(); 
            $this->userModel = new UserModel();
            $this->kelasModel = new KelasModel();
            $this->mapelModel = new MataPelajaranModel();
            $this->siswaModel = new SiswaModel();
            $this->absensiModel = new AbsensiSiswaModel();
            $this->nilaiModel = new NilaiModel();
            $this->taModel = new TahunAjaranModel();
        } catch (Exception $e) {
            // Jika ada Model yang gagal diinisialisasi (misalnya: file hilang, namespace salah),
            // log error KRITIS.
            log_message('critical', 'Gagal memuat salah satu Model di BasePortalGuruController: ' . $e->getMessage());
            
            // Pada mode development, Anda bisa melemparkan (throw) error agar tampil di layar:
            // throw new \RuntimeException('Gagal memuat Model. Cek log atau pastikan semua file Model ada.', 500, $e);
            
            // Catatan: Jika error terjadi di sini, properti Model akan kosong, 
            // dan Controller turunan (misalnya ProfilController) akan mendapatkan error
            // "Undefined Property" saat mencoba mengakses $this->guruModel.
        }
    }
    
    /**
     * Helper untuk pengecekan peran yang diizinkan untuk mengakses sebuah method.
     * @param array $allowedRoles Daftar peran yang diizinkan, e.g., ['guru', 'admin']
     * @return bool|RedirectResponse Mengembalikan true jika sukses, atau RedirectResponse jika gagal.
     */
    protected function checkAccess(array $allowedRoles): bool|RedirectResponse
    {
        $isLoggedIn = $this->session->get('isLoggedInGuru'); // Flag login khusus guru
        $roleName = $this->session->get('role_name');
        $idGuru = $this->session->get('guru_id'); 

        // 1. Cek Login
        if (!$isLoggedIn) {
            $this->session->setFlashdata('error', 'Sesi Anda telah berakhir. Silakan login ulang.');
            return redirect()->to(base_url('portal/guru/login'));
        }
        
        // 2. Cek Role
        if (!in_array($roleName, $allowedRoles)) {
            $errorMessage = 'Anda tidak memiliki akses ke halaman ini. Role Anda: ' . ucfirst($roleName);
            $this->session->setFlashdata('error', $errorMessage);
            
            // Redirect sesuai peran (jika admin terlogin, kembalikan ke dashboard admin, dll.)
            $target = (strtolower($roleName) === 'admin') ? base_url('app/dashboard') : base_url('portal/guru/login');

            return redirect()->to($target);
        }

        // 3. Cek kelengkapan ID Guru
        if ($roleName === 'guru' && ($idGuru === null || !is_numeric($idGuru))) {
            log_message('error', 'Guru ID di sesi tidak ditemukan atau tidak valid.');
            $this->session->destroy();
            $this->session->setFlashdata('error', 'Data sesi Guru tidak valid. Silakan login ulang.');
            return redirect()->to(base_url('portal/guru/login'));
        }
        
        return true;
    }

    /**
     * Mengambil Tahun Ajaran dan Semester aktif (Logika Helper).
     * @return array|null
     */
    protected function getActiveContext(): ?array
    {
        // Pengecekan krusial: pastikan Model terinisialisasi
        if (!isset($this->taModel)) {
             log_message('error', 'taModel belum terinisialisasi. Kemungkinan error saat initController.');
             return null;
        }

        // Ambil TA aktif yang terakhir
        $activeTA = $this->taModel->where('status', 'aktif')->orderBy('id', 'DESC')->first();
        
        if (!$activeTA) {
            // Jika tidak ada TA aktif, kembalikan null atau default
            return null;
        }

        return [
            'id_tahun_ajaran'   => $activeTA['id'],
            'nama_tahun_ajaran' => $activeTA['nama'] ?? 'TA Tidak Didefinisikan',
            'semester'          => $activeTA['semester'] ?? 'Ganjil'
        ];
    }
}