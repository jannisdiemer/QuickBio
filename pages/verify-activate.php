<?php
    declare(strict_types=1);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require __DIR__ . '/../mysql.php';
    require __DIR__ . '/../phpmailer/src/PHPMailer.php';
    require __DIR__ . '/../phpmailer/src/SMTP.php';
    require __DIR__ . '/../phpmailer/src/Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $text = "No activation link.";
    $color = "red";
    $activated = false;

    function sendActivationMail(string $toEmail, string $activationCode): void
    {
        $mail = new PHPMailer(true);

        $activationLink = 'https://quickbio.net/activate/' . urlencode($activationCode);

        $mail->isSMTP();
        $mail->Host = 'SMTP-SERVER';
        $mail->SMTPAuth = true;
        $mail->Username = 'MAIL-ADRESS';
        $mail->Password = 'PASSWORD';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 'PORT';

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->Sender = 'noreply@quickbio.net';
        $mail->setFrom('noreply@quickbio.net', 'QuickBio');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Activate your QuickBio account';

        $mail->Body = '
            <!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <title>Activate your QuickBio account</title>
                </head>
                <body style="margin:0;padding:0;background-color:#141824;font-family:Arial,sans-serif;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#141824;padding:40px 0;">
                        <tr>
                            <td align="center">
                                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color:#21283b;border-radius:10px;padding:40px;color:#e0e5eb;">
                                    <tr>
                                        <td align="center">
                                            <img 
                                                src="https://quickbio.net/Images/quickbio.png" 
                                                width="80" 
                                                alt="QuickBio Logo" 
                                                style="display:block;margin-bottom:20px;border-radius:50%;">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center" style="font-size:26px;font-weight:bold;padding-bottom:10px;">
                                            Welcome to QuickBio
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center" style="font-size:16px;color:#a5afc3;padding-bottom:30px;">
                                            Your profile hub for everything online.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center" style="padding-bottom:30px;font-size:16px;color:#e0e5eb;">
                                            Click the button below to activate your account.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center">
                                            <a 
                                                href="' . htmlspecialchars($activationLink, ENT_QUOTES, 'UTF-8') . '"
                                                style="background-color:#78a0ff;color:#ffffff;padding:14px 28px;text-decoration:none;border-radius:8px;font-weight:bold;display:inline-block;">
                                                Activate Account
                                            </a>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center" style="padding-top:40px;font-size:13px;color:#a5afc3;">
                                            If you did not request this email, you can safely ignore it.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center" style="padding-top:20px;font-size:12px;color:#6f7893;">
                                            QuickBio · Saarland, Germany
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>
            </html>';

        $mail->AltBody =
            "Activate your QuickBio account\n\n" .
            "Click the link below to activate your account:\n" .
            $activationLink . "\n\n" .
            "If you did not request this email, you can safely ignore it.";

        $mail->send();
    }

    $code = (isset($id) && is_string($id) && $id !== '') ? $id : null;

    if ($code !== null) {
        try {
            $stmt = $mysql->prepare("
                UPDATE accounts
                SET activated = 1,
                    activation_code = NULL
                WHERE activation_code = :code
                LIMIT 1
            ");
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() === 1) {
                $text = "Your account has been successfully activated.";
                $color = "green";
                $activated = true;
            } else {
                $text = "Invalid or expired activation link.";
                $color = "red";
                $activated = false;
            }
        } catch (PDOException $e) {
            $text = "A database error occurred.";
            $color = "red";
            $activated = false;
        }
    }

    if (isset($_POST['submit'])) {
        $email = trim($_POST['email'] ?? '');

        if ($email === '') {
            $text = "Please fill in all fields.";
            $color = "red";
            $activated = false;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $text = "Please enter a valid email address.";
            $color = "red";
            $activated = false;
        } else {
            try {
                $stmt = $mysql->prepare("
                    SELECT Email
                    FROM accounts
                    WHERE Email = :email
                    LIMIT 1
                ");
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();

                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $activationCode = bin2hex(random_bytes(16));

                    $updateStmt = $mysql->prepare("
                        UPDATE accounts
                        SET activation_code = :activation_code
                        WHERE Email = :email
                        LIMIT 1
                    ");
                    $updateStmt->bindParam(':activation_code', $activationCode, PDO::PARAM_STR);
                    $updateStmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $updateStmt->execute();

                    sendActivationMail($email, $activationCode);
                }

                $text = "If an account with that email exists, a new activation link has been sent.";
                $color = "green";
                $activated = false;
            } catch (PDOException $e) {
                $text = "A database error occurred.";
                $color = "red";
                $activated = false;
            } catch (Exception $e) {
                $text = "The activation email could not be sent.";
                $color = "red";
                $activated = false;
            } catch (Throwable $e) {
                $text = "An unexpected error occurred.";
                $color = "red";
                $activated = false;
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="icon" type="image/png" href="/Images/quickbio_round.png">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Activate | QuickBio</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link href="/styles/city.css" rel="stylesheet" type="text/css">
        <link href="/styles/style.css" rel="stylesheet" type="text/css">
        <link href="/styles/text-window.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="container-fluid vh-100">
            <div class="row h-100">
                <div class="h-100 text-white d-flex justify-content-center align-items-center" id="main">
                    <div id="card">
                        <a href="/login" class="login-icon-btn" aria-label="Login">
                            <svg viewBox="0 0 24 24" class="login-icon">
                                <path fill="currentColor" d="M12 11a3.5 3.5 0 1 0-3.5-3.5A3.5 3.5 0 0 0 12 11zm0 2c-4 0-7 2-7 4.5V19h14v-1.5C19 15 16 13 12 13z"/>
                            </svg>
                        </a>

                        <div id="header">
                            <div id="pfp-wrap">
                                <img src="/Images/quickbio.png" id="profilepic" alt="Profilbild">
                            </div>
                        </div>

                        <h1 id="name" style="color: <?php echo htmlspecialchars($color, ENT_QUOTES, 'UTF-8'); ?>;">
                            <?php echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); ?>
                        </h1>

                        <?php if ($activated) { ?>
                            <a class="form-check-label text-light" href="/">
                                Return home
                            </a>
                        <?php } else { ?>
                            <form method="post" action="/activate">
                                <div class="mb-3">
                                    <label class="form-label text-light" for="email">Email</label>
                                    <input
                                        type="email"
                                        class="form-control custom-input"
                                        id="email"
                                        name="email"
                                        placeholder="your@email.com"
                                        value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>

                                <button type="submit" name="submit" class="btn custom-login-btn w-100">
                                    Resend activation code
                                </button>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <footer class="footer mt-5 py-3 text-center">
            <div class="container">

                <div class="footer-links mb-2">
                    <a href="/impressum">Impressum</a> |
                    <a href="/datenschutz">Datenschutz</a> |
                    <a href="/tos">AGB</a>
                </div>

                <div class="footer-copy">
                    © <?php echo date("Y"); ?> <a href="/jannisdiemer">Jannis Diemer</a>
                </div>

            </div>
        </footer>
    </body>
</html>