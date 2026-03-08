<div class="container mt-4">
    <div class="card shadow-sm p-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Detail User</h4>
        </div>

        <div class="card-body">

            <div class="row">

                <!-- FOTO PROFIL -->
                <div class="col-md-4 text-center">

                    <?php if (!empty($user['profile_photo'])): ?>
                        <img src="uploads/images/profile_photo/<?= htmlspecialchars($user['profile_photo']) ?>"
                            class="rounded mb-3 img-fluid"
                            style="width: 100%;">
                    <?php else: ?>
                        <div class="alert alert-warning">Tidak ada foto</div>
                    <?php endif; ?>

                </div>

                <!-- DETAIL USER -->
                <div class="col-md-8">

                    <h4><?= htmlspecialchars($user['name']) ?></h4>

                    <table class="table table-bordered mt-3">
                        <tr>
                            <th width="180">Email</th>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                        </tr>

                        <tr>
                            <th>Role</th>
                            <td><span class="badge bg-info"><?= htmlspecialchars($user['role_name']) ?></span></td>
                        </tr>

                        <tr>
                            <th>Telepon</th>
                            <td><?= htmlspecialchars((string)($user['phone'] ?? '-')) ?></td>
                        </tr>

                        <tr>
                            <th>Alamat</th>
                            <td><?= htmlspecialchars($user['address'] ?? '-') ?></td>
                        </tr>

                        <tr>
                            <th>Tanggal Lahir</th>
                            <td><?= htmlspecialchars($user['date_of_birth'] ?? '-') ?></td>
                        </tr>

                        <tr>
                            <th>Gender</th>
                            <td><?= htmlspecialchars($user['gender'] ?? '-') ?></td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>
                                <?php if ($user['is_active'] == 1): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Dibuat Pada</th>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                        </tr>

                        <tr>
                            <th>Diperbarui</th>
                            <td><?= htmlspecialchars($user['updated_at']) ?></td>
                        </tr>

                    </table>
                    <div class="mt-3 d-flex gap-2 justify-content-end">
                        <a href="?action=users.edit&id=<?= $user['id'] ?>" class="btn btn-warning px-3">Edit</a>
                        <a onclick="return confirm('Yakin hapus user ini?')"
                            href="?action=users.delete&id=<?= $user['id'] ?>"
                            class="btn btn-danger px-3">Hapus</a>
                        <a href="?action=users.index" class="btn btn-secondary px-3">Kembali</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>