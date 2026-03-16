<?php
require_once BASE_PATH . '/helpers.php';
require_once BASE_PATH . '/controllers/BaseController.php';

class PermissionController extends BaseController
{
    public function index()
    {
        require_permission('permissions.view');
        global $pdo;

        /* =========================
       ROLES
    ========================== */

        $roles = $pdo->query("
        SELECT r.*,
        (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id=r.id) total_permission
        FROM roles r
        ORDER BY r.id
    ")->fetchAll(PDO::FETCH_ASSOC);

        $selectedRoleId = $_GET['role_id'] ?? ($roles[0]['id'] ?? 0);

        /* =========================
       MENUS
    ========================== */

        $menus = $pdo->query("
        SELECT *
        FROM menus
        ORDER BY parent_id ASC, order_number ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

        /* =========================
       PERMISSIONS
    ========================== */

        $permissions = $pdo->query("SELECT * FROM permissions")
            ->fetchAll(PDO::FETCH_ASSOC);

        /* =========================
       ROLE PERMISSION
    ========================== */

        $rolePermissions = [];

        if ($selectedRoleId) {

            $stmt = $pdo->prepare("
            SELECT permission_id
            FROM role_permissions
            WHERE role_id=?
        ");

            $stmt->execute([$selectedRoleId]);

            $rolePermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        /* =========================
       BUILD MENU TREE
    ========================== */

        $menuTree = [];
        $refs = [];

        foreach ($menus as $menu) {

            $menu['children'] = [];

            $refs[$menu['id']] = $menu;

            if ($menu['parent_id']) {

                $refs[$menu['parent_id']]['children'][] = &$refs[$menu['id']];
            } else {

                $menuTree[] = &$refs[$menu['id']];
            }
        }

        /* =========================
       BUILD PERMISSION MATRIX
    ========================== */

        $permissionMap = [];

        foreach ($permissions as $perm) {

            $slug = explode('.', $perm['slug']);

            $resource = strtolower($slug[0] ?? '');
            $action   = $slug[1] ?? '';

            $permissionMap[$resource][$action] = $perm;
        }

        $this->render(
            'permissions/index',
            compact(
                'roles',
                'menuTree',
                'permissionMap',
                'selectedRoleId',
                'rolePermissions'
            ),
            'main'
        );
    }

    /* =========================
       UPDATE ROLE PERMISSION
    ========================== */
    public function updateRolePermission()
    {
        validate_csrf();
        require_permission('permissions.edit');
        global $pdo;

        $roleId = intval($_POST['role_id'] ?? 0);
        $permissions = $_POST['permissions'] ?? [];

        if (!$roleId) {
            flash("Role belum dipilih", "danger");
            redirect("?action=permissions.index");
        }

        $pdo->beginTransaction();

        try {

            $pdo->prepare("
                DELETE FROM role_permissions 
                WHERE role_id = ?
            ")->execute([$roleId]);

            if (!empty($permissions)) {

                $stmt = $pdo->prepare("
                    INSERT INTO role_permissions (role_id, permission_id)
                    VALUES (?, ?)
                ");

                foreach ($permissions as $permId) {
                    $stmt->execute([$roleId, intval($permId)]);
                }
            }

            $pdo->commit();
            flash("Hak akses berhasil diperbarui", "success");
        } catch (Exception $e) {
            $pdo->rollBack();
            flash("Terjadi kesalahan saat menyimpan", "danger");
        }

        redirect("?action=permissions.index&role_id=" . $roleId);
    }
}
