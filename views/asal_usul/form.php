    <form id="dataForm" enctype="multipart/form-data">

        <?php if(isset($data['idjenistrans'])): ?>
            <input type="hidden" name="idjenistrans" value="<?= $data['idjenistrans'] ?>">
        <?php endif; ?>

        
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Keterangan</label>
                    <input type='text'
                    name='keterangan'
                    class='form-control'
                    value='<?= htmlspecialchars($data['keterangan'] ?? '') ?>'
                    placeholder='Masukkan Keterangan'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Nourut</label>
                    <input type='number'  name='nourut' 
                    class='form-control'
                    value='<?= $data['nourut'] ?? '' ?>'
                    placeholder='Masukkan Nourut' >
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