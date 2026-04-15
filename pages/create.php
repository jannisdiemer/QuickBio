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
                                            style="display:block;margin-bottom:20px;border-radius:50%;"
                                        >
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
                                            style="background-color:#78a0ff;color:#ffffff;padding:14px 28px;text-decoration:none;border-radius:8px;font-weight:bold;display:inline-block;"
                                        >
                                            Activate Account
                                        </a>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" style="padding-top:40px;font-size:13px;color:#a5afc3;">
                                        If you did not create this account, you can ignore this email.
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
            "Welcome to QuickBio\n\n" .
            "Activate your account:\n" .
            $activationLink . "\n\n" .
            "If you did not create this account, you can ignore this email.";

        $mail->send();
    }

    if (isset($_POST["submit"])) {
        $email = trim($_POST["email"] ?? "");
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $rpassword = $_POST["rpassword"] ?? "";
        $logincode = trim($_POST["logincode"] ?? "");
		$accepted = isset($_POST["accepted"]);

		if (
			$email === "" ||
			$username === "" ||
			$password === "" ||
			$rpassword === "" ||
			$logincode === ""
		) {
			$error = "Please fill in all fields.";
		} elseif (!$accepted) {
			$error = "You must accept the Datenschutz and AGB to create an account.";
		} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$error = "Please enter a valid email address.";
		} elseif ($password !== $rpassword) {
			$error = "Passwords do not match.";
		} elseif (strlen($password) < 8) {
			$error = "Your password must be at least 8 characters long.";
		} elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
			$error = "Username may only contain letters, numbers, underscores and hyphens.";
		} else {
            try {
                $stmt = $mysql->prepare("
                    SELECT ID, Email
                    FROM accounts
                    WHERE ID = :id OR Email = :email
                    LIMIT 1
                ");
                $stmt->bindParam(":id", $username, PDO::PARAM_STR);
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $stmt->execute();

                $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingUser) {
                    if (($existingUser["ID"] ?? "") === $username) {
                        $error = "This username is already taken.";
                    } elseif (($existingUser["Email"] ?? "") === $email) {
                        $error = "This email is already registered.";
                    } else {
                        $error = "Account already exists.";
                    }
                } else {
                    $stmt = $mysql->prepare("
                        SELECT code
                        FROM Codes
                        WHERE code = :code
                        LIMIT 1
                    ");
                    $stmt->bindParam(":code", $logincode, PDO::PARAM_STR);
                    $stmt->execute();

                    $codeExists = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$codeExists) {
                        $error = "Invalid login code.";
                    } else {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $activationCode = bin2hex(random_bytes(16));

                        $mysql->beginTransaction();

                        $insertStmt = $mysql->prepare("
                            INSERT INTO accounts (ID, Email, PASSWORD, activation_code)
                            VALUES (:id, :email, :password, :activation_code)
                        ");
                        $insertStmt->bindParam(":id", $username, PDO::PARAM_STR);
                        $insertStmt->bindParam(":email", $email, PDO::PARAM_STR);
                        $insertStmt->bindParam(":password", $passwordHash, PDO::PARAM_STR);
                        $insertStmt->bindParam(":activation_code", $activationCode, PDO::PARAM_STR);
                        $insertStmt->execute();

                        sendActivationMail($email, $activationCode);

                        $deleteStmt = $mysql->prepare("
                            DELETE FROM Codes
                            WHERE code = :code
                            LIMIT 1
                        ");
                        $deleteStmt->bindParam(":code", $logincode, PDO::PARAM_STR);
                        $deleteStmt->execute();

                        $mysql->commit();

                        $success = "Account created. Please check your email to activate your account.";
                    }
                }
            } catch (PDOException $e) {
                if ($mysql->inTransaction()) {
                    $mysql->rollBack();
                }
                $error = "A database error occurred. Please try again.";
            } catch (Exception $e) {
                if ($mysql->inTransaction()) {
                    $mysql->rollBack();
                }
                $error = "The account was not created because the activation email could not be sent.";
            } catch (Throwable $e) {
                if ($mysql->inTransaction()) {
                    $mysql->rollBack();
                }
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
        <title>Create | QuickBio</title>

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
                    aria-label="Account menu">
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

                        <h1 id="name">Create</h1>
                        <h2 id="description">Create your profile</h2>

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

                                    <form method="post" action="/create" novalidate>
                                        <div class="mb-3">
                                            <label class="form-label text-light" for="email">Email</label>
                                            <input
                                                type="email"
                                                class="form-control custom-input"
                                                id="email"
                                                name="email"
                                                placeholder="your@email.com"
                                                value="<?php echo htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>

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

                                        <div class="mb-3">
                                            <label class="form-label text-light" for="rpassword">Repeat Password</label>
                                            <input
                                                type="password"
                                                class="form-control custom-input"
                                                id="rpassword"
                                                name="rpassword"
                                                placeholder="Repeat password">
                                        </div>
										
										<div class="form-check mb-3">
											<input
												class="form-check-input"
												type="checkbox"
												id="accepted"
												name="accepted"
												value="1"
												<?php echo isset($_POST["accepted"]) ? "checked" : ""; ?>
												required>
											<label class="form-check-label text-light" for="accepted">
												I have read and accept the <a href="/datenschutz">Privacy Policy</a> and <a href="/tos">Terms of Service</a>.
											</label>
										</div>

                                        <div class="mb-3">
                                            <label class="form-label text-light" for="logincode">Login Code</label>
                                            <input
                                                type="text"
                                                class="form-control custom-input"
                                                id="logincode"
                                                name="logincode"
                                                placeholder="Enter login code"
                                                value="<?php echo htmlspecialchars($_POST["logincode"] ?? "", ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>

                                        <div class="form-check mb-4">
                                            <a class="form-check-label text-light" href="/login">
                                                Login to your existing account
                                            </a>

                                            <a class="form-check-label text-light" href="mailto:support@quickbio.net?subject=Request%20Invite%20Code">
                                                Request an invite code
                                            </a>
                                        </div>

                                        <button
                                            type="submit"
                                            name="submit"
                                            class="btn custom-login-btn w-100"
                                            style="color:white;border-radius:20px;border-color:white;">
                                            Create
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
                    © <?php echo date("Y"); ?> <a href="/jannisdiemer">Jannis Diemer</a>
                </div>

            </div>
        </footer>
    </body>
</html>