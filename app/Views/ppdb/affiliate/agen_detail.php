<?= $this->extend('layout/main_layout') ?>

<?= $this->section('content') ?>
<style>
    .detail-header { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); border-radius: 1.5rem; padding: 2.5rem; color: white; }
    .avatar-placeholder { width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; }
    .info-label { font-size: 0.75rem; text-transform: uppercase; color: #94a3b8; font-weight: 800; }
    .info-value { font-size: 1rem; font-weight: 700; color: #1e293b; }
</style>

<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="<?= base_url('app/ppdb/affiliate/agen') ?>" class="btn btn-light rounded-pill px-4 shadow-sm border font-weight-bold text-primary">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="detail-header shadow-sm mb-4">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="avatar-placeholder"><?= substr($agen->nama_agen, 0, 1) ?></div>
            </div>
            <div class="col">
                <h5 class="mb-1 opacity-75 font-weight-bold text-uppercase">AGEN: <?= $agen->kode_agen ?></h5>
                <h2 class="font-weight-bold mb-0"><?= $agen->nama_agen ?></h2>
                <div class="mt-2">
                    <span class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm font-weight-bold">
                        <i class="fas fa-bullhorn mr-1"></i> METODE: <?= strtoupper($agen->metode_agen ?? 'GENERAL') ?>
                    </span>
                </div>
            </div>
            <div class="col-md-3 text-right">
                <div class="h3 font-weight-bold mb-0">Rp <?= number_format($agen->fee_per_siswa ?? 0, 0, ',', '.') ?></div>
                <small class="opacity-75">Komisi Per Siswa Lunas</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Profil & Rekening -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-lg mb-4">
                <div class="card-body p-4">
                    <h6 class="font-weight-bold text-primary mb-4 border-left-primary pl-3">Profil & Rekening</h6>
                    
                    <div class="mb-3">
                        <div class="info-label">WhatsApp</div>
                        <div class="info-value"><i class="fab fa-whatsapp text-success mr-1"></i> <?= $agen->no_hp ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?= $agen->email ?? '-' ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Informasi Bank</div>
                        <div class="p-3 bg-light rounded-lg border mt-2">
                            <div class="font-weight-bold text-primary"><?= $agen->nama_bank ?? 'N/A' ?></div>
                            <div class="h6 font-weight-bold mb-0"><?= $agen->nomor_rekening ?? '-' ?></div>
                            <div class="text-xs text-muted mt-1">A/N: <?= $agen->nama_rekening ?? '-' ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Siswa -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-lg mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-graduate mr-2"></i>Siswa Referal Melalui Agen Ini</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small text-uppercase font-weight-bold">
                                <tr>
                                    <th class="pl-4">Nama Siswa</th>
                                    <th>Jalur</th>
                                    <th class="text-center">Pembayaran</th>
                                    <th class="text-center">Seleksi</th>
                                    <th class="text-right pr-4">Fee Agen</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <?php if(!empty($siswa)): ?>
                                    <?php foreach($siswa as $s): ?>
                                    <tr>
                                        <td class="pl-4">
                                            <div class="font-weight-bold"><?= $s->nama_lengkap ?></div>
                                            <div class="text-xs text-muted"><?= $s->no_pendaftaran ?></div>
                                        </td>
                                        <td><span class="badge bg-light border text-dark"><?= $s->jalur_masuk ?></span></td>
                                        <td class="text-center">
                                            <span class="badge <?= $s->status_pembayaran == 'Lunas' ? 'text-success' : 'text-warning' ?>">
                                                <?= $s->status_pembayaran ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge border bg-light text-dark px-2"><?= $s->status_seleksi ?></span>
                                        </td>
                                        <td class="text-right pr-4 font-weight-bold <?= $s->status_pembayaran == 'Lunas' ? 'text-success' : 'text-muted' ?>">
                                            Rp <?= number_format($s->nominal_fee, 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted italic">Agen ini belum memiliki pendaftar terdaftar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>