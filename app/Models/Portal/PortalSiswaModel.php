<?php

namespace App\Models\Portal;

use CodeIgniter\Model;

/**
 * PortalSiswaModel
 * Model khusus untuk melayani kebutuhan data di Dashboard Siswa.
 * STATUS: FINAL ROBUST V11 (Integrasi Tahun Ajaran Aktif pada Jadwal)
 */
class PortalSiswaModel extends Model
{
    protected $table            = 'siswa';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'nis', 'nisn', 'nama_lengkap', 'password', 'email', 'id_kelas', 'status'
    ];

    public function getSiswaByNIS($nis)
    {
        return $this->where('nis', $nis)->first();
    }

    /**
     * Ambil Jadwal Mingguan Lengkap (Hanya Tahun Ajaran Aktif)
     */
    public function getJadwalMingguan($kelasId)
    {
        if (empty($kelasId)) return [];
        if (!$this->db->tableExists('jadwal_pelajaran')) return [];

        $builder = $this->db->table('jadwal_pelajaran jp')
            ->select('jp.hari, jp.jam_mulai, jp.jam_selesai')
            // JOIN ke Tahun Ajaran untuk memfilter status AKTIF
            ->join('tahun_ajaran ta', 'ta.id = jp.id_tahun_ajaran')
            ->where('ta.status', 'aktif') 
            ->where('jp.id_kelas', $kelasId)
            ->where('jp.deleted_at', null)
            ->orderBy("FIELD(jp.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
            ->orderBy('jp.jam_mulai', 'ASC');

        // Safe Join Mapel
        if ($this->db->tableExists('mata_pelajaran')) {
            $builder->select('mp.nama_mapel')->join('mata_pelajaran mp', 'mp.id = jp.id_mata_pelajaran', 'left');
        } elseif ($this->db->tableExists('mapel')) {
            $builder->select('mp.nama_mapel')->join('mapel mp', 'mp.id = jp.id_mata_pelajaran', 'left');
        } else {
            $builder->select("'' as nama_mapel");
        }

        // Safe Join Guru
        if ($this->db->tableExists('pegawai')) {
            $builder->select('g.nama_lengkap as nama_guru')->join('pegawai g', 'g.id = jp.id_guru', 'left');
        } elseif ($this->db->tableExists('guru')) {
            $builder->select('g.nama_lengkap as nama_guru')->join('guru g', 'g.id = jp.id_guru', 'left');
        } else {
            $builder->select("'' as nama_guru");
        }

        // Safe Join Ruangan
        if ($this->db->tableExists('sapras_ruangan')) {
            $builder->select('r.nama as ruangan')->join('sapras_ruangan r', 'r.id = jp.id_ruangan', 'left');
        } elseif ($this->db->tableExists('ruangan')) {
            $builder->select('r.nama_ruangan as ruangan')->join('ruangan r', 'r.id = jp.id_ruangan', 'left');
        } else {
            $builder->select("'' as ruangan");
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Ambil Jadwal Harian (Hanya Tahun Ajaran Aktif)
     */
    public function getJadwalHarian($kelasId, $hari)
    {
        if (empty($kelasId)) return [];
        if (!$this->db->tableExists('jadwal_pelajaran')) return [];

        $builder = $this->db->table('jadwal_pelajaran jp')
            ->select('jp.id, jp.jam_mulai, jp.jam_selesai')
            // JOIN ke Tahun Ajaran untuk memfilter status AKTIF
            ->join('tahun_ajaran ta', 'ta.id = jp.id_tahun_ajaran')
            ->where('ta.status', 'aktif')
            ->where('jp.id_kelas', $kelasId)
            ->where('jp.hari', $hari)
            ->where('jp.deleted_at', null)
            ->orderBy('jp.jam_mulai', 'ASC');

        if ($this->db->tableExists('mata_pelajaran')) {
            $builder->select('mp.nama_mapel')->join('mata_pelajaran mp', 'mp.id = jp.id_mata_pelajaran', 'left');
        } else {
            $builder->select("'' as nama_mapel");
        }

        if ($this->db->tableExists('pegawai')) {
            $builder->select('g.nama_lengkap as nama_guru')->join('pegawai g', 'g.id = jp.id_guru', 'left');
        } else {
            $builder->select("'' as nama_guru");
        }

        if ($this->db->tableExists('sapras_ruangan')) {
            $builder->select('r.nama as ruangan, r.nama as ruangan_alt')->join('sapras_ruangan r', 'r.id = jp.id_ruangan', 'left');
        } else {
            $builder->select("'' as ruangan, '' as ruangan_alt");
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Ambil Riwayat Nilai Terbaru
     */
    public function getNilaiTerbaru($siswaId, $limit = 5)
    {
        $tableNilai = null;
        if ($this->db->tableExists('nilai_siswa')) $tableNilai = 'nilai_siswa';
        elseif ($this->db->tableExists('nilai')) $tableNilai = 'nilai';

        if (!$tableNilai) return [];

        $fields = $this->db->getFieldNames($tableNilai);
        $selects = [];

        // Kategori Penilaian
        if (in_array('kategori_nilai', $fields)) $selects[] = 'n.kategori_nilai as nama_penilaian';
        else $selects[] = "'Tugas' as nama_penilaian";

        // Nilai Angka
        if (in_array('nilai_akhir', $fields)) {
            $selects[] = 'n.nilai_akhir as nilai_angka';
        } elseif (in_array('nilai', $fields)) {
            $selects[] = 'n.nilai as nilai_angka';
        } elseif (in_array('nilai_pengetahuan', $fields)) {
            $selects[] = 'n.nilai_pengetahuan as nilai_angka';
        } else {
            $selects[] = '0 as nilai_angka';
        }

        if (in_array('nilai_huruf', $fields)) $selects[] = 'n.nilai_huruf';
        if (in_array('semester', $fields)) $selects[] = 'n.semester';

        $builder = $this->db->table($tableNilai . ' n')
            ->select(implode(', ', $selects))
            ->where('n.id_siswa', $siswaId)
            ->limit($limit);

        // Sorting
        if (in_array('updated_at', $fields)) $builder->orderBy('n.updated_at', 'DESC');
        else $builder->orderBy('n.id', 'DESC');

        // Join Mapel
        $fk_mapel = in_array('id_mata_pelajaran', $fields) ? 'id_mata_pelajaran' : (in_array('id_mapel', $fields) ? 'id_mapel' : null);
        if ($fk_mapel) {
            if ($this->db->tableExists('mata_pelajaran')) {
                $builder->select('mp.nama_mapel')->join('mata_pelajaran mp', 'mp.id = n.' . $fk_mapel, 'left');
            } elseif ($this->db->tableExists('mapel')) {
                $builder->select('mp.nama_mapel')->join('mapel mp', 'mp.id = n.' . $fk_mapel, 'left');
            } else {
                $builder->select("'Mata Pelajaran' as nama_mapel");
            }
        } else {
            $builder->select("'Mata Pelajaran' as nama_mapel");
        }
        
        // Join Jenis Penilaian (Fallback)
        if (!in_array('kategori_nilai', $fields) && $this->db->tableExists('jenis_penilaian')) {
             $builder->select('jp.nama_penilaian')->join('jenis_penilaian jp', 'jp.id = n.id_jenis_penilaian', 'left');
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Ambil Tagihan yang Belum Lunas
     */
    public function getTagihanBelumLunas($siswaId)
    {
        if (!$this->db->tableExists('tagihan')) return [];

        $fields = $this->db->getFieldNames('tagihan');
        $colJumlah = in_array('jumlah', $fields) ? 't.jumlah' : (in_array('nominal', $fields) ? 't.nominal as jumlah' : '0 as jumlah');

        $builder = $this->db->table('tagihan t')
            ->select('t.id, ' . $colJumlah . ', t.tanggal_tagihan, t.status')
            ->where('t.id_siswa', $siswaId)
            ->groupStart()
                ->where('t.status !=', 'lunas')
                ->where('t.status !=', 'Lunas')
            ->groupEnd()
            ->orderBy('t.tanggal_tagihan', 'DESC');

        if ($this->db->tableExists('jenis_pembayaran')) {
            $builder->select('jp.nama_pembayaran')->join('jenis_pembayaran jp', 'jp.id = t.id_jenis_pembayaran', 'left');
        } else {
            $builder->select("'Tagihan' as nama_pembayaran");
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Ambil Riwayat Pembayaran
     */
    public function getRiwayatPembayaran($siswaId, $limit = 5)
    {
        if (!$this->db->tableExists('pembayaran') || !$this->db->tableExists('tagihan')) return [];

        return $this->db->table('pembayaran p')
            ->select('
                p.jumlah_bayar, 
                p.tanggal_bayar, 
                p.metode_pembayaran, 
                p.id_tagihan,
                jp.nama_pembayaran as nama_tagihan
            ')
            ->join('tagihan t', 't.id = p.id_tagihan')
            ->join('jenis_pembayaran jp', 'jp.id = t.id_jenis_pembayaran', 'left')
            ->where('t.id_siswa', $siswaId)
            ->orderBy('p.tanggal_bayar', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil Riwayat Rapor Siswa
     */
    public function getRiwayatRapor($siswaId)
    {
        if (!$this->db->tableExists('raport')) return [];

        return $this->db->table('raport r')
            ->select('
                r.id,
                r.semester,
                r.rata_rata,
                r.status_kenaikan,
                r.status_raport,
                r.created_at as tanggal_terbit,
                ta.tahun_ajaran,
                k.nama_kelas,
                k.tingkat
            ')
            ->join('siswa_enrollment se', 'se.id = r.id_enrollment', 'inner')
            ->join('tahun_ajaran ta', 'ta.id = se.id_tahun_ajaran', 'left')
            ->join('kelas k', 'k.id = se.id_kelas', 'left')
            ->where('se.id_siswa', $siswaId)
            ->where('r.status_raport', 'Final') 
            ->orderBy('ta.tahun_ajaran', 'DESC')
            ->orderBy('r.semester', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil Detail Rapor Tunggal
     */
    public function getDetailRapor($idRapor, $siswaId)
    {
        if (!$this->db->tableExists('raport')) return null;

        return $this->db->table('raport r')
            ->select('r.*, ta.tahun_ajaran, k.nama_kelas, k.kode_jenjang')
            ->join('siswa_enrollment se', 'se.id = r.id_enrollment', 'inner')
            ->join('tahun_ajaran ta', 'ta.id = se.id_tahun_ajaran', 'left')
            ->join('kelas k', 'k.id = se.id_kelas', 'left')
            ->where('r.id', $idRapor)
            ->where('se.id_siswa', $siswaId) 
            ->get()
            ->getRowArray();
    }
}