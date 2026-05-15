<?php
// PerkuliahanController.php
// Bertindak sebagai perantara antara Model dan View.
// Mengatur logika aplikasi.

// Memuat file Model yang diperlukan
require_once 'PerkuliahanModel.php';

class PerkuliahanController {
    
    private $model;

    public function __construct() {
        // Membuat instance baru dari Model
        $this->model = new PerkuliahanModel();
    }

    /**
     * Metode utama untuk menampilkan halaman daftar perkuliahan (index).
     */
    public function index() {
        // 1. Mengambil data dari Model
        $dataPerkuliahan = $this->model->getAllPerkuliahan();

        // 2. Memuat file View dan mengirimkan data ke dalamnya
        // Dalam framework sungguhan, ini biasanya:
        // return view('view_perkuliahan', ['data' => $dataPerkuliahan]);
        
        // Untuk PHP native, kita 'include' filenya:
        include 'view_perkuliahan.php';
    }

    /**
     * Metode untuk menampilkan form tambah data (Contoh).
     */
    public function create() {
        // Logika untuk menampilkan form tambah
        // include 'view_tambah_perkuliahan.php';
        echo "Halaman untuk menambah data perkuliahan.";
    }

    /**
     * Metode untuk menghapus data (Contoh).
     */
    public function delete($id) {
        $this->model->deletePerkuliahan($id);
        // Redirect kembali ke halaman index
        // header('Location: index.php'); // (index.php akan menjadi router)
        echo "Data dengan ID $id telah dihapus. Mengarahkan kembali ke daftar...";
    }

    // Metode lain seperti store(), edit(), update() bisa ditambahkan di sini.
}
?>
