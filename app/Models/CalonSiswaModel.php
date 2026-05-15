<?php

namespace App\Models;

use CodeIgniter\Model;

class CalonSiswaModel extends Model
{
    protected $table            = 'calon_siswa';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'nomor_pendaftaran', 'nama_lengkap', 'nisn', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'agama', 'alamat', 'asal_sekolah', 'nama_ayah', 'pekerjaan_ayah', 'nama_ibu', 'pekerjaan_ibu',
        'nama_wali', 'telepon_wali', 'file_kk', 'file_akta', 'file_ijazah', 'status_pendaftaran'
    ];

    // Aturan Validasi untuk form pendaftaran yang disempurnakan
    protected $validationRules = [
        'nama_lengkap'  => 'required|min_length[3]',
        'nisn'          => 'required|numeric|exact_length[10]|is_unique[calon_siswa.nisn]',
        'nik'           => 'required|numeric|exact_length[16]|is_unique[calon_siswa.nik]',
        'jenis_kelamin' => 'required|in_list[L,P]',
        'tempat_lahir'  => 'required',
        'tanggal_lahir' => 'required|valid_date',
        'agama'         => 'required',
        'alamat'        => 'required',
        'asal_sekolah'  => 'required',
        'nama_ayah'     => 'required',
        'pekerjaan_ayah' => 'required',
        'nama_ibu'      => 'required',
        'pekerjaan_ibu' => 'required',
        'telepon_wali'  => 'required|numeric|min_length[10]',
        'file_kk'       => 'uploaded[file_kk]|max_size[file_kk,2048]|ext_in[file_kk,pdf,jpg,jpeg,png]',
        'file_akta'     => 'uploaded[file_akta]|max_size[file_akta,2048]|ext_in[file_akta,pdf,jpg,jpeg,png]',
        'file_ijazah'   => 'uploaded[file_ijazah]|max_size[file_ijazah,2048]|ext_in[file_ijazah,pdf,jpg,jpeg,png]',
    ];

    protected $validationMessages = [
        'nisn' => [
            'is_unique' => 'NISN ini sudah terdaftar sebelumnya.'
        ],
        'nik' => [
            'is_unique' => 'NIK ini sudah terdaftar sebelumnya.'
        ],
        'file_kk' => [
            'uploaded' => 'File Kartu Keluarga wajib diunggah.',
            'max_size' => 'Ukuran file Kartu Keluarga tidak boleh lebih dari 2MB.',
            'ext_in'   => 'Format file Kartu Keluarga harus PDF, JPG, atau PNG.',
        ],
        'file_akta' => [
            'uploaded' => 'File Akta Kelahiran wajib diunggah.',
            'max_size' => 'Ukuran file Akta Kelahiran tidak boleh lebih dari 2MB.',
            'ext_in'   => 'Format file Akta Kelahiran harus PDF, JPG, atau PNG.',
        ],
        'file_ijazah' => [
            'uploaded' => 'File Ijazah/SKL wajib diunggah.',
            'max_size' => 'Ukuran file Ijazah/SKL tidak boleh lebih dari 2MB.',
            'ext_in'   => 'Format file Ijazah/SKL harus PDF, JPG, atau PNG.',
        ],
    ];
}

