<?php 
echo $this->extend('layout/portal_layout'); 
echo $this->section('content');
?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-light rounded p-4 shadow-sm">
                <h5 class="mb-4">Rekap Absensi Siswa: <?= esc($siswa['nama_lengkap']) ?> (NIS: <?= esc($siswa['nis']) ?>)</h5>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Mata Pelajaran</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rekap_absensi)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data absensi dalam 30 hari terakhir.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($rekap_absensi as $r): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= esc(date('d F Y', strtotime($r['tanggal']))) ?></td>
                                        <td><?= esc($r['nama_mapel'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php 
                                                $status = strtolower($r['status']);
                                                $badge_class = '';
                                                if ($status == 'hadir') $badge_class = 'bg-success';
                                                elseif ($status == 'sakit') $badge_class = 'bg-warning';
                                                elseif ($status == 'izin') $badge_class = 'bg-info';
                                                elseif ($status == 'alpa') $badge_class = 'bg-danger';
                                                else $badge_class = 'bg-secondary';
                                            ?>
                                            <span class="badge <?= $badge_class ?>"><?= esc(ucwords($status)) ?></span>
                                        </td>
                                        <td><?= esc($r['keterangan'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-muted">Data ditampilkan untuk 30 riwayat terakhir.</p>
            </div>
        </div>
    </div>
</div>

<?php 
echo $this->endSection(); 
?>