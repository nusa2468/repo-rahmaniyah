<!-- ========================================================================
     MODALS KESISWAAN
     Pastikan ID di sini cocok dengan document.getElementById di scripts.php
     ======================================================================== -->

<!-- 1. MODAL EKSKUL -->
<dialog id="modalEkskul" class="modal rounded-3xl p-0 w-full max-w-lg backdrop:bg-slate-900/50">
    <form action="<?= base_url('app/kesiswaan/store_ekskul') ?>" method="POST" class="p-8 bg-white" id="formEkskul">
        <?= csrf_field() ?> 
        <input type="hidden" name="id" id="ekskul_id">
        <h3 class="font-bold text-lg mb-6 text-slate-800">Kelola Ekskul</h3>
        
        <div class="space-y-4">
            <?php if(isset($isGlobal) && $isGlobal): ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Unit Sekolah</label>
                <select name="kode_jenjang" required class="w-full px-4 py-2 rounded-xl border border-slate-200">
                    <option value="" disabled selected>Pilih Unit...</option>
                    <?php if(isset($jenjang_list) && !empty($jenjang_list)): ?>
                        <?php foreach($jenjang_list as $j): ?>
                            <option value="<?= $j['kode_jenjang'] ?>"><?= $j['nama_jenjang'] ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div><label class="block text-sm font-medium mb-1">Nama Ekskul</label><input type="text" name="nama_ekskul" id="nama_ekskul" required class="w-full px-4 py-2 rounded-xl border border-slate-200"></div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Guru Pembina</label>
                <select name="guru_pembina_id" id="guru_pembina_id" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="" disabled selected>Pilih Pembina...</option>
                    <?php if(isset($guru_list)): foreach($guru_list as $guru): ?>
                        <option value="<?= $guru['id'] ?>"><?= $guru['nama_lengkap'] ?> (<?= $guru['nip'] ?? '-' ?>)</option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium mb-1">Kategori</label><select name="kategori" id="kategori" class="w-full px-4 py-2 rounded-xl border border-slate-200"><option value="Olahraga">Olahraga</option><option value="Seni">Seni</option><option value="Sains">Sains</option><option value="Lainnya">Lainnya</option></select></div>
                <div><label class="block text-sm font-medium mb-1">Hari</label><select name="hari_latihan" id="hari_latihan" class="w-full px-4 py-2 rounded-xl border border-slate-200"><option value="Jumat">Jumat</option><option value="Sabtu">Sabtu</option><option value="Minggu">Minggu</option></select></div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium mb-1">Mulai</label><input type="time" name="jam_mulai" id="jam_mulai" required class="w-full px-4 py-2 rounded-xl border border-slate-200"></div>
                <div><label class="block text-sm font-medium mb-1">Selesai</label><input type="time" name="jam_selesai" id="jam_selesai" required class="w-full px-4 py-2 rounded-xl border border-slate-200"></div>
            </div>
            
            <div><label class="block text-sm font-medium mb-1">Deskripsi</label><textarea name="deskripsi" id="deskripsi" class="w-full px-4 py-2 rounded-xl border border-slate-200"></textarea></div>
        </div>
        
        <div class="mt-8 flex justify-end gap-3">
            <button type="button" onclick="closeModal('modalEkskul')" class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100">Batal</button>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700">Simpan</button>
        </div>
    </form>
</dialog>

<!-- 2. MODAL ANGGOTA -->
<dialog id="modalAnggota" class="modal rounded-3xl p-0 w-full max-w-lg backdrop:bg-slate-900/50">
    <form action="<?= base_url('app/kesiswaan/store_anggota_ekskul') ?>" method="POST" class="p-8 bg-white" id="formAnggota">
        <?= csrf_field() ?> 
        <input type="hidden" name="id" id="anggota_id">
        <h3 class="font-bold text-lg mb-6 text-slate-800">Kelola Anggota Ekskul</h3>
        
        <div class="space-y-4">
             <?php if(isset($isGlobal) && $isGlobal): ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Unit Sekolah</label>
                <select name="kode_jenjang" required class="w-full px-4 py-2 rounded-xl border border-slate-200">
                    <option value="" disabled selected>Pilih Unit...</option>
                    <?php if(isset($jenjang_list) && !empty($jenjang_list)): ?>
                        <?php foreach($jenjang_list as $j): ?>
                            <option value="<?= $j['kode_jenjang'] ?>"><?= $j['nama_jenjang'] ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <?php endif; ?>
            
             <div><label class="block text-sm font-medium mb-1">Pilih Ekskul</label><select name="ekskul_id" id="agt_ekskul" class="w-full px-4 py-2 rounded-xl border border-slate-200"><option value="" disabled selected>Pilih Ekskul...</option><?php if(isset($master_ekskul)) foreach($master_ekskul as $me): ?><option value="<?= $me['id'] ?>"><?= $me['nama_ekskul'] ?> <?php if(isset($isGlobal) && $isGlobal): ?>[<?= $me['kode_jenjang'] ?>]<?php endif; ?></option><?php endforeach; ?></select></div>
             
             <div>
                <label class="block text-sm font-medium mb-1">Pilih Siswa</label>
                <select name="siswa_id" id="agt_siswa" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="" disabled selected>Cari Siswa...</option>
                    <?php if(isset($siswa_list)): foreach($siswa_list as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= $s['nama_lengkap'] ?> - <?= $s['nis'] ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            
             <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium mb-1">Nilai</label><select name="nilai_huruf" id="agt_nilai" class="w-full px-4 py-2 rounded-xl border border-slate-200"><option value="A">A</option><option value="B">B</option><option value="C">C</option></select></div>
                <div><label class="block text-sm font-medium mb-1">Deskripsi Nilai</label><input type="text" name="deskripsi_nilai" id="agt_desk" class="w-full px-4 py-2 rounded-xl border border-slate-200"></div>
            </div>
        </div>
        
        <div class="mt-8 flex justify-end gap-3">
            <button type="button" onclick="closeModal('modalAnggota')" class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100">Batal</button>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700">Simpan</button>
        </div>
    </form>
</dialog>

<!-- 3. MODAL PRESENSI -->
<dialog id="modalPresensi" class="modal rounded-3xl p-0 w-full max-w-4xl backdrop:bg-slate-900/50">
    <form action="<?= base_url('app/kesiswaan/store_presensi_ekskul') ?>" method="POST" class="p-8 bg-white" id="formPresensi">
        <?= csrf_field() ?> 
        <input type="hidden" name="id" id="presensi_id">
        <!-- Hidden input to store JSON -->
        <input type="hidden" name="data_presensi" id="data_presensi"> 
        
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-lg text-slate-800">Catat Presensi Kegiatan</h3>
            <button type="button" onclick="closeModal('modalPresensi')" class="text-slate-400 hover:text-slate-600"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg></button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Side: Form Detail -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700">Pilih Ekskul</label>
                    <select name="ekskul_id" id="pre_ekskul" onchange="loadAnggotaByEkskul(this.value)" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" disabled selected>-- Pilih Ekskul --</option>
                        <?php if(isset($master_ekskul)) foreach($master_ekskul as $me): ?>
                            <option value="<?= $me['id'] ?>"><?= $me['nama_ekskul'] ?> <?php if(isset($isGlobal) && $isGlobal): ?>[<?= $me['kode_jenjang'] ?>]<?php endif; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700">Tanggal Kegiatan</label>
                    <input type="date" name="tanggal" id="pre_tanggal" required value="<?= date('Y-m-d') ?>" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700">Materi Kegiatan</label>
                    <textarea name="materi_kegiatan" id="pre_materi" rows="4" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: Latihan fisik..."></textarea>
                </div>
                
                <div class="pt-4 border-t border-slate-100">
                     <button type="submit" class="w-full px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition">Simpan Presensi</button>
                </div>
            </div>

            <!-- Right Side: Student List -->
            <div class="lg:col-span-2 bg-slate-50 rounded-2xl p-4 border border-slate-200 flex flex-col h-[500px]">
                <div class="flex justify-between items-center mb-3">
                    <label class="block text-sm font-bold text-slate-700">Daftar Kehadiran Siswa</label>
                    <div class="text-xs text-slate-500">
                        <span id="total_siswa">0</span> Siswa Terdaftar
                    </div>
                </div>
                
                <!-- Bulk Actions -->
                <div class="flex gap-2 mb-3">
                    <button type="button" onclick="setAllStatus('H')" class="flex-1 py-1.5 text-xs font-bold rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200">Set Semua Hadir</button>
                    <button type="button" onclick="setAllStatus('I')" class="flex-1 py-1.5 text-xs font-bold rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200">Set Semua Izin</button>
                </div>

                <!-- Scrollable List -->
                <div class="flex-1 overflow-y-auto custom-scrollbar" id="presensiListContainer">
                    <div class="text-center py-10 text-slate-400 text-sm italic">
                        Pilih Ekskul terlebih dahulu untuk memuat daftar siswa.
                    </div>
                </div>
            </div>
        </div>
    </form>
</dialog>

<!-- 4. MODAL KASUS BK -->
<dialog id="modalKasus" class="modal rounded-3xl p-0 w-full max-w-lg backdrop:bg-slate-900/50">
    <form action="<?= base_url('app/kesiswaan/store_kasus_bk') ?>" method="POST" class="p-8 bg-white" id="formKasus">
        <?= csrf_field() ?> 
        <input type="hidden" name="id" id="bk_id">
        <h3 class="font-bold text-lg mb-6 text-slate-800">Catat Kasus BK</h3>
        
        <div class="space-y-4">
            <?php if(isset($isGlobal) && $isGlobal): ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Unit Sekolah</label>
                <select name="kode_jenjang" required class="w-full px-4 py-2 rounded-xl border border-slate-200">
                    <option value="" disabled selected>Pilih Unit...</option>
                    <?php if(isset($jenjang_list) && !empty($jenjang_list)): ?>
                        <?php foreach($jenjang_list as $j): ?>
                            <option value="<?= $j['kode_jenjang'] ?>"><?= $j['nama_jenjang'] ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <?php endif; ?>
            <div><label class="block text-sm font-medium mb-1">Siswa</label><select name="siswa_id" id="bk_siswa_id" class="w-full px-4 py-2 rounded-xl border border-slate-200"><option value="" disabled selected>Cari Siswa...</option><?php if(isset($siswa_list)): foreach($siswa_list as $s): ?><option value="<?= $s['id'] ?>"><?= $s['nama_lengkap'] ?> - <?= $s['nis'] ?> [<?= $s['kode_jenjang'] ?>]</option><?php endforeach; endif; ?></select></div>
            <div><label class="block text-sm font-medium mb-1">Kategori</label><select name="bk_kategori_id" id="bk_kategori_id" class="w-full px-4 py-2 rounded-xl border border-slate-200"><option value="" disabled selected>Pilih...</option><?php if(isset($kategori_bk)): foreach($kategori_bk as $kat): ?><option value="<?= $kat['id'] ?>"><?= $kat['nama_kasus'] ?></option><?php endforeach; endif; ?></select></div>
            <div><label class="block text-sm font-medium mb-1">Tanggal</label><input type="date" name="tanggal_kejadian" id="bk_tanggal" required class="w-full px-4 py-2 rounded-xl border border-slate-200"></div>
            <div><label class="block text-sm font-medium mb-1">Keterangan</label><textarea name="keterangan_detail" id="bk_keterangan" class="w-full px-4 py-2 rounded-xl border border-slate-200"></textarea></div>
            <div><label class="block text-sm font-medium mb-1">Tindak Lanjut</label><input type="text" name="tindak_lanjut" id="bk_tindak" class="w-full px-4 py-2 rounded-xl border border-slate-200"></div>
        </div>
        
        <div class="mt-8 flex justify-end gap-3">
            <button type="button" onclick="closeModal('modalKasus')" class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100">Batal</button>
            <button type="submit" class="px-6 py-2 bg-rose-600 text-white rounded-xl font-bold hover:bg-rose-700">Simpan</button>
        </div>
    </form>
</dialog>

<!-- 5. MODAL ORGANISASI -->
<dialog id="modalOrganisasi" class="modal rounded-3xl p-0 w-full max-w-lg backdrop:bg-slate-900/50">
    <form action="<?= base_url('app/kesiswaan/store_organisasi') ?>" method="POST" class="p-8 bg-white" id="formOrganisasi">
        <?= csrf_field() ?> 
        <input type="hidden" name="id" id="org_id">
        <h3 class="font-bold text-lg mb-6 text-slate-800">Kelola Pengurus</h3>
        
        <div class="space-y-4">
            <?php if(isset($isGlobal) && $isGlobal): ?>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Unit Sekolah</label>
                <select name="kode_jenjang" required class="w-full px-4 py-2 rounded-xl border border-slate-200">
                    <option value="" disabled selected>Pilih Unit...</option>
                    <?php if(isset($jenjang_list) && !empty($jenjang_list)): ?>
                        <?php foreach($jenjang_list as $j): ?>
                            <option value="<?= $j['kode_jenjang'] ?>"><?= $j['nama_jenjang'] ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <?php endif; ?>
            <div><label class="block text-sm font-medium mb-1">Siswa</label><select name="siswa_id" id="org_siswa_id" class="w-full px-4 py-2 rounded-xl border border-slate-200"><option value="" disabled selected>Cari Siswa...</option><?php if(isset($siswa_list)): foreach($siswa_list as $s): ?><option value="<?= $s['id'] ?>"><?= $s['nama_lengkap'] ?> - <?= $s['nis'] ?> [<?= $s['kode_jenjang'] ?>]</option><?php endforeach; endif; ?></select></div>
            <div><label class="block text-sm font-medium mb-1">Jabatan</label><input type="text" name="jabatan" id="org_jabatan" required class="w-full px-4 py-2 rounded-xl border border-slate-200"></div>
            <div>
                <label class="block text-sm font-medium mb-1">Jenis</label>
                <select name="jenis_organisasi" id="org_jenis" class="w-full px-4 py-2 rounded-xl border border-slate-200">
                    <option value="OSIS">OSIS</option><option value="MPK">MPK</option>
                </select>
            </div>
            
            <!-- KOLOM TAMBAHAN -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Urutan</label>
                    <input type="number" name="urutan" id="org_urutan" class="w-full px-4 py-2 rounded-xl border border-slate-200" placeholder="1">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Atasan Langsung</label>
                    <select name="parent_id" id="org_parent" class="w-full px-4 py-2 rounded-xl border border-slate-200">
                        <option value="0">-- Tanpa Atasan --</option>
                        <?php if(isset($organisasi_list)): foreach($organisasi_list as $o): ?>
                            <option value="<?= $o['id'] ?>"><?= $o['jabatan'] ?> (<?= explode(' ', $o['nama_lengkap'])[0] ?>)</option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 mt-2"><input type="checkbox" name="status_aktif" id="org_status" value="1" checked><label>Status Aktif</label></div>
        </div>
        
        <div class="mt-8 flex justify-end gap-3">
            <button type="button" onclick="closeModal('modalOrganisasi')" class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100">Batal</button>
            <button type="submit" class="px-6 py-2 bg-amber-500 text-white rounded-xl font-bold hover:bg-amber-600">Simpan</button>
        </div>
    </form>
</dialog>

<!-- 6. MODAL ALUMNI -->
<dialog id="modalAlumni" class="modal rounded-3xl p-0 w-full max-w-lg backdrop:bg-slate-900/50">
    <form action="<?= base_url('app/kesiswaan/store_alumni') ?>" method="POST" class="p-8 bg-white" id="formAlumni">
        <?= csrf_field() ?> 
        <input type="hidden" name="id" id="alumni_id">
        <h3 class="font-bold text-lg mb-6 text-slate-800">Data Alumni</h3>
        
        <div class="space-y-4">
            <?php if(isset($isGlobal) && $isGlobal): ?>
            <div>
                <label class="block text-sm font-medium mb-1">Unit</label>
                <select name="kode_jenjang" required class="w-full px-4 py-2 rounded-xl border border-slate-200">
                    <option value="" disabled selected>Pilih...</option>
                    <?php if(isset($jenjang_list) && !empty($jenjang_list)): ?>
                        <?php foreach($jenjang_list as $j): ?>
                            <option value="<?= $j['kode_jenjang'] ?>"><?= $j['nama_jenjang'] ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm font-medium mb-1">Siswa</label><select name="siswa_id" id="alm_siswa_id" class="w-full px-4 py-2 rounded-xl border border-slate-200"><option value="" disabled selected>Cari...</option><?php if(isset($siswa_list)): foreach($siswa_list as $s): ?><option value="<?= $s['id'] ?>"><?= $s['nama_lengkap'] ?> - <?= $s['nis'] ?></option><?php endforeach; endif; ?></select></div>
                <div><label class="block text-sm font-medium mb-1">Tahun</label><input type="number" name="tahun_lulus" id="alm_tahun" required value="<?= date('Y') ?>" class="w-full px-4 py-2 rounded-xl border border-slate-200"></div>
            </div>
            
            <div><label class="block text-sm font-medium mb-1">Status</label><select name="status_kegiatan" id="alm_status" class="w-full px-4 py-2 rounded-xl border border-slate-200"><option value="Kuliah">Kuliah</option><option value="Bekerja">Bekerja</option></select></div>
            <div><label class="block text-sm font-medium mb-1">Instansi</label><input type="text" name="nama_instansi" id="alm_instansi" class="w-full px-4 py-2 rounded-xl border border-slate-200"></div>
            <div><label class="block text-sm font-medium mb-1">Posisi</label><input type="text" name="jabatan_jurusan" id="alm_jurusan" class="w-full px-4 py-2 rounded-xl border border-slate-200"></div>
            <div><label class="block text-sm font-medium mb-1">Testimoni</label><textarea name="testimoni" id="alm_testimoni" class="w-full px-4 py-2 rounded-xl border border-slate-200"></textarea></div>
        </div>
        
        <div class="mt-8 flex justify-end gap-3">
            <button type="button" onclick="closeModal('modalAlumni')" class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100">Batal</button>
            <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700">Simpan</button>
        </div>
    </form>
</dialog>

<!-- 7. MODAL PRESTASI -->
<dialog id="modalPrestasi" class="modal rounded-3xl p-0 w-full max-w-lg backdrop:bg-slate-900/50">
    <form action="<?= base_url('app/kesiswaan/store_prestasi') ?>" method="POST" class="p-8 bg-white" id="formPrestasi">
        <?= csrf_field() ?> 
        <input type="hidden" name="id" id="prestasi_id">
        <h3 class="font-bold text-lg mb-6 text-slate-800">Catat Prestasi Siswa</h3>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Siswa</label>
                <select name="siswa_id" id="pres_siswa_id" class="w-full px-4 py-2 rounded-xl border border-slate-200" required>
                    <option value="" disabled selected>Cari Siswa...</option>
                    <?php if(isset($siswa_list)): foreach($siswa_list as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= $s['nama_lengkap'] ?> - <?= $s['nis'] ?> [<?= $s['kode_jenjang'] ?>]</option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Jenis Prestasi</label>
                    <select name="jenis_prestasi" id="pres_jenis" class="w-full px-4 py-2 rounded-xl border border-slate-200">
                        <option value="Akademik">Akademik</option>
                        <option value="Olahraga">Olahraga</option>
                        <option value="Seni">Seni</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Tingkat</label>
                    <select name="tingkat" id="pres_tingkat" class="w-full px-4 py-2 rounded-xl border border-slate-200" required>
                        <option value="Sekolah">Sekolah</option>
                        <option value="Kabupaten/Kota">Kabupaten/Kota</option>
                        <option value="Provinsi">Provinsi</option>
                        <option value="Nasional">Nasional</option>
                        <option value="Internasional">Internasional</option>
                    </select>
                </div>
            </div>

            <div><label class="block text-sm font-medium mb-1">Nama Prestasi/Lomba</label><input type="text" name="nama_prestasi" id="pres_nama" required class="w-full px-4 py-2 rounded-xl border border-slate-200" placeholder="Contoh: Juara 1 Olimpiade Matematika"></div>
            
            <div class="grid grid-cols-2 gap-4">
                <!-- FIX ID: pres_juara agar cocok dengan JS editPrestasi -->
                <div><label class="block text-sm font-medium mb-1">Peringkat</label><input type="text" name="peringkat" id="pres_juara" class="w-full px-4 py-2 rounded-xl border border-slate-200" placeholder="1, 2, 3, Harapan 1..."></div>
                <!-- FIX ID: pres_tanggal -->
                <div><label class="block text-sm font-medium mb-1">Tanggal Terima</label><input type="date" name="tanggal_prestasi" id="pres_tanggal" required class="w-full px-4 py-2 rounded-xl border border-slate-200"></div>
            </div>
            
            <div><label class="block text-sm font-medium mb-1">Keterangan</label><textarea name="keterangan" id="pres_keterangan" class="w-full px-4 py-2 rounded-xl border border-slate-200"></textarea></div>
        </div>
        
        <div class="mt-8 flex justify-end gap-3">
            <button type="button" onclick="closeModal('modalPrestasi')" class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100">Batal</button>
            <button type="submit" class="px-6 py-2 bg-violet-600 text-white rounded-xl font-bold hover:bg-violet-700">Simpan</button>
        </div>
    </form>
</dialog>