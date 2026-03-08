<div class="container mt-4">

    <h3>Edit Role</h3>

    <?php flash_show(); ?>

    <form action="?action=roles.update&id=<?= $role['id'] ?>" method="POST">
        <?= csrf_field(); ?>

        <div class="card shadow-sm p-4">

            <div class="mb-3">
                <label class="form-label">Nama Role *</label>
                <input type="text"
                    name="name"
                    class="form-control"
                    value="<?= htmlspecialchars($role['name']) ?>"
                    required>
            </div>

           <div class="mt-4 d-flex justify-content-between align-items-center">
                <button type="submit" class="btn btn-primary px-4">Update Role</button>
                <a href="?action=roles.index" class="btn btn-secondary px-4">Kembali</a>
            </div>
        </div>

    </form>

</div>