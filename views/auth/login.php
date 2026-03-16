<?php
// Redirect jika sudah login
if (isset($_SESSION['auth'])) {
    $role = $_SESSION['auth']['role_id'];
    header("Location: ?action=dashboard");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Login - Admin Panel</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    
    <style>
        body {
            background: #f1f4f8;
        }

        .auth-card {
            background: #fff;
            border-radius: 10px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>

<body>

    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">

        <div class="auth-card" style="width: 400px;">

            <h3 class="text-center mb-3">Login</h3>

            <?php flash_show(); ?>

            <form action="?action=auth.loginProcess" method="POST">
                <?= csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input name="email" type="text" class="form-control" required>
                </div>

                <div class="mb-3 position-relative">
                    <label class="form-label">Password</label>
                    <input name="password" type="password" class="form-control password-field" required>
                    <span class="toggle-password" style="position:absolute; top:38px; right:12px; cursor:pointer;">
                            👁️
                        </span>
                </div>

                <button class="btn btn-primary w-100 mt-2">Login</button>

                <p class="text-center text-muted mt-3 small">
                    Login sebagai <b>superadmin@test.com</b></b><br>
                    Password: <b>password</b>
                </p>

                <p class="text-center mt-3 mb-0">
                    Belum punya akun?
                    <a href="?action=auth.register">Daftar sekarang</a>
                </p>
            </form>
        </div>
    </div>

</body>

</html>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {

        // Toggle password show/hide
        $(".toggle-password").on("click", function() {
            let input = $(this).siblings(".password-field");
            let type = input.attr("type") === "password" ? "text" : "password";
            input.attr("type", type);
            $(this).text(type === "password" ? "👁️" : "🙈");
        });
    });
</script>