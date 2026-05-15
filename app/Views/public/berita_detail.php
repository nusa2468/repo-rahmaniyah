<?= $this->extend('layout/public_layout') ?>

<?= $this->section('content') ?>
<div class="container my-5 pt-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="<?= base_url(strtolower($jenjang)) ?>" class="text-primary">Beranda <?= $jenjang ?></a></li>
            <li class="breadcrumb-item active">Berita</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <article class="bg-white p-4 p-md-5 shadow-sm rounded-lg">
                <header class="mb-4">
                    <h1 class="font-weight-bold text-dark display-4" style="font-size: 2.5rem;"><?= esc($berita['judul']) ?></h1>
                    <div class="d-flex align-items-center text-muted small mt-3">
                        <span class="mr-3"><i class="fas fa-user-circle mr-1"></i> <?= esc($berita['penulis'] ?? 'Admin') ?></span>
                        <span class="mr-3"><i class="fas fa-calendar-alt mr-1"></i> <?= date('d M Y', strtotime($berita['created_at'])) ?></span>
                        <span><i class="fas fa-tag mr-1"></i> <?= esc($berita['jenjang']) ?></span>
                    </div>
                </header>

                <?php if ($berita['gambar']): ?>
                    <img src="<?= base_url('uploads/berita/' . $berita['gambar']) ?>" class="img-fluid rounded-lg mb-4 w-100 shadow-sm" alt="<?= esc($berita['judul']) ?>">
                <?php endif; ?>

                <div class="article-content lh-lg text-dark" style="font-size: 1.1rem;">
                    <!-- Merender konten dari TinyMCE -->
                    <?= $berita['konten'] ?>
                </div>

                <hr class="my-5">
                <div class="share-post">
                    <span class="font-weight-bold mr-3">Bagikan:</span>
                    <a href="#" class="btn btn-outline-primary btn-sm rounded-circle"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-info btn-sm rounded-circle"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="btn btn-outline-success btn-sm rounded-circle"><i class="fab fa-whatsapp"></i></a>
                </div>
            </article>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 100px;">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="font-weight-bold mb-3">Unit Terkait</h5>
                        <div class="list-group list-group-flush">
                            <a href="<?= base_url('sd') ?>" class="list-group-item list-group-item-action border-0 px-0">SD Islam Terpadu</a>
                            <a href="<?= base_url('smp') ?>" class="list-group-item list-group-item-action border-0 px-0">SMP Islam Terpadu</a>
                            <a href="<?= base_url('sma') ?>" class="list-group-item list-group-item-action border-0 px-0">SMA Islam Terpadu</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .article-content img { max-width: 100%; height: auto; border-radius: 10px; }
    .article-content p { margin-bottom: 1.5rem; }
</style>
<?= $this->endSection() ?>