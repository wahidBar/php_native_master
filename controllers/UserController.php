<?php
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/helpers.php';
require_once BASE_PATH . '/controllers/BaseController.php';

class UserController extends BaseController
{

    /* ===========================
     * SHOW DETAIL USER
     * =========================== */
    public function show()
    {
        global $pdo;

        // Pastikan id ada
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            die("Invalid ID.");
        }

        $id = (int) $_GET['id'];
        $stmt = $pdo->prepare("
        SELECT users.*, roles.name AS role_name
        FROM users
        LEFT JOIN roles ON roles.id = users.role_id
        WHERE users.id = :id
        LIMIT 1
        ");

        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        // var_dump($user);
        // die;

        if (!$user) {
            die("User not found.");
        }

        $this->render('users/show', compact('user'), 'main');
    }

    public function index()
    {
        require_permission('users.view');
        global $pdo;

        $limit  = 10;
        $page   = max(1, intval($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');
        $role   = intval($_GET['role'] ?? 0);

        $offset = ($page - 1) * $limit;

        $where  = [];
        $params = [];

        if ($search !== '') {
            $where[] = "(u.name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($role > 0) {
            $where[] = "u.role_id = ?";
            $params[] = $role;
        }

        $whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

        // COUNT TOTAL
        $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM users u
        LEFT JOIN roles r ON r.id = u.role_id
        $whereSQL
    ");
        $stmt->execute($params);
        $totalRows = $stmt->fetchColumn();

        $totalPages = ceil($totalRows / $limit);

        // DATA
        $stmt = $pdo->prepare("
        SELECT u.*, r.name as role_name
        FROM users u
        LEFT JOIN roles r ON r.id = u.role_id
        $whereSQL
        ORDER BY u.id DESC
        LIMIT $limit OFFSET $offset
    ");
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ROLES (filter dropdown)
        $roles = $pdo->query("SELECT id, name FROM roles ORDER BY name ASC")
            ->fetchAll(PDO::FETCH_ASSOC);

        // AJAX RESPONSE
        if (isset($_GET['ajax'])) {
            echo json_encode([
                'users'       => $users,
                'totalPages'  => $totalPages,
                'currentPage' => $page,
                'limit'       => $limit
            ]);
            exit;
        }


        $this->render('users/index', compact(
            'users',
            'roles',
            'totalPages',
            'page'
        ), 'main');
    }


    /* ===========================
     * CREATE FORM
     * =========================== */
    public function create()
    {
        global $pdo;

        $roles = $pdo->query("SELECT * FROM roles")->fetchAll();
        $this->render('users/create', compact('roles'), 'main');
    }


    /* ===========================
     * STORE (CREATE PROCESS)
     * =========================== */
    public function store()
    {
        try {
            validate_csrf();
            global $pdo;

            $name     = trim($_POST['name']);
            $email    = trim($_POST['email']);
            $password = trim($_POST['password']);
            $role_id  = $_POST['role_id'];
            $phone    = $_POST['phone'];
            $addr     = $_POST['address'];
            $dob      = $_POST['date_of_birth'];
            $gender   = $_POST['gender'];
            $status   = $_POST['is_active'];

            // Validasi dasar
            if ($name === '' || $email === '' || strlen($password) < 6) {
                flash("Validasi gagal: name/email kosong atau password < 6 karakter.", "error");
                redirect("?action=users.create");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash("Format email tidak valid.", "error");
                redirect("?action=users.create");
            }

            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                flash("Email sudah terdaftar, silakan gunakan email lain.", "error");
                redirect("?action=users.create");
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);

            /* Upload foto */
            $filename = null;
            if (!empty($_FILES['profile_photo']['name'])) {
                $filename = time() . '_' . $_FILES['profile_photo']['name'];
                $dest = "uploads/images/profile_photo/$filename";
                move_uploaded_file($_FILES['profile_photo']['tmp_name'], $dest);
            }

            /* INSERT FIX */
            $sql = "INSERT INTO users (
            role_id,
            name,
            email,
            email_verified_at,
            password,
            phone,
            address,
            date_of_birth,
            gender,
            profile_photo,
            is_active,
            remember_token,
            deleted_at,
            created_at,
            updated_at
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            $pdo->prepare($sql)->execute([
                $role_id,
                $name,
                $email,
                date('Y-m-d H:i:s'),
                $hash,
                $phone,
                $addr,
                $dob,
                $gender,
                $filename,
                intval($status),
                null,
                null,
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);

            flash("User berhasil dibuat!", "success");
            redirect("?action=users.index");
        } catch (PDOException $e) {
            // Tampilkan error ke view
            flash("Error: " . $e->getMessage(), "error");
            redirect_back();
        }
    }



    /* ===========================
     * EDIT FORM
     * =========================== */
    public function edit()
    {
        global $pdo;

        if (!isset($_GET['id'])) {
            flash("ID tidak ditemukan.");
            redirect("?action=users.index");
        }

        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            flash("User tidak ditemukan.", "warning");
            redirect("?action=users.index");
        }

        $roles = $pdo->query("SELECT * FROM roles")->fetchAll();
        $this->render('users/edit', compact('roles', 'user'), 'main');
    }


    /* ===========================
     * UPDATE (PROCESS)
     * =========================== */
    /* =========================== */
    public function update()
    {
        try {
            validate_csrf();
            global $pdo;

            $id       = intval($_POST['id']);
            $name     = trim($_POST['name']);
            $email    = trim($_POST['email']);
            $role_id  = $_POST['role_id'];
            $phone    = $_POST['phone'];
            $addr     = $_POST['address'];
            $dob      = $_POST['date_of_birth'];
            $gender   = $_POST['gender'];
            $status   = $_POST['is_active'];
            $password = trim($_POST['password']); // opsional

            if ($name == "" || $email == "") {
                flash("Nama & email wajib diisi.");
                redirect_back();
            }


            /* Ambil data lama */
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
            $stmt->execute([$id]);
            $oldUser = $stmt->fetch();

            if (!$oldUser) {
                flash("User tidak ditemukan.");
                redirect("?action=users.index");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash("Format email tidak valid.", "error");
                redirect("?action=users.create");
            }
            
            /* === HANDLE PASSWORD === */
            $newPasswordHash = $oldUser['password']; // default tidak update

            if ($password !== "") {
                if (strlen($password) < 6) {
                    flash("Password minimal 6 karakter.");
                    redirect_back();
                }
                $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
            }

            /* === HANDLE FOTO === */
            $oldPhoto  = $oldUser['profile_photo'];
            $filename  = $oldPhoto;
            $uploadDir = "uploads/images/profile_photo/";

            if (!empty($_FILES['profile_photo']['name'])) {

                // delete foto lama
                if ($oldPhoto && file_exists($uploadDir . $oldPhoto)) {
                    unlink($uploadDir . $oldPhoto);
                }

                // upload foto baru
                $filename = time() . "_" . basename($_FILES['profile_photo']['name']);
                move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadDir . $filename);
            }

            /* === UPDATE — FIX FULL COLUMN === */
            $sql = "
            UPDATE users SET
                role_id = ?,
                name = ?,
                email = ?,
                password = ?,
                phone = ?,
                address = ?,
                date_of_birth = ?,
                gender = ?,
                profile_photo = ?,
                is_active = ?,
                updated_at = NOW()
            WHERE id = ?
        ";

            $pdo->prepare($sql)->execute([
                $role_id,
                $name,
                $email,
                $newPasswordHash,
                $phone,
                $addr,
                $dob,
                $gender,
                $filename,
                intval($status),
                $id
            ]);

            flash("User berhasil diupdate!", "success");
            redirect("?action=users.index");
        } catch (PDOException $e) {
            flash("Error: " . $e->getMessage(), 'error');
            redirect_back();
        }
    }


    /* ===========================
     * DELETE USER
     * =========================== */
    public function delete()
    {
        global $pdo;

        $id = intval($_GET['id']);

        // Ambil data foto sebelum hapus
        $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id=?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user && $user['profile_photo']) {
            $file = "uploads/profile_photo/" . $user['profile_photo'];
            if (file_exists($file)) {
                unlink($file);
            }
        }

        // Hapus user
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);

        flash("User berhasil dihapus.", "success");
        redirect("?action=users.index");
    }
}
