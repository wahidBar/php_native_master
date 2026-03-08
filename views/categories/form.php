    <form id="dataForm" enctype="multipart/form-data">

        <?php if(isset($data['id'])): ?>
            <input type="hidden" name="id" value="<?= $data['id'] ?>">
        <?php endif; ?>

        
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Kategori</label>
                    <input type='text'
                    name='kategori'
                    class='form-control'
                    value='<?= htmlspecialchars($data['kategori'] ?? '') ?>'
                    placeholder='Masukkan Kategori'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Aktif</label>
                    <input type='text'
                    name='aktif'
                    class='form-control'
                    value='<?= htmlspecialchars($data['aktif'] ?? '') ?>'
                    placeholder='Masukkan Aktif'  >
                </div>

        <hr>

        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                Batal
            </button>
            <button type="submit" class="btn btn-primary px-4">
                Simpan
            </button>
        </div>

    </form>