<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Akun Disetujui</title>
</head>

<body style="font-family: Arial;">

    <h2>Halo <?= htmlspecialchars($name) ?> 👋</h2>

    <p>
        Selamat! Akun Anda telah <b>disetujui oleh admin</b>.
    </p>

    <p>
        Sekarang Anda sudah dapat login dan menggunakan sistem RKD-CAFE.
    </p>

    <br>

    <a href="<?= $_ENV['APP_URL'] ?>/login"
        style="padding:10px 20px; background:#28a745; color:#fff; text-decoration:none;">
        Login Sekarang
    </a>

    <br><br>

    <p>Terima kasih 🙏</p>

</body>

</html>