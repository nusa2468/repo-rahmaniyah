<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header + Back Button -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Input Absensi Harian
            </h1>
            <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">
                Unit: <span class="font-bold text-indigo-600 dark:text-indigo-400"><?= esc($kode_jenjang ?? 'N/A') ?></span>
                | Kelas: <span class="font-bold text-purple-600 dark:text-purple-400"><?= esc($nama_kelas_display) ?></span>
            </p>
            <p class="text-lg font-medium text-gray-700 dark:text-gray-300">
                Tanggal: <?= esc(\CodeIgniter\I18n\Time::parse($tanggal)->toLocalizedString('dddd, d MMMM yyyy')) ?>
            </p>
        </div>
        <a href="<?= base_url('app/akademik/absensi-siswa') ?>"
           class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-xl shadow-lg transition transform hover:scale-105">
            <i class="fas fa-arrow-left mr-3"></i>
            Ganti Kelas / Tanggal
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->has('error')): ?>
        <div class="mb-6 p-5 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-xl flex items-center shadow-md">
            <i class="fas fa-exclamation-triangle mr-3 text-2xl"></i>
            <?= esc(session('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->has('warning')): ?>
        <div class="mb-6 p-5 bg-amber-100 border-l-4 border-amber-500 text-amber-800 rounded-xl flex items-center shadow-md">
            <i class="fas fa-exclamation-circle mr-3 text-2xl"></i>
            <?= esc(session('warning')) ?>
        </div>
    <?php endif; ?>

    <!-- Form Absensi -->
    <form action="<?= base_url('app/akademik/absensi-siswa/simpan') ?>" method="post" class="space-y-6">
        <?= csrf_field() ?>
        <input type="hidden" name="tanggal" value="<?= esc($tanggal) ?>">
        <input type="hidden" name="id_kelas" value="<?= esc($id_kelas) ?>">

        <?php if (empty($siswa_di_kelas)): ?>
            <div class="text-center py-16 bg-gray-50 dark:bg-gray-800 rounded-2xl">
                <i class="fas fa-users-slash text-6xl text-gray-400 mb-6"></i>
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    Tidak ada siswa aktif di kelas ini pada tahun ajaran aktif.
                </p>
            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header Jadwal -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-book-open mr-3"></i>
                        Daftar Mata Pelajaran Hari Ini (<?= count($jadwal_hari_ini) ?> Mapel)
                    </h2>
                </div>

                <!-- Tabel Absensi -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-6 py-4 text-left font-bold text-gray-700 dark:text-gray-200 w-12">#</th>
                                <th class="px-6 py-4 text-left font-bold text-gray-700 dark:text-gray-200 min-w-48">Nama Siswa</th>
                                <?php foreach ($jadwal_hari_ini as $jadwal): ?>
                                    <th class="px-6 py-4 text-center font-bold text-gray-700 dark:text-gray-200 min-w-40">
                                        <div class="space-y-1">
                                            <div class="font-bold text-base"><?= esc($jadwal['nama_mapel']) ?></div>
                                            <div class="text-xs opacity-90">
                                                <?= date('H:i', strtotime($jadwal['jam_mulai'])) ?> - <?= date('H:i', strtotime($jadwal['jam_selesai'])) ?>
                                            </div>
                                            <div class="text-xs font-medium text-indigo-200 bg-indigo-800/30 px-2 py-1 rounded mt-1 inline-block">
                                                <?= esc($jadwal['nama_guru'] ?? 'Guru Tidak Diketahui') ?>
                                            </div>
                                        </div>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php $no = 1; foreach ($siswa_di_kelas as $siswa): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-6 py-5 text-center font-medium text-gray-600 dark:text-gray-400"><?= $no++ ?></td>
                                    <td class="px-6 py-5">
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            <?= esc($siswa['nama_siswa']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            NIS: <?= esc($siswa['nis'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <?php foreach ($jadwal_hari_ini as $jadwal): ?>
                                        <?php
                                        $current_status = $absensi_tersimpan[$siswa['id']][$jadwal['id']] ?? 'hadir';
                                        $name = "absensi[{$siswa['id']}][{$jadwal['id']}]";
                                        ?>
                                        <td class="px-6 py-5 text-center">
                                            <div class="flex justify-center gap-2">
                                                <!-- Hadir -->
                                                <input type="radio" name="<?= $name ?>" value="hadir"
                                                       id="hadir_<?= $siswa['id'] ?>_<?= $jadwal['id'] ?>"
                                                       class="hidden peer/hadir"
                                                       <?= $current_status === 'hadir' ? 'checked' : '' ?>>
                                                <label for="hadir_<?= $siswa['id'] ?>_<?= $jadwal['id'] ?>"
                                                       class="cursor-pointer px-4 py-2 rounded-lg font-semibold text-sm transition
                                                              peer-checked/hadir:bg-green-600 peer-checked/hadir:text-white
                                                              bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                    H
                                                </label>

                                                <!-- Sakit -->
                                                <input type="radio" name="<?= $name ?>" value="sakit"
                                                       id="sakit_<?= $siswa['id'] ?>_<?= $jadwal['id'] ?>"
                                                       class="hidden peer/sakit"
                                                       <?= $current_status === 'sakit' ? 'checked' : '' ?>>
                                                <label for="sakit_<?= $siswa['id'] ?>_<?= $jadwal['id'] ?>"
                                                       class="cursor-pointer px-4 py-2 rounded-lg font-semibold text-sm transition
                                                              peer-checked/sakit:bg-yellow-600 peer-checked/sakit:text-white
                                                              bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                    S
                                                </label>

                                                <!-- Izin -->
                                                <input type="radio" name="<?= $name ?>" value="izin"
                                                       id="izin_<?= $siswa['id'] ?>_<?= $jadwal['id'] ?>"
                                                       class="hidden peer/izin"
                                                       <?= $current_status === 'izin' ? 'checked' : '' ?>>
                                                <label for="izin_<?= $siswa['id'] ?>_<?= $jadwal['id'] ?>"
                                                       class="cursor-pointer px-4 py-2 rounded-lg font-semibold text-sm transition
                                                              peer-checked/izin:bg-orange-600 peer-checked/izin:text-white
                                                              bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                    I
                                                </label>

                                                <!-- Alpa -->
                                                <input type="radio" name="<?= $name ?>" value="alpa"
                                                       id="alpa_<?= $siswa['id'] ?>_<?= $jadwal['id'] ?>"
                                                       class="hidden peer/alpa"
                                                       <?= $current_status === 'alpa' ? 'checked' : '' ?>>
                                                <label for="alpa_<?= $siswa['id'] ?>_<?= $jadwal['id'] ?>"
                                                       class="cursor-pointer px-4 py-2 rounded-lg font-semibold text-sm transition
                                                              peer-checked/alpa:bg-red-600 peer-checked/alpa:text-white
                                                              bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                    A
                                                </label>
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer + Simpan -->
                <div class="px-6 py-5 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex justify-end">
                        <button type="submit"
                                class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold text-lg rounded-xl shadow-2xl transform transition hover:scale-105 flex items-center">
                            <i class="fas fa-save mr-3 text-xl"></i>
                            Simpan Seluruh Absensi
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </form>
</div>

<?= $this->endSection() ?>