<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register - User</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

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
            <h3 class="text-center mb-4 fw-bold">Daftar Akun</h3>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger" id="alert-error">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form id="registerForm" action="?action=auth.doRegister" method="POST">
                <?= csrf_field(); ?>

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input name="email" type="email" class="form-control" required>
                </div>

                <div class="mb-3 position-relative">
                    <label class="form-label">Password</label>
                    <input name="password" type="password" class="form-control password-field" required>
                    <span class="toggle-password" style="position:absolute; top:38px; right:12px; cursor:pointer;">
                        👁️
                    </span>
                </div>

                <div class="mb-3 position-relative">
                    <label class="form-label">Konfirmasi Password</label>
                    <input name="password_confirmation" type="password" class="form-control password-field" required>
                    <span class="toggle-password" style="position:absolute; top:38px; right:12px; cursor:pointer;">
                        👁️
                    </span>
                </div>

                <button class="btn btn-success w-100 mt-3 py-2 fw-semibold">Buat Akun</button>

                <p class="text-center mt-3">
                    Sudah punya akun?
                    <a href="?action=auth.login" class="fw-semibold">Login</a>
                </p>
            </form>
        </div>
    </div>
</body>

</html>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {

        // Toggle password show/hide
        $(".toggle-password").on("click", function() {
            let input = $(this).siblings(".password-field");
            let type = input.attr("type") === "password" ? "text" : "password";
            input.attr("type", type);
            $(this).text(type === "password" ? "👁️" : "🙈");
        });

        // Basic client-side validation
        $("#registerForm").on("submit", function(e) {
            let pass = $("input[name=password]").val();
            let confirm = $("input[name=password_confirmation]").val();

            if (pass !== confirm) {
                e.preventDefault();
                $("#alert-error").remove();

                $(".card").prepend(
                    `<div class="alert alert-danger" id="alert-error">Konfirmasi password tidak sama!</div>`
                );

                // shake animation
                $(".card").css({
                    position: "relative"
                });
                $(".card").animate({
                        left: "-10px"
                    }, 80)
                    .animate({
                        left: "10px"
                    }, 80)
                    .animate({
                        left: "0px"
                    }, 80);

                return false;
            }
        });
    });
</script>