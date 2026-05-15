<?php

namespace App\Models\Kepegawaian;

use CodeIgniter\Model;

/**
 * AbsensiPegawaiModel (Enterprise Unified Edition)
 * Mengelola data presensi seluruh SDM (Guru & Staff).
 */
class AbsensiPegawaiModel extends Model
{
    protected $table            = 'absensi_pegawai';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object'; 
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $allowedFields    = [
        'id_pegawai', 'kode_jenjang', 'tanggal', 'jam_masuk', 'jam_keluar',
        'status', 'metode_absen', 'keterangan', 'bukti_foto_masuk', 'bukti_foto_keluar', 'id_user_admin'
    ];

    /**
     * Logic Scoping Unit & Tipe.
     */
    protected function applyScope($builder, $type = 'guru', $filterJenjang = null, $skipJoin = false)
    {
        $session = session();
        $userJenjang = strtoupper($session->get('kode_jenjang') ?? 'GLOBAL');
        $isGlobal = in_array($userJenjang, ['GLOBAL', 'YAYASAN', 'PUSAT']);

        if (!$skipJoin) {
            $builder->join('pegawai p', 'p.id = ' . $this->table . '.id_pegawai');
        }

        if ($type === 'staff') {
            $builder->whereIn('p.jenis_pegawai', ['staff', 'penunjang']);
        } else {
            $builder->where('p.jenis_pegawai', 'guru');
        }

        if (!$isGlobal) {
            $builder->where('p.kode_jenjang', $userJenjang);
        } else {
            if (!empty($filterJenjang) && !in_array(strtoupper($filterJenjang), ['GLOBAL', 'ALL'])) {
                $builder->where('p.kode_jenjang', $filterJenjang);
            }
        }
    }

    public function getAbsensiHarian($tanggal, $type = 'guru', $filterJenjang = null)
    {
        $builder = $this->builder();
        $builder->select($this->table . '.*, p.nama_lengkap as nama_pegawai, p.kode_jenjang as unit_pegawai, p.nip as nip_pegawai');
        $this->applyScope($builder, $type, $filterJenjang, false);
        $builder->where($this->table . '.tanggal', $tanggal);
        $builder->where($this->table . '.deleted_at', null);
        return $builder->orderBy('p.nama_lengkap', 'ASC')->get()->getResult();
    }

    public function getDailyStats($tanggal, $type = 'guru', $filterJenjang = null)
    {
        $builder = $this->builder();
        $builder->select('
            COUNT(' . $this->table . '.id) as total_absen,
            SUM(CASE WHEN ' . $this->table . '.status = "hadir" THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN ' . $this->table . '.status = "terlambat" THEN 1 ELSE 0 END) as terlambat,
            SUM(CASE WHEN ' . $this->table . '.status IN ("sakit", "izin", "alpa", "cuti") THEN 1 ELSE 0 END) as absen_izin
        ');
        $this->applyScope($builder, $type, $filterJenjang, false);
        $builder->where($this->table . '.tanggal', $tanggal);
        return $builder->get()->getRow();
    }

    /**
     * FITUR REKAPITULASI (FIXED GROUP BY & QUERY)
     */
    public function getRekapBulanan($bulan, $tahun, $type = 'guru', $filterJenjang = null)
    {
        $builder = $this->db->table('pegawai p');
        
        $builder->select('
            p.id as id_pegawai, p.nama_lengkap, p.nip, p.kode_jenjang,
            COUNT(a.id) as total_log,
            SUM(CASE WHEN a.status = "hadir" THEN 1 ELSE 0 END) as jml_hadir,
            SUM(CASE WHEN a.status = "sakit" THEN 1 ELSE 0 END) as jml_sakit,
            SUM(CASE WHEN a.status = "izin" THEN 1 ELSE 0 END) as jml_izin,
            SUM(CASE WHEN a.status = "alpa" THEN 1 ELSE 0 END) as jml_alpa,
            SUM(CASE WHEN a.status = "terlambat" THEN 1 ELSE 0 END) as jml_terlambat,
            SUM(CASE WHEN a.status = "cuti" THEN 1 ELSE 0 END) as jml_cuti,
            SUM(CASE WHEN a.status = "dinas_luar" THEN 1 ELSE 0 END) as jml_dinas
        ');

        // Gunakan Parameter Binding untuk keamanan dan tipe data yang tepat
        $b = (int)$bulan;
        $t = (int)$tahun;
        
        // LEFT JOIN agar pegawai yang BELUM absen tetap muncul di list
        $builder->join($this->table . ' a', "a.id_pegawai = p.id AND MONTH(a.tanggal) = $b AND YEAR(a.tanggal) = $t AND a.deleted_at IS NULL", 'left');

        // Terapkan scope tanpa join ulang
        $this->applyScope($builder, $type, $filterJenjang, true);

        // FIX: Group By lengkap untuk kompatibilitas SQL Mode 'ONLY_FULL_GROUP_BY'
        $builder->where('p.status_aktif', 'aktif')
                ->groupBy(['p.id', 'p.nama_lengkap', 'p.nip', 'p.kode_jenjang'])
                ->orderBy('p.nama_lengkap', 'ASC');

        return $builder->get()->getResult();
    }

    public function autoRecord(int $idPegawai, string $metode = 'fingerprint')
    {
        $today = date('Y-m-d');
        $now   = date('H:i:s');
        $existing = $this->where(['id_pegawai' => $idPegawai, 'tanggal' => $today])->first();

        if (!$existing) {
            $pegawai = $this->db->table('pegawai')->select('kode_jenjang')->where('id', $idPegawai)->get()->getRow();
            return $this->insert([
                'id_pegawai'   => $idPegawai,
                'kode_jenjang' => $pegawai->kode_jenjang ?? 'GLOBAL',
                'tanggal'      => $today,
                'jam_masuk'    => $now,
                'status'       => 'hadir',
                'metode_absen' => $metode,
                'keterangan'   => 'Check-in sistem otomatis'
            ]);
        } else if (empty($existing->jam_keluar)) {
            $masukTime = strtotime($existing->jam_masuk);
            if ((strtotime($now) - $masukTime) > 300) { 
                return $this->update($existing->id, [
                    'jam_keluar' => $now,
                    'keterangan' => ($existing->keterangan ?? '') . ' | Check-out sistem otomatis'
                ]);
            }
        }
        return false;
    }
}