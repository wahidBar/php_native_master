<div class="container-fluid py-4">

    <div class="row g-4">

        <!-- ROLE PANEL -->
        <div class="col-lg-3">
            <div class="card shadow-sm border-0">
                <div class="card-header fw-semibold">
                    Roles
                </div>

                <div class="card-body p-2">

                    <?php foreach ($roles as $role): ?>
                        <a href="?action=permissions.index&role_id=<?= $role['id'] ?>"
                           class="role-card p-3 mb-2 rounded <?= $role['id'] == $selectedRoleId ? 'active' : '' ?>">
                            <div class="fw-semibold">
                                <?= htmlspecialchars($role['name']) ?>
                            </div>
                            <small>
                                <?= $role['total_permission'] ?> Permissions
                            </small>
                        </a>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>

        <!-- MATRIX PANEL -->
        <div class="col-lg-9">

            <form method="POST" action="?action=permissions.updateRolePermission">
                <?= csrf_field(); ?>

                <input type="hidden" name="role_id" value="<?= $selectedRoleId ?>">

                <div class="card shadow-sm border-0">

                    <div class="card-header fw-semibold">
                        Permission Matrix
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">

                            <table class="table table-bordered align-middle mb-0">

                                <thead class="table text-center">
                                    <tr>
                                        <th class="text-start">Menu</th>
                                        <th>View</th>
                                        <th>Create</th>
                                        <th>Edit</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>

                                <tbody>

                                <?php foreach ($matrix as $resource => $data): ?>
                                    <tr>

                                        <td class="fw-semibold">
                                            <?= htmlspecialchars($data['menu_name']) ?>
                                            <div class="text-muted small">
                                                <?= $data['route'] ?>
                                            </div>
                                        </td>

                                        <?php foreach (['view','create','edit','delete'] as $act): ?>
                                            <td class="text-center">

                                                <?php if (isset($data['actions'][$act])): 
                                                    $perm = $data['actions'][$act];
                                                ?>
                                                    <input type="checkbox"
                                                           name="permissions[]"
                                                           value="<?= $perm['id'] ?>"
                                                           <?= in_array($perm['id'], $rolePermissions) ? 'checked' : '' ?>>
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>

                                            </td>
                                        <?php endforeach; ?>

                                    </tr>
                                <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>

                </div>

            </form>

        </div>
    </div>
</div>

<style>
.role-card {
    display:block;
    background: var(--bs-tertiary-bg);
    text-decoration:none;
    color:inherit;
}

.role-card.active {
    background: var(--bs-primary);
    color:white;
}
</style>

<!-- <script>
    document.addEventListener('DOMContentLoaded', function() {

        let activeRole = document.querySelector('.role-card.active');

        document.querySelectorAll('.role-card').forEach(card => {
            card.addEventListener('click', async function() {

                if (activeRole) activeroles.classList.remove('active');
                this.classList.add('active');
                activeRole = this;

                const roleId = this.dataset.id;
                const roleName = this.dataset.name;

                document.getElementById('selectedRole').value = roleId;
                document.getElementById('roleLabel').innerText = " - " + roleName;

                try {
                    const res = await fetch('?action=permissions.getRolePermissions&role_id=' + roleId);
                    const data = await res.json();

                    document.querySelectorAll('.permission-checkbox')
                        .forEach(cb => cb.checked = false);

                    data.forEach(id => {
                        const el = document.getElementById('perm' + id);
                        if (el) el.checked = true;
                    });

                } catch (err) {
                    console.error("Gagal mengambil permission:", err);
                }

            });
        });

        const toggleBtn = document.getElementById('toggleGlobal');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {

                const checkboxes = document.querySelectorAll('.permission-checkbox');
                if (!checkboxes.length) return;

                const allChecked = [...checkboxes].every(cb => cb.checked);
                checkboxes.forEach(cb => cb.checked = !allChecked);

            });
        }

    });
</script> -->