<?php
$link = htmlspecialchars($link);
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="margin:0;padding:0;background:#f3f4f6;font-family:ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:24px;">
        <tr>
            <td align="center">

                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border-radius:16px;padding:32px;box-shadow:0 10px 25px rgba(0,0,0,0.05);">

                    <!-- TITLE -->
                    <tr>
                        <td style="text-align:center;">
                            <h2 style="margin:0;font-size:20px;font-weight:600;color:#111827;">
                                Aktivasi Akun
                            </h2>
                        </td>
                    </tr>

                    <!-- TEXT -->
                    <tr>
                        <td style="padding-top:16px;text-align:center;color:#4b5563;font-size:14px;">
                            Akun Anda telah disetujui oleh admin.
                        </td>
                    </tr>

                    <!-- BUTTON -->
                    <tr>
                        <td style="padding-top:24px;text-align:center;">
                            <a href="<?= $link ?>"
                                style="
                       display:inline-block;
                       padding:12px 20px;
                       background:#16a34a;
                       color:#ffffff;
                       text-decoration:none;
                       border-radius:10px;
                       font-size:14px;
                       font-weight:600;
                       box-shadow:0 4px 10px rgba(22,163,74,0.3);
                   ">
                                Aktivasi Akun
                            </a>
                        </td>
                    </tr>

                    <!-- INFO -->
                    <tr>
                        <td style="padding-top:20px;text-align:center;font-size:12px;color:#6b7280;">
                            Link berlaku selama 24 jam.
                        </td>
                    </tr>

                    <!-- FALLBACK -->
                    <tr>
                        <td style="padding-top:20px;text-align:center;font-size:12px;color:#9ca3af;">
                            Jika tombol tidak bekerja:<br>
                            <a href="<?= $link ?>" style="color:#2563eb;word-break:break-all;">
                                <?= $link ?>
                            </a>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>