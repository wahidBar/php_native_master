<?php
$isEdit   = ($mode ?? '') === 'edit';
$barang   = $barang ?? [];
$nextKode = $nextKode ?? '';
$tipe     = $tipe ?? '';

?>
<style>
    .modal {
        z-index: 1900 !important;
    }

    .modal-backdrop {
        z-index: 1800 !important;
    }
    
</style>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">

        <!-- HEADER -->
        <div class="card-header  text-grey">
            <h5 class="fw-semibold mb-0">
                <?= $isEdit ? 'Edit Barang' : 'Tambah Barang' ?>
            </h5>
        </div>

        <!-- FORM -->
        <form method="POST" action="?action=barang.save" autocomplete="off">
            <input type="hidden" name="idbarang" value="<?= htmlspecialchars($barang['idbarang'] ?? '') ?>">
            <input type="hidden" name="tipe" value="<?= htmlspecialchars($tipe) ?>">
            <input type="hidden"
                name="parent_id"
                id="parent_id"
                value="<?= htmlspecialchars($barang['parent_id'] ?? '') ?>">


            <div class="card-body">

                <!-- ================= DATA UTAMA ================= -->
                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3">Data Barang</h6>

                <div class="row g-3">

                    <!-- KODE BARANG -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text"
                                name="kodebarang"
                                id="kodebarang"
                                class="form-control"
                                value="<?= htmlspecialchars($barang['kodebarang'] ?? $nextKode) ?>"
                                required>

                            <button type="button" id="btnKode" class="btn btn-outline-secondary">
                                <i class="bi bi-search"></i>
                            </button>


                        </div>
                    </div>

                    <!-- NAMA BARANG -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text"
                            name="namabarang"
                            class="form-control"
                            value="<?= htmlspecialchars($barang['namabarang'] ?? '') ?>"
                            required>
                    </div>

                    <!-- TIPE BARANG -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Tipe Barang <span class="text-danger">*</span>
                        </label>
                        <select name="tipebarang" class="form-control">
                            <?php foreach ($tipeList as $kode => $label): ?>
                                <option value="<?= $kode ?>"
                                    <?= ($tipe == $kode) ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- SATUAN -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Satuan</label>
                        <select name="idsatuan" class="form-select">
                            <?php foreach (($satuan ?? []) as $s): ?>
                                <option value="<?= htmlspecialchars($s['idsatuan']) ?>"
                                    <?= ($barang['idsatuan'] ?? '') == $s['idsatuan'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['idsatuan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- AKTIVA -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Aktiva</label>
                        <select name="cboAktiva" class="form-select">
                            <option value="">-- Pilih Aktiva --</option>
                            <?php foreach (($aktiva ?? []) as $a): ?>
                                <option value="<?= htmlspecialchars($a['id_aktiva']) ?>"
                                    <?= ($barang['aktiva_id'] ?? '') == $a['id_aktiva'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($a['aktiva']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- DAKSPI -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">DAKSPI</label>
                        <select name="dakspi" class="form-select">
                            <option value="1" <?= ($barang['dakspi'] ?? 0) == 1 ? 'selected' : '' ?>>DAKSPI</option>
                            <option value="0" <?= ($barang['dakspi'] ?? 0) == 0 ? 'selected' : '' ?>>NON DAKSPI</option>
                        </select>
                    </div>

                    <!-- STATUS AKTIF -->
                    <div class="col-md-12">
                        <div class="form-check form-switch mt-2">
                            <input type="hidden" name="aktif" value="0">
                            <input class="form-check-input"
                                type="checkbox"
                                name="aktif"
                                value="1"
                                <?= ($barang['isbrg_aktif'] ?? 1) == 1 ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold">Aktif</label>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- ================= DEPRESIASI ================= -->
                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3">
                    Informasi Depresiasi / Penyusutan
                </h6>

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Metode Depresiasi</label>
                        <?php $metode = $barang['metodedepresiasi'] ?? 'NN'; ?>
                        <select name="metodedepresiasi" class="form-select">
                            <option value="NN" <?= $metode == 'NN' ? 'selected' : '' ?>>NN - Tidak Susut</option>
                            <option value="SL" <?= $metode == 'SL' ? 'selected' : '' ?>>SL - Garis Lurus</option>
                            <option value="RB" <?= $metode == 'RB' ? 'selected' : '' ?>>RB - Saldo Menurun</option>
                            <option value="DD" <?= $metode == 'DD' ? 'selected' : '' ?>>DD - Double Declining</option>
                            <option value="SY" <?= $metode == 'SY' ? 'selected' : '' ?>>SY - Sum of Years</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Prosentase Depresiasi (%)</label>
                        <input type="number"
                            name="stddeppct"
                            min="0"
                            step="0.01"
                            class="form-control"
                            value="<?= htmlspecialchars($barang['stddeppct'] ?? 0) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Life Time (bulan)</label>
                        <input type="number"
                            name="lifetime"
                            min="0"
                            class="form-control"
                            value="<?= htmlspecialchars($barang['lifetime'] ?? 0) ?>">
                    </div>
                </div>

                <hr class="my-4">

                <!-- ================= KEUANGAN ================= -->
                <h6 class="fw-bold text-secondary border-bottom pb-2 mb-3">
                    Kode Rekening Keuangan
                </h6>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Akun Persediaan</label>
                        <input type="text" name="akunaset" class="form-control"
                            value="<?= htmlspecialchars($barang['akunaset'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Akun Pengadaan</label>
                        <input type="text" name="akunpengadaan" class="form-control"
                            value="<?= htmlspecialchars($barang['akunpengadaan'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Akun Pemakaian</label>
                        <input type="text" name="akunpemakaian" class="form-control"
                            value="<?= htmlspecialchars($barang['akunpemakaian'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Akun Penghapusan</label>
                        <input type="text" name="akunhapus" class="form-control"
                            value="<?= htmlspecialchars($barang['akunhapus'] ?? '') ?>">
                    </div>
                </div>

            </div>

            <!-- FOOTER -->
            <div class="card-footer d-flex justify-content-between  text-end ">
                <button type="submit" class="btn btn-primary px-4">
                    <?= $isEdit ? 'Update Data' : 'Simpan Data' ?>
                </button>

                <a href="?action=dashboard" class="btn btn-secondary px-4">Kembali</a>
            </div>
        </form>
    </div>
</div>
<div class="modal fade"
     id="modalJenisKode"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Pilih Jenis Barang</h5>
            </div>

            <div class="modal-body text-center">
                <button id="pilihInduk"
                        type="button"
                        class="btn btn-primary w-100 mb-2">
                    Barang Induk
                </button>

                <button id="pilihChild"
                        type="button"
                        class="btn btn-success w-100">
                    Barang Child
                </button>
            </div>

        </div>
    </div>
</div>
<div class="modal fade"
     id="modalKode"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow">

            <div class="modal-header">
                <h5 class="modal-title">Pilih Kode Barang</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm align-middle">
                        <thead class="table text-center">
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($kodeInduk)): ?>
                                <?php foreach ($kodeInduk as $k): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($k['kodebarang']) ?></td>
                                        <td><?= htmlspecialchars($k['namabarang']) ?></td>
                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-sm btn-primary pilih-kode"
                                                data-id="<?= $k['idbarang'] ?>"
                                                data-kode="<?= $k['kodebarang'] ?>">
                                                Pilih
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3"
                                        class="text-center text-muted">
                                        Tidak ada data
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {

    const btnKode     = document.getElementById('btnKode');
    const kodeInput   = document.getElementById('kodebarang');
    const parentInput = document.getElementById('parent_id');

    const modalJenisEl = document.getElementById('modalJenisKode');
    const modalKodeEl  = document.getElementById('modalKode');

    const modalJenis = new bootstrap.Modal(modalJenisEl, {
        backdrop: 'static',
        keyboard: false
    });

    const modalKode = new bootstrap.Modal(modalKodeEl, {
        backdrop: 'static',
        keyboard: false
    });

    btnKode.addEventListener('click', function() {
        modalJenis.show();
    });

    // INDUK
    document.getElementById('pilihInduk')
        .addEventListener('click', function() {

            fetch('?action=barang.kodeIndukBaru')
                .then(r => r.text())
                .then(kode => {

                    kodeInput.value = kode;
                    parentInput.value = "";
                    modalJenis.hide();
                });
        });

    // CHILD
    document.getElementById('pilihChild')
        .addEventListener('click', function() {

            modalJenisEl.addEventListener('hidden.bs.modal', function () {
                modalKode.show();
            }, { once: true });

            modalJenis.hide();
        });

    // PILIH KODE
    document.addEventListener('click', function(e) {

        const btn = e.target.closest('.pilih-kode');
        if (!btn) return;

        const kodeInduk = btn.dataset.kode;
        const idInduk   = btn.dataset.id;

        fetch('?action=barang.kodeChildBaru&parent=' + kodeInduk)
            .then(r => r.text())
            .then(kode => {

                kodeInput.value  = kode;
                parentInput.value = idInduk;
                modalKode.hide();
            });
    });

});
</script>