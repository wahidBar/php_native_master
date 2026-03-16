<div class="container mt-4">
    <h3>Edit User</h3>

    <?php if (isset($_SESSION['flash_show'])): ?>
        <div class="alert alert-info"><?= $_SESSION['flash_show'];
                                        unset($_SESSION['flash_show']); ?></div>
    <?php endif; ?>
    <?php flash_show(); ?>

    <form action="?action=users.update" method="POST" enctype="multipart/form-data">
        <?= csrf_field(); ?>
        <input type="hidden" name="id" value="<?= (int)$user['id']; ?>">

        <div class="card shadow-sm p-4">

            <div class="row">
                <!-- LEFT -->
                <div class="col-md-6">

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control"
                            value="<?= htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                            value="<?= htmlspecialchars($user['email']); ?>" required>
                    </div>


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

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-control" required>
                            <option value="">-- Pilih Role --</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id']; ?>" <?= ($user['role_id'] == $r['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" class="form-control"
                            value="<?= htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input
                            type="date"
                            name="date_of_birth"
                            class="form-control"
                            min="1900-01-01"
                            max="2099-12-31">
                    </div>

                </div>

                <!-- RIGHT -->
                <div class="col-md-6">

                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="gender" class="form-control">
                            <option value="">-- Pilih Gender --</option>
                            <option value="male" <?= (isset($user['gender']) && $user['gender'] == 'male') ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= (isset($user['gender']) && $user['gender'] == 'female') ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= (isset($user['gender']) && $user['gender'] == 'other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="4"><?= htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1" <?= (isset($user['is_active']) && intval($user['is_active']) === 1) ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= (isset($user['is_active']) && intval($user['is_active']) === 0) ? 'selected' : '' ?>>Nonaktif</option>
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
                            <?php if (!empty($user['profile_photo'])): ?>
                                <img id="previewPhoto"
                                    src="uploads/images/profile_photo/<?= htmlspecialchars($user['profile_photo']); ?>"
                                    width="140"
                                    class="rounded shadow-sm"
                                    style="object-fit:cover;">
                            <?php else: ?>
                                <img id="previewPhoto"
                                    src=""
                                    width="140"
                                    class="rounded shadow-sm"
                                    style="display:none; object-fit:cover;">
                                <p id="noPhotoText" class="text-muted mt-2">Belum ada foto.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-primary px-4">Update</button>
                <a href="?action=users.index" class="btn btn-secondary px-4">Kembali</a>
            </div>

        </div>
    </form>
</div>

<script>
    function previewImage(event) {

        const preview = document.getElementById("previewPhoto");
        const file = event.target.files[0];
        const noPhotoText = document.getElementById("noPhotoText");

        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = "block";

            if (noPhotoText) {
                noPhotoText.style.display = "none";
            }
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