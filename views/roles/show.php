<div class="container mt-4">

    <h3>Detail Role</h3>

    <?php flash_show(); ?>

    <div class="card shadow-sm p-4">

        <table class="table">
            <tr>
                <th width="150">ID</th>
                <td><?= $role['id'] ?></td>
            </tr>
            <tr>
                <th>Nama Role</th>
                <td><?= htmlspecialchars($role['name']) ?></td>
            </tr>
            <tr>
                <th>Dibuat</th>
                <td><?= $role['created_at'] ?></td>
            </tr>
            <!-- <tr>
                <th>Diupdate</th>
                <td><?= $role['updated_at'] ?></td>
            </tr> -->
        </table>

        <div class="mt-3 d-flex gap-2 justify-content-end">
            <a href="?action=roles.edit&id=<?= $role['id'] ?>" class="btn btn-warning px-3">Edit</a>
            <a onclick="return confirm('Yakin hapus role ini?')"
                href="?action=roles.delete&id=<?= $role['id'] ?>"
                class="btn btn-danger px-3">Hapus</a>
            <a href="?action=roles.index" class="btn btn-secondary px-3">Kembali</a>
        </div>

    </div>

</div>