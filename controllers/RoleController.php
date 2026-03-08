<?php
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/helpers.php';

require_once BASE_PATH . '/controllers/BaseController.php';


class RoleController extends BaseController
{
    /* ===========================
     * INDEX
     * =========================== */
    public function index()
    {
        require_permission('roles.view');
        global $pdo;

        $roles = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
        $this->render('roles/index', compact('roles'), 'main');
    }

    /* ===========================
     * CREATE FORM
     * =========================== */
    public function create()
    {
        require_permission('roles.create');
        $this->render('roles/create', [], 'main');
    }

    /* ===========================
     * STORE
     * =========================== */
    public function store()
    {
        validate_csrf();
        require_permission('roles.create');
        global $pdo;

        $name = trim($_POST['name']);
        $slug = strtolower(str_replace(' ', '', $name));


        if (!$name || !$slug) {
            flash("Name & Slug wajib diisi", "error");
            redirect_back();
        }

        $pdo->prepare("INSERT INTO roles (name, slug) VALUES (?,?)")
            ->execute([$name, $slug]);

        flash("Role berhasil dibuat", "success");
        redirect("?action=roles.index");
    }

    /* ===========================
     * EDIT FORM + PERMISSIONS
     * =========================== */
    public function edit()
    {
        require_permission('roles.edit');
        global $pdo;

        $id = intval($_GET['id']);

        $role = $pdo->prepare("SELECT * FROM roles WHERE id=?");
        $role->execute([$id]);
        $role = $role->fetch();

        $permissions = $pdo->query("SELECT * FROM permissions")->fetchAll();

        $rolePermissions = $pdo->prepare("
            SELECT permission_id FROM role_permissions WHERE role_id=?
        ");
        $rolePermissions->execute([$id]);
        $rolePermissions = array_column($rolePermissions->fetchAll(), 'permission_id');

        $this->render('roles/edit', compact('rolePermissions', 'permissions', 'role'), 'main');
    }

    /* ===========================
     * UPDATE + SYNC PERMISSION
     * =========================== */
    public function update()
    {
        require_permission('roles.edit');
        validate_csrf();
        global $pdo;

        try {

            $id   = intval($_GET['id']);
            $name = trim($_POST['name']);

            if (!$name) {
                flash("Nama role wajib diisi.", "error");
                redirect("?action=roles.edit&id=$id");
            }

            // 🔍 CEK ROLE ADA
            $stmt = $pdo->prepare("SELECT * FROM roles WHERE id=?");
            $stmt->execute([$id]);
            $role = $stmt->fetch();

            if (!$role) {
                flash("Role tidak ditemukan.", "error");
                redirect("?action=roles.index");
            }

            // 🔍 CEK DUPLIKAT NAME (UNIQUE)
            $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM roles 
            WHERE name=? AND id<>?
        ");
            $stmt->execute([$name, $id]);

            if ($stmt->fetchColumn() > 0) {
                flash("Nama role sudah digunakan.", "error");
                redirect("?action=roles.edit&id=$id");
            }

            // ✅ GENERATE SLUG OTOMATIS
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

            // 🔍 CEK DUPLIKAT SLUG
            $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM roles 
            WHERE slug=? AND id<>?
        ");
            $stmt->execute([$slug, $id]);

            if ($stmt->fetchColumn() > 0) {
                flash("Slug role bentrok. Ubah nama roles.", "error");
                redirect("?action=roles.edit&id=$id");
            }

            // ✅ UPDATE ROLE
            $stmt = $pdo->prepare("
            UPDATE roles 
            SET name=?, slug=? 
            WHERE id=?
        ");
            $stmt->execute([$name, $slug, $id]);

            flash("Role berhasil diupdate.", "success");
            redirect("?action=roles.index");
        } catch (PDOException $e) {

            // Kalau masih ada error SQL
            log_error("Role update gagal", $e);

            flash("Gagal update role: " . $e->getMessage(), "error");
            redirect_back();
        }
    }

    /* ===========================
     * DELETE
     * =========================== */
    public function delete()
    {
        require_permission('roles.delete');
        global $pdo;

        $id = intval($_GET['id']);
        $pdo->prepare("DELETE FROM roles WHERE id=?")->execute([$id]);

        flash("Role dihapus", "success");
        redirect("?action=roles.index");
    }

    public function show()
    {
        global $pdo;
        $id = $_GET['id'];

        $stmt = $pdo->prepare("SELECT * FROM roles WHERE id=?");
        $stmt->execute([$id]);
        $role = $stmt->fetch();


        $this->render('roles/show', compact('role'), 'main');
    }
}
