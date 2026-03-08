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
                (SELECT COUNT(*) 
                 FROM role_permissions rp 
                 WHERE rp.role_id = r.id) as total_permission
            FROM roles r
            ORDER BY r.id ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $selectedRoleId = $_GET['role_id'] ?? ($roles[0]['id'] ?? 0);

        /* =========================
           MENUS
        ========================== */
        $menus = $pdo->query("
            SELECT *
            FROM menus
            ORDER BY order_number ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        /* =========================
           ALL PERMISSIONS
        ========================== */
        $permissions = $pdo->query("
            SELECT * FROM permissions
        ")->fetchAll(PDO::FETCH_ASSOC);

        /* =========================
           ROLE PERMISSIONS
        ========================== */
        $rolePermissions = [];

        if ($selectedRoleId) {
            $stmt = $pdo->prepare("
                SELECT permission_id 
                FROM role_permissions 
                WHERE role_id = ?
            ");
            $stmt->execute([$selectedRoleId]);
            $rolePermissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        /* =========================
           BUILD MATRIX
        ========================== */
        $matrix = [];

        foreach ($menus as $menu) {

            if (!$menu['route']) continue;

            // Ambil resource dari route → users.index → user
            $routeParts = explode('.', $menu['route']);
            $resource = strtolower($routeParts[0]);

            $matrix[$resource] = [
                'menu_name' => $menu['name'],
                'route'     => $menu['route'],
                'actions'   => []
            ];

            // Cari permission berdasarkan slug: users.*
            foreach ($permissions as $perm) {

                $slugParts = explode('.', $perm['slug']);
                $permResource = strtolower($slugParts[0] ?? '');
                $action = $slugParts[1] ?? null;

                // Cocokkan resource (user ↔ users)
                if (rtrim($permResource, 's') === rtrim($resource, 's')) {

                    $matrix[$resource]['actions'][$action] = $perm;
                }
            }
        }

        $this->render(
            'permissions/index',
            compact('roles', 'matrix', 'selectedRoleId', 'rolePermissions'),
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