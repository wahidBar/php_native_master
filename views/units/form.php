    <form id="dataForm" enctype="multipart/form-data">

        <?php if(isset($data['idunit'])): ?>
            <input type="hidden" name="idunit" value="<?= $data['idunit'] ?>">
        <?php endif; ?>

        
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Kodeunit</label>
                    <input type='text'
                    name='kodeunit'
                    class='form-control'
                    value='<?= htmlspecialchars($data['kodeunit'] ?? '') ?>'
                    placeholder='Masukkan Kodeunit' required >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Namaunit</label>
                    <input type='text'
                    name='namaunit'
                    class='form-control'
                    value='<?= htmlspecialchars($data['namaunit'] ?? '') ?>'
                    placeholder='Masukkan Namaunit'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Namapanjang</label>
                    <input type='text'
                    name='namapanjang'
                    class='form-control'
                    value='<?= htmlspecialchars($data['namapanjang'] ?? '') ?>'
                    placeholder='Masukkan Namapanjang'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Parentunit</label>
                    <input type='number'  name='parentunit' 
                    class='form-control'
                    value='<?= $data['parentunit'] ?? '' ?>'
                    placeholder='Masukkan Parentunit' >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Level</label>
                    <input type='text'
                    name='level'
                    class='form-control'
                    value='<?= htmlspecialchars($data['level'] ?? '') ?>'
                    placeholder='Masukkan Level' required >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Isleaf</label>
                    <input type='number' step='any'
                    name='isleaf'
                    class='form-control'
                    value='<?= $data['isleaf'] ?? '' ?>'
                    placeholder='Masukkan Isleaf' >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Deflokasi</label>
                    <input type='text'
                    name='deflokasi'
                    class='form-control'
                    value='<?= htmlspecialchars($data['deflokasi'] ?? '') ?>'
                    placeholder='Masukkan Deflokasi'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>T Userid</label>
                    <input type='text'
                    name='t_userid'
                    class='form-control'
                    value='<?= htmlspecialchars($data['t_userid'] ?? '') ?>'
                    placeholder='Masukkan T Userid'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>T Updatetime</label>
                    <input type='text'
                    name='t_updatetime'
                    class='form-control'
                    value='<?= htmlspecialchars($data['t_updatetime'] ?? '') ?>'
                    placeholder='Masukkan T Updatetime'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>T Ipaddress</label>
                    <input type='text'
                    name='t_ipaddress'
                    class='form-control'
                    value='<?= htmlspecialchars($data['t_ipaddress'] ?? '') ?>'
                    placeholder='Masukkan T Ipaddress'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Kodeupb</label>
                    <input type='text'
                    name='kodeupb'
                    class='form-control'
                    value='<?= htmlspecialchars($data['kodeupb'] ?? '') ?>'
                    placeholder='Masukkan Kodeupb'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Nippetugas</label>
                    <input type='text'
                    name='nippetugas'
                    class='form-control'
                    value='<?= htmlspecialchars($data['nippetugas'] ?? '') ?>'
                    placeholder='Masukkan Nippetugas'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Namapetugas</label>
                    <input type='text'
                    name='namapetugas'
                    class='form-control'
                    value='<?= htmlspecialchars($data['namapetugas'] ?? '') ?>'
                    placeholder='Masukkan Namapetugas'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Jabatanpetugas</label>
                    <input type='text'
                    name='jabatanpetugas'
                    class='form-control'
                    value='<?= htmlspecialchars($data['jabatanpetugas'] ?? '') ?>'
                    placeholder='Masukkan Jabatanpetugas'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Nippetugas2</label>
                    <input type='text'
                    name='nippetugas2'
                    class='form-control'
                    value='<?= htmlspecialchars($data['nippetugas2'] ?? '') ?>'
                    placeholder='Masukkan Nippetugas2'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Namapetugas2</label>
                    <input type='text'
                    name='namapetugas2'
                    class='form-control'
                    value='<?= htmlspecialchars($data['namapetugas2'] ?? '') ?>'
                    placeholder='Masukkan Namapetugas2'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Jabatanpetugas2</label>
                    <input type='text'
                    name='jabatanpetugas2'
                    class='form-control'
                    value='<?= htmlspecialchars($data['jabatanpetugas2'] ?? '') ?>'
                    placeholder='Masukkan Jabatanpetugas2'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Islast</label>
                    <input type='text'
                    name='islast'
                    class='form-control'
                    value='<?= htmlspecialchars($data['islast'] ?? '') ?>'
                    placeholder='Masukkan Islast'  >
                </div>
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>Isunit Aktif</label>
                    <input type='text'
                    name='isunit_aktif'
                    class='form-control'
                    value='<?= htmlspecialchars($data['isunit_aktif'] ?? '') ?>'
                    placeholder='Masukkan Isunit Aktif'  >
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