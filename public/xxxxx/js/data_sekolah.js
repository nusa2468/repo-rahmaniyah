/**
 * Contoh Struktur Data untuk Sekolah dan Jurusan.
 * Memastikan bahwa field 'kodeJurusan' dapat bernilai 0
 * untuk mengakomodasi sekolah yang tidak memiliki spesifikasi jurusan.
 */

// Konstanta untuk kode jurusan
const KODE_JURUSAN = {
    TANPA_JURUSAN: 0,
    IPA: 1,
    IPS: 2,
    BAHASA: 3
    // Tambahkan kode jurusan lain sesuai kebutuhan
};

/**
 * Merepresentasikan data sekolah.
 * @typedef {Object} Sekolah
 * @property {string} nama - Nama sekolah.
 * @property {number} kodeJurusan - Kode jurusan (0 jika tidak ada jurusan).
 * @property {string} kota - Kota lokasi sekolah.
 */

/**
 * Daftar contoh data sekolah.
 * Perhatikan contoh 'Sekolah Menengah Umum' yang menggunakan kodeJurusan 0.
 * @type {Sekolah[]}
 */
const daftarSekolah = [
    {
        nama: "SMA 1 Maju",
        kodeJurusan: KODE_JURUSAN.IPA, // 1 (IPA)
        kota: "Jakarta"
    },
    {
        nama: "SMK 2 Teknologi",
        kodeJurusan: KODE_JURUSAN.IPS, // 2 (IPS)
        kota: "Bandung"
    },
    {
        nama: "Sekolah Menengah Umum",
        kodeJurusan: KODE_JURUSAN.TANPA_JURUSAN, // 0 (Tidak ada Jurusan/Umum)
        kota: "Surabaya"
    },
    {
        nama: "Lembaga Pendidikan Khusus",
        kodeJurusan: 0, // Menggunakan nilai 0 secara langsung
        kota: "Yogyakarta"
    },
];

/**
 * Fungsi untuk menampilkan informasi sekolah dan interpretasi kode jurusan.
 */
function tampilkanInfoSekolah() {
    console.log("--- Data Sekolah ---");
    daftarSekolah.forEach(sekolah => {
        let deskripsiJurusan;
        if (sekolah.kodeJurusan === KODE_JURUSAN.TANPA_JURUSAN) {
            deskripsiJurusan = "Tidak Ada Jurusan (Umum)";
        } else if (sekolah.kodeJurusan === KODE_JURUSAN.IPA) {
            deskripsiJurusan = "Ilmu Pengetahuan Alam (IPA)";
        } else if (sekolah.kodeJurusan === KODE_JURUSAN.IPS) {
            deskripsiJurusan = "Ilmu Pengetahuan Sosial (IPS)";
        } else if (sekolah.kodeJurusan === KODE_JURUSAN.BAHASA) {
            deskripsiJurusan = "Bahasa";
        } else {
            deskripsiJurusan = `Kode Jurusan Tidak Dikenal (${sekolah.kodeJurusan})`;
        }

        console.log(`
            Nama Sekolah: ${sekolah.nama}
            Kota: ${sekolah.kota}
            Kode: ${sekolah.kodeJurusan}
            Deskripsi Jurusan: ${deskripsiJurusan}
        `);
    });

    console.log("\nCATATAN Penting:");
    console.log(`Nilai '0' berhasil digunakan untuk merepresentasikan 'Tidak Ada Jurusan'.`);
}

// Panggil fungsi untuk menampilkan data
tampilkanInfoSekolah();

// Contoh penggunaan nilai 0 untuk validasi:
const sekolahBaru = { nama: "Sekolah Desa", kodeJurusan: 0, kota: "Malang" };
if (sekolahBaru.kodeJurusan === 0) {
    console.log(`\nSekolah "${sekolahBaru.nama}" memiliki Jurusan: Umum/Tidak Ada.`);
} else {
    console.log(`\nSekolah "${sekolahBaru.nama}" memiliki Jurusan: Terdefinisi.`);
}