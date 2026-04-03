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

    function sendDeleteMail(string $toEmail, string $deleteCode): void
    {
        $mail = new PHPMailer(true);

        $deleteLink = 'https://quickbio.net/verify-delete/' . urlencode($deleteCode);

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
        $mail->Subject = 'Delete your QuickBio account';

        $mail->Body = '
            <!DOCTYPE html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <title>Delete your QuickBio account</title>
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
                                            Delete your QuickBio account
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center" style="font-size:16px;color:#a5afc3;padding-bottom:30px;">
                                            We received a request to delete your QuickBio account.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center" style="padding-bottom:30px;font-size:16px;color:#e0e5eb;">
                                            Click the button below to confirm the deletion of your account.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="center">
                                            <a 
                                                href="' . htmlspecialchars($deleteLink, ENT_QUOTES, 'UTF-8') . '" 
                                                style="background-color:#dc3545;color:#ffffff;padding:14px 28px;text-decoration:none;border-radius:8px;font-weight:bold;display:inline-block;">
                                                Confirm Deletion
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
            "Delete your QuickBio account\n\n" .
            "We received a request to delete your QuickBio account.\n\n" .
            "Confirm deletion here:\n" .
            $deleteLink . "\n\n" .
            "If you did not request this, you can safely ignore this email.";

        $mail->send();
    }

    if (isset($_POST["submit"])) {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($username === "" || $password === "") {
            $error = "Please fill in all fields.";
        } else {
            try {
                $stmt = $mysql->prepare("
                    SELECT ID, Email, PASSWORD
                    FROM accounts
                    WHERE ID = :id
                    LIMIT 1
                ");
                $stmt->bindParam(":id", $username, PDO::PARAM_STR);
                $stmt->execute();

                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $error = "No such username.";
                } elseif (!password_verify($password, $user["PASSWORD"])) {
                    $error = "Password invalid.";
                } else {
                    $deleteCode = bin2hex(random_bytes(16));
                    $email = $user["Email"];

                    $stmt = $mysql->prepare("
                        UPDATE accounts
                        SET delete_code = :delete_code
                        WHERE ID = :id
                        LIMIT 1
                    ");
                    $stmt->bindParam(":delete_code", $deleteCode, PDO::PARAM_STR);
                    $stmt->bindParam(":id", $username, PDO::PARAM_STR);
                    $stmt->execute();

                    sendDeleteMail($email, $deleteCode);

                    $success = "A verification link has been sent to your email address.";
                }
            } catch (PDOException $e) {
                $error = "A database error occurred. Please try again.";
            } catch (Exception $e) {
                $error = "The verification email could not be sent. Please try again.";
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
        <title>Delete | QuickBio</title>

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

                        <h1 id="name">Delete</h1>
                        <h2 id="description">Delete your profile</h2>

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

                                    <form method="post" action="/delete">
                                        <div class="mb-3">
                                            <label class="form-label text-light" for="username">Username</label>
                                            <input
                                                type="text"
                                                class="form-control custom-input"
                                                id="username"
                                                name="username"
                                                placeholder="Your username"
                                                value="<?php echo htmlspecialchars($_POST["username"] ?? "", ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-light" for="password">Password</label>
                                            <input
                                                type="password"
                                                class="form-control custom-input"
                                                id="password"
                                                name="password"
                                                placeholder="Enter password">
                                        </div>

                                        <div class="form-check mb-4">
                                            <a class="form-check-label text-light" href="/">
                                                Return home
                                            </a>
                                        </div>

                                        <button type="submit" name="submit" class="btn custom-login-btn w-100">
                                            Send verification link
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