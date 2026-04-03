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

    $error = "";
    $success = "";

    function sendResetMail(string $toEmail, string $resetCode): void
    {
        $mail = new PHPMailer(true);

        $resetLink = 'https://quickbio.net/verify-reset/' . urlencode($resetCode);

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
        $mail->Subject = 'Reset your QuickBio password';

        $mail->Body = '
            <!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <title>Reset your QuickBio password</title>
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
                                            Reset your password
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center" style="font-size:16px;color:#a5afc3;padding-bottom:30px;">
                                            We received a request to reset your QuickBio password.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center" style="padding-bottom:30px;font-size:16px;color:#e0e5eb;">
                                            Click the button below to choose a new password.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center">
                                            <a 
                                                href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '"
                                                style="background-color:#78a0ff;color:#ffffff;padding:14px 28px;text-decoration:none;border-radius:8px;font-weight:bold;display:inline-block;">
                                                Reset Password
                                            </a>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center" style="padding-top:40px;font-size:13px;color:#a5afc3;">
                                            If you did not request this, you can safely ignore this email.
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
            "Reset your QuickBio password\n\n" .
            "We received a request to reset your password.\n\n" .
            "Reset it here:\n" .
            $resetLink . "\n\n" .
            "If you did not request this, you can safely ignore this email.";

        $mail->send();
    }

    if (isset($_POST["submit"])) {
        $email = trim($_POST["email"] ?? "");

        if ($email === "") {
            $error = "Please fill in all fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            try {
                $stmt = $mysql->prepare("
                    SELECT Email
                    FROM accounts
                    WHERE Email = :email
                    LIMIT 1
                ");
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $stmt->execute();

                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $resetCode = bin2hex(random_bytes(16));

                    $updateStmt = $mysql->prepare("
                        UPDATE accounts
                        SET reset_code = :reset_code
                        WHERE Email = :email
                        LIMIT 1
                    ");
                    $updateStmt->bindParam(":reset_code", $resetCode, PDO::PARAM_STR);
                    $updateStmt->bindParam(":email", $email, PDO::PARAM_STR);
                    $updateStmt->execute();

                    sendResetMail($email, $resetCode);
                }

                $success = "If an account with that email exists, a reset link has been sent.";
            } catch (PDOException $e) {
                $error = "A database error occurred. Please try again.";
            } catch (Exception $e) {
                $error = "The reset email could not be sent. Please try again.";
            } catch (Throwable $e) {
                $error = "An unexpected error occurred. Please try again.";
            }
        }
    }

    $isLoggedIn = !empty($_SESSION["loggedin"]);
    $currentUsername = $_SESSION["username"] ?? "";
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <link rel="icon" type="image/png" href="/Images/quickbio_round.png">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reset Password | QuickBio</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

        <link href="/styles/city.css" rel="stylesheet" type="text/css">
        <link href="/styles/style.css" rel="stylesheet" type="text/css">
        <link href="/styles/text-window.css" rel="stylesheet" type="text/css">
        <link href="/styles/button.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="container-fluid vh-100">
            <div class="account-menu dropdown">
                <button
                    class="account-menu-btn dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Account Menü">
                    <svg viewBox="0 0 24 24" class="login-icon">
                        <path fill="currentColor" d="M12 11a3.5 3.5 0 1 0-3.5-3.5A3.5 3.5 0 0 0 12 11zm0 2c-4 0-7 2-7 4.5V19h14v-1.5C19 15 16 13 12 13z"/>
                    </svg>
                </button>

                <ul class="dropdown-menu dropdown-menu-end account-dropdown">
                    <?php if ($isLoggedIn) { ?>
                        <li>
                            <a class="dropdown-item" href="/<?php echo htmlspecialchars($currentUsername, ENT_QUOTES, 'UTF-8'); ?>">
                                Mein Profil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/editing">
                                Profil bearbeiten
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="/logout">
                                Logout
                            </a>
                        </li>
                    <?php } else { ?>
                        <li>
                            <a class="dropdown-item" href="/login">
                                Login
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/create">
                                Registrieren
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <div class="row h-100">
                <div class="h-100 text-white d-flex justify-content-center align-items-center" id="main">
                    <div id="card">
                        <div id="header">
                            <div id="pfp-wrap">
                                <img src="/Images/quickbio.png" id="profilepic" alt="Profilbild">
                            </div>
                        </div>

                        <h1 id="name">Reset</h1>
                        <h2 id="description">Reset your password</h2>

                        <div class="container mt-4">
                            <div class="card editor-card shadow border-0" style="background-color: rgb(33, 40, 59); color: white;">
                                <div class="card-body">
                                    <?php if ($error !== "") { ?>
                                        <div class="alert alert-danger">
                                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    <?php } ?>

                                    <?php if ($success !== "") { ?>
                                        <div class="alert alert-success">
                                            <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    <?php } ?>

                                    <form method="post" action="/reset">
                                        <div class="mb-3">
                                            <label class="form-label text-light" for="email">Email</label>
                                            <input
                                                type="email"
                                                class="form-control custom-input"
                                                id="email"
                                                name="email"
                                                placeholder="Enter email"
                                                value="<?php echo htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>

                                        <div class="form-check mb-4">
                                            <a class="form-check-label text-light" href="/">
                                                Return home
                                            </a>
                                        </div>

                                        <button
                                            type="submit"
                                            name="submit"
                                            class="btn custom-login-btn w-100"
                                            style="color:white;border-radius:20px;border-color:white;">
                                            Send reset link
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        </div>
        <footer class="footer mt-5 py-3 text-center">
            <div class="container">

                <div class="footer-links mb-2">
                    <a href="/impressum">Impressum</a> |
                    <a href="/datenschutz">Datenschutz</a> |
                    <a href="/tos">AGB</a>
                </div>

                <div class="footer-copy">
                    © <?php echo date("Y"); ?> Jannis Diemer
                </div>

            </div>
        </footer>
    </body>
</html>