<?php
$business_name = htmlspecialchars($business_name);
$owner_name    = htmlspecialchars($owner_name);
$email         = htmlspecialchars($email);
$link          = htmlspecialchars($link);
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="margin:0;padding:0;background:#f3f4f6;font-family:ui-sans-serif,system-ui;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:24px;">
        <tr>
            <td align="center">

                <table width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border-radius:16px;padding:32px;">

                    <!-- TITLE -->
                    <tr>
                        <td style="text-align:center;">
                            <h3 style="margin:0;font-size:18px;font-weight:600;color:#111827;">
                                Registrasi Baru
                            </h3>
                        </td>
                    </tr>

                    <!-- DATA -->
                    <tr>
                        <td style="padding-top:20px;font-size:14px;color:#374151;">
                            <p><strong>Usaha:</strong> <?= $business_name ?></p>
                            <p><strong>Owner:</strong> <?= $owner_name ?></p>
                            <p><strong>Email:</strong> <?= $email ?></p>
                        </td>
                    </tr>

                    <!-- BUTTON -->
                    <tr>
                        <td style="padding-top:24px;text-align:center;">
                            <a href="<?= $link ?>"
                                style="
                       display:inline-block;
                       padding:10px 18px;
                       background:#2563eb;
                       color:#ffffff;
                       text-decoration:none;
                       border-radius:10px;
                       font-size:14px;
                       font-weight:600;
                   ">
                                Buka Dashboard
                            </a>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>