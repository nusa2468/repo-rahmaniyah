<?php

namespace App\Controllers\Akuntansi;

use App\Controllers\BaseController;
use App\Models\Akuntansi\AkuntansiCoaModel;

class BukuBesarController extends BaseController
{
    protected $coaModel;
    protected $globalIdentifiers = ['GLOBAL', 'YAYASAN', 'PUSAT', 'ROOT'];

    public function __construct()
    {
        $this->coaModel = new AkuntansiCoaModel();
    }

    public function index()
    {
        $sessionJenjang = strtoupper(session('kode_jenjang') ?? 'GLOBAL');
        $isGlobal       = in_array($sessionJenjang, $this->globalIdentifiers);
        
        // FIX: Default ke MULTI (Konsolidasi) jika Superadmin
        $filterJenjang = $this->request->getGet('jenjang') ?? ($isGlobal ? 'MULTI' : $sessionJenjang);
        
        // PROTEKSI RLS
        if (!$isGlobal && $filterJenjang !== $sessionJenjang) {
            $filterJenjang = $sessionJenjang;
        }

        $startDate     = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate       = $this->request->getGet('end_date') ?? date('Y-m-t');
        $idCoa         = $this->request->getGet('id_coa');

        // Master COA selalu ditarik dari GLOBAL karena bersifat tunggal
        $coaList = $this->coaModel->getCoaBuilder('GLOBAL')
                                  ->where('akuntansi_coa.is_parent', 0)
                                  ->get()->getResultArray();

        $mutasi = [];
        $saldoAwal = 0;
        $akunTerpilih = null;

        if ($idCoa) {
            $akunTerpilih = $this->coaModel->find($idCoa);
            
            $db = \Config\Database::connect();
            $saldoBawaan = (float)($akunTerpilih['saldo_awal'] ?? 0);
            $saldoNormal = $this->getSaldoNormal($akunTerpilih['id_kategori']);

            $querySaldoAwal = $db->table('akuntansi_jurnal_detail jd')
                ->selectSum('jd.debit', 'tot_debit')
                ->selectSum('jd.kredit', 'tot_kredit')
                ->join('akuntansi_jurnal j', 'j.id = jd.id_jurnal')
                ->where('jd.id_coa', $idCoa)
                ->where('j.tanggal <', $startDate)
                ->where('j.status', 'Posted');
                
            if ($filterJenjang !== 'MULTI' && $filterJenjang !== 'GLOBAL') {
                $querySaldoAwal->where('j.kode_jenjang', $filterJenjang);
            } elseif ($filterJenjang === 'GLOBAL') {
                $querySaldoAwal->where('j.kode_jenjang', 'GLOBAL');
            }

            $querySaldoAwal = $querySaldoAwal->get()->getRow();

            $totDebAwal = (float)($querySaldoAwal->tot_debit ?? 0);
            $totKreAwal = (float)($querySaldoAwal->tot_kredit ?? 0);

            if ($saldoNormal == 'Debit') {
                $saldoAwal = $saldoBawaan + $totDebAwal - $totKreAwal;
            } else {
                $saldoAwal = $saldoBawaan + $totKreAwal - $totDebAwal;
            }

            $queryMutasi = $db->table('akuntansi_jurnal_detail jd')
                ->select('jd.*, j.tanggal, j.nomor_jurnal, j.deskripsi as deskripsi_jurnal, j.referensi, j.kode_jenjang')
                ->join('akuntansi_jurnal j', 'j.id = jd.id_jurnal')
                ->where('jd.id_coa', $idCoa)
                ->where('j.tanggal >=', $startDate)
                ->where('j.tanggal <=', $endDate)
                ->where('j.status', 'Posted');
                
            if ($filterJenjang !== 'MULTI' && $filterJenjang !== 'GLOBAL') {
                $queryMutasi->where('j.kode_jenjang', $filterJenjang);
            } elseif ($filterJenjang === 'GLOBAL') {
                $queryMutasi->where('j.kode_jenjang', 'GLOBAL');
            }

            $mutasi = $queryMutasi->orderBy('j.tanggal', 'ASC')
                ->orderBy('j.id', 'ASC')
                ->get()->getResultArray();
        }

        $db = \Config\Database::connect();
        $daftarUnit = [];
        if ($db->tableExists('jenjang_sekolah')) {
            $query = $db->table('jenjang_sekolah')->where('status', 'aktif')->orderBy('urutan', 'ASC')->get();
            foreach ($query->getResultArray() as $row) {
                if (!in_array(strtoupper($row['kode_jenjang']), $this->globalIdentifiers)) {
                    $daftarUnit[strtoupper($row['kode_jenjang'])] = $row['nama_jenjang'];
                }
            }
        }

        $data = [
            'title'         => 'Buku Besar (General Ledger)',
            'current_module'=> 'akuntansi',
            'coaList'       => $coaList,
            'mutasi'        => $mutasi,
            'saldoAwal'     => $saldoAwal,
            'akunTerpilih'  => $akunTerpilih,
            'startDate'     => $startDate,
            'endDate'       => $endDate,
            'filterJenjang' => $filterJenjang,
            'isGlobal'      => $isGlobal,
            'daftarUnit'    => $daftarUnit,
            'saldoNormal'   => $akunTerpilih ? $this->getSaldoNormal($akunTerpilih['id_kategori']) : 'Debit'
        ];

        return view('akuntansi/buku_besar/index', $data);
    }

    private function getSaldoNormal($idKategori) {
        $db = \Config\Database::connect();
        $kat = $db->table('akuntansi_kategori')->where('id', $idKategori)->get()->getRow();
        return $kat ? $kat->saldo_normal : 'Debit';
    }
}