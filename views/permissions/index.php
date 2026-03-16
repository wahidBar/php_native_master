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

                                    <?php

                                    function renderPermissionRows($menus, $permissionMap, $rolePermissions, $level = 0)
                                    {

                                        foreach ($menus as $menu):

                                            $routeParts = explode('.', $menu['route'] ?? '');
                                            $resource = strtolower($routeParts[0] ?? '');

                                            $isParent = empty($menu['parent_id']);

                                    ?>

                                            <tr class="<?= $isParent ? 'perm-parent' : 'perm-child' ?>">

                                                <td class="perm-menu" style="padding-left: <?= 20 + ($level * 25) ?>px">

                                                    <?php if ($isParent): ?>

                                                        <div class="perm-parent-title">

                                                            <?= htmlspecialchars($menu['name']) ?>

                                                        </div>

                                                    <?php else: ?>

                                                        <div class="perm-child-title">

                                                            <span class="perm-branch">└</span>

                                                            <?= htmlspecialchars($menu['name']) ?>

                                                        </div>

                                                    <?php endif; ?>

                                                    <div class="perm-route">

                                                        <?= $menu['route'] ?>

                                                    </div>

                                                </td>

                                                <?php foreach (['view', 'create', 'edit', 'delete'] as $act): ?>

                                                    <td class="text-center">

                                                        <?php if (isset($permissionMap[$resource][$act])):

                                                            $perm = $permissionMap[$resource][$act];

                                                        ?>

                                                            <input
                                                                type="checkbox"
                                                                class="form-check-input perm-checkbox"
                                                                name="permissions[]"
                                                                value="<?= $perm['id'] ?>"
                                                                <?= in_array($perm['id'], $rolePermissions) ? 'checked' : '' ?>>

                                                        <?php else: ?>

                                                            <span class="perm-none">—</span>

                                                        <?php endif; ?>

                                                    </td>

                                                <?php endforeach; ?>

                                            </tr>

                                    <?php

                                            if (!empty($menu['children'])) {

                                                renderPermissionRows(
                                                    $menu['children'],
                                                    $permissionMap,
                                                    $rolePermissions,
                                                    $level + 1
                                                );
                                            }

                                        endforeach;
                                    }

                                    renderPermissionRows($menuTree, $permissionMap, $rolePermissions);

                                    ?>

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
        display: block;
        background: var(--bs-tertiary-bg);
        text-decoration: none;
        color: inherit;
    }

    .role-card.active {
        background: var(--bs-primary);
        color: white;
    }
</style>
<script>
    document.querySelectorAll('.perm-parent input[type=checkbox]').forEach(parent => {

        parent.addEventListener('change', function() {

            let row = this.closest('tr')
            let next = row.nextElementSibling

            while (next && next.classList.contains('perm-child')) {

                let cb = next.querySelectorAll('input[type=checkbox]')[this.cellIndex - 1]

                if (cb) cb.checked = this.checked

                next = next.nextElementSibling

            }

        })

    })
</script>