<?php

class AuthController
{
    public function verify_mysql_old_password($plain, $mysqlHash)
    {
        $hash = '*' . strtoupper(sha1(sha1($plain, true)));
        return hash_equals($mysqlHash, $hash);
    }


    public function login()
    {
        include 'views/auth/login.php';
    }

    public function loginProcess()
    {
        try {
            validate_csrf();
            global $pdo;

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                $email    = $_POST['email'];
                $password = $_POST['password'];
                // GET USER
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND deleted_at IS NULL");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                // dd($email, $user);
                // 🚨 USER TIDAK ADA
                if (!$user) {
                    flash("Email tidak ditemukan.", "error");
                    redirect("?action=auth.login");
                }

                // CHECK PASSWORD
                $validPassword = false;

                // 1️⃣ PASSWORD HASH MODERN
                if (password_verify($password, $user['password'])) {

                    // Rehash jika perlu
                    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {

                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $pdo->prepare("UPDATE users SET password=? WHERE id=?")
                            ->execute([$newHash, $user['id']]);
                    }

                    $validPassword = true;
                }

                // 2️⃣ MYSQL OLD PASSWORD (*HASH)
                elseif (substr($user['password'], 0, 1) === '*') {

                    if ($this->verify_mysql_old_password($password, $user['password'])) {

                        $validPassword = true;

                        // ✅ UPGRADE KE BCRYPT
                        $newHash = password_hash($password, PASSWORD_DEFAULT);

                        $pdo->prepare("UPDATE users SET password=? WHERE id=?")
                            ->execute([$newHash, $user['id']]);
                    }
                }

                // ❌ PASSWORD SALAH
                if (!$validPassword) {
                    flash("Password salah.", "error");
                    redirect("?action=auth.login");
                }

                // CHECK ACTIVE
                if ($user['is_active'] == 0) {
                    flash("Akun belum aktif.", "warning");
                    redirect("?action=auth.login");
                }

                // CHECK EMAIL VERIFIED
                if ($user['email_verified_at'] == null) {
                    flash("Email belum diverifikasi.", "warning");
                    redirect("?action=auth.login");
                }

                // SIMPAN SESSION — LENGKAP UNTUK CRUD + ROLE
                $_SESSION['auth'] = [
                    'id'              => $user['id'],
                    'name'            => $user['name'],
                    'email'           => $user['email'],
                    'role_id'         => $user['role_id'],

                    // Data tambahan
                    'phone'           => $user['phone'],
                    'address'         => $user['address'],
                    'date_of_birth'   => $user['date_of_birth'],
                    'gender'          => $user['gender'],
                    'profile_photo'   => $user['profile_photo'],

                    'is_active'       => $user['is_active'],
                    'email_verified_at' => $user['email_verified_at'],
                    'created_at'      => $user['created_at'],
                    'updated_at'      => $user['updated_at']
                ];

                flash("Login berhasil. Selamat datang, {$user['name']}!", "success");
                include 'views/layouts/index.php';
            }

            require_once "views/auth/login.php";
        } 
        // catch (Exception $e) {
        //     die($e->getMessage());
        // }

        catch (Exception $e) {
            log_error("Login gagal", $e);
            flash("Terjadi kesalahan saat login.", "error");
            redirect_back();
        }
    }

    public function logout()
    {
        session_start();

        // Hanya hapus data user login
        unset($_SESSION['auth']);

        flash("Anda telah logout.", "success");

        redirect("?action=auth.login");
    }


    public function register()
    {
        include 'views/auth/register.php';
    }

    public function registerProcess()
    {
        validate_csrf();
        global $pdo;

        $name     = trim($_POST['name']);
        $email    = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Cek duplikasi email
        $cek = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $cek->execute([$email]);

        if ($cek->fetch()) {
            flash("Email sudah digunakan.", "warning");
            redirect("?action=auth.register");
        }

        $verifyToken = bin2hex(random_bytes(16));

        $sql = "INSERT INTO users (role_id,name,email,password,email_verified_at,is_active,verify_token,created_at,updated_at)
                VALUES (?,?,?,?,?,?,?,NOW(),NOW())";

        $pdo->prepare($sql)->execute([
            2, // Default role = User
            $name,
            $email,
            $password,
            null,
            0,
            $verifyToken
        ]);

        flash("Registrasi berhasil! Periksa email Anda untuk verifikasi.", "success");
        redirect("?action=auth.login");
    }

    public function verifyEmail()
    {
        global $pdo;

        $token = $_GET['token'] ?? '';

        $stmt = $pdo->prepare("SELECT id FROM users WHERE verify_token=?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            flash("Token verifikasi tidak valid.", "error");
            redirect("?action=auth.login");
        }

        $pdo->prepare(
            "UPDATE users SET email_verified_at=NOW(), is_active=1, verify_token=NULL WHERE id=?"
        )->execute([$user['id']]);

        flash("Email berhasil diverifikasi! Silakan login.", "success");
        redirect("?action=auth.login");
    }
}
