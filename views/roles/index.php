<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">
                <i class="feather-shield me-2 text-primary"></i>
                Manajemen Role
            </h4>
            <small class="text-muted">
                Kelola hak akses dan peran dalam sistem
            </small>
        </div>

        <a href="?action=roles.create" class="btn btn-primary">
            <i class="feather-plus"></i> Tambah Role
        </a>
    </div>

    <div class="card-body">

        <?php flash_show(); ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">

                <thead class="table">
                    <tr>
                        <th width="50">#</th>
                        <th>Nama Role</th>
                        <th width="220" class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $i => $r): ?>
                            <tr>

                                <!-- NOMOR -->
                                <td><?= $i + 1 ?></td>

                                <!-- NAMA ROLE -->
                                <td>
                                    <strong>
                                        <?= htmlspecialchars($r['name']) ?>
                                    </strong>
                                </td>

                                <!-- AKSI -->
                                <td class="text-center">
                                    <div class="btn-group">

                                        <a href="?action=roles.show&id=<?= $r['id'] ?>"
                                            class="btn btn-sm btn-outline-info">
                                            <i class="feather-eye"></i>
                                        </a>

                                        <a href="?action=roles.edit&id=<?= $r['id'] ?>"
                                            class="btn btn-sm btn-outline-warning">
                                            <i class="feather-edit"></i>
                                        </a>

                                        <a onclick="return confirm('Yakin hapus role ini?')"
                                            href="?action=roles.delete&id=<?= $r['id'] ?>"
                                            class="btn btn-sm btn-outline-danger">
                                            <i class="feather-trash-2"></i>
                                        </a>

                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                Belum ada data role
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>

    </div>
</div>