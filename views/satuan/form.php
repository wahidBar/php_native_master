    <form id="dataForm" enctype="multipart/form-data">

        <?php if(isset($data['nourut'])): ?>
            <input type="hidden" name="nourut" value="<?= $data['nourut'] ?>">
        <?php endif; ?>

        
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Idsatuan</label>
                    <input type='text'
                    name='idsatuan'
                    class='form-control'
                    value='<?= htmlspecialchars($data['idsatuan'] ?? '') ?>'
                    placeholder='Masukkan Idsatuan' required >
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