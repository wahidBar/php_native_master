<div class="container mt-4">
    <h3>Tambah User</h3>

    <?php flash_show(); ?>

    <form action="?action=users.store" method="POST" enctype="multipart/form-data">
        <?= csrf_field(); ?>

        <div class="card shadow-sm p-4">

            <div class="row">

                <!-- LEFT -->
                <div class="col-md-6">

                    <div class="mb-3">
                        <label class="form-label">Nama *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <!-- PASSWORD -->
                        <div class="mb-3 position-relative">
                            <label class="form-label">
                                Password
                                <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small>
                            </label>

                            <input type="password"
                                name="password"
                                class="form-control password-field pe-5"
                                placeholder="Isi jika ingin mengganti password"
                                autocomplete="new-password">

                            <span class="toggle-password"
                                style="position:absolute; top:38px; right:12px; cursor:pointer; font-size:18px;">
                                👁️
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role_id" class="form-control" required>
                            <option value="">-- Pilih Role --</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id']; ?>"><?= htmlspecialchars($r['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="date_of_birth" class="form-control">
                    </div>

                </div>

                <!-- RIGHT -->
                <div class="col-md-6">

                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="gender" class="form-control">
                            <option value="">-- Pilih Gender --</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto Profil</label>

                        <input type="file"
                            name="profile_photo"
                            class="form-control"
                            id="photoInput"
                            accept="image/*"
                            onchange="previewImage(event)">

                        <div class="mt-3">
                            <img id="previewPhoto"
                                src=""
                                width="140"
                                class="rounded shadow-sm"
                                style="display:none; object-fit:cover;">
                        </div>
                    </div>

                </div>
            </div>
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-primary px-4">Simpan</button>
                <a href="?action=users.index" class="btn btn-secondary px-4">Kembali</a>
            </div>

        </div>
    </form>
</div>
<script>
    function previewImage(event) {

        const preview = document.getElementById("previewPhoto");
        const file = event.target.files[0];

        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = "block";
        }

    }
    document.addEventListener("DOMContentLoaded", function() {

        // Toggle Password
        document.querySelectorAll(".toggle-password").forEach(function(toggle) {

            toggle.addEventListener("click", function() {

                const wrapper = this.closest(".position-relative");
                const input = wrapper.querySelector(".password-field");

                const type = input.type === "password" ? "text" : "password";
                input.type = type;

                this.textContent = type === "password" ? "👁️" : "🙈";
            });
        });
    });
</script>