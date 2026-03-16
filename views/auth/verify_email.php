<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Email</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <style>
        body {
            background: #eef2f7;
        }

        .verify-box {
            background: #fff;
            padding: 35px;
            border-radius: 12px;
            width: 420px;
            box-shadow: 0 2px 18px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center" style="min-height:100vh;">

    <div class="verify-box text-center">
        <h3>Email Berhasil Diverifikasi ✔</h3>

        <p class="mt-3">
            Email Anda telah diverifikasi. Silakan login untuk melanjutkan.
        </p>

        <a href="?action=auth.login" class="btn btn-primary mt-3 w-100">
            Ke Halaman Login
        </a>
    </div>

</div>

</body>
</html>
