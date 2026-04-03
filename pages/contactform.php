<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $success = false;
    $error = '';

    $name = '';
    $email = '';
    $subject = '';
    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $website = $_POST['website'] ?? '';

        if ($website !== '') {
            $error = 'Ungültige Anfrage.';
        } elseif ($name === '' || $email === '' || $subject === '' || $message === '') {
            $error = 'Bitte alle Felder ausfüllen.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Ungültige E-Mail.';
        } else {
            $to = 'support@quickbio.net';

            $mailSubject = 'Kontaktformular: ' . $subject;

            $mailMessage = "Name: $name\n";
            $mailMessage .= "E-Mail: $email\n\n";
            $mailMessage .= "Nachricht:\n$message";

            $headers = "From: QuickBio <noreply@quickbio.net>\r\n";
            $headers .= "Reply-To: $email\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            if (mail($to, $mailSubject, $mailMessage, $headers)) {
                $success = true;
                $name = $email = $subject = $message = '';
            } else {
                $error = 'Fehler beim Senden.';
            }
        }
    }
?>

<html>
    <head> 
        <link rel="icon" type="image/png" href="/Images/quickbio_round.png"> 
        <meta charset="utf-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"> 
        <title>Kontakt | QuickBio</title> 

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

        <link href="/styles/city.css" rel="stylesheet">
        <link href="/styles/style.css" rel="stylesheet">
        <link href="/styles/text-window.css" rel="stylesheet">
        <link href="/styles/button.css" rel="stylesheet" type="text/css" />
    </head> 

    <body class="d-flex flex-column min-vh-100">
        <div class="h-100 text-white d-flex justify-content-center align-items-center" id="main">
            <?php
                $isLoggedIn = !empty($_SESSION["loggedin"]);
                $currentUsername = $_SESSION["username"] ?? "";
            ?>

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
                            <a class="dropdown-item" href="/<?php echo htmlspecialchars($currentUsername); ?>">
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
                            <a class="dropdown-item" href="/register">
                                Registrieren
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <div id="card" class="contact-card">

                <div id="header">
                    <h1 class="text-center">Kontakt</h1>
                    <p class="text-center text-light">Schreib uns eine Nachricht</p>
                </div>

                <div class="container mt-4">
                    <div class="card editor-card shadow">
                        <div class="card-body">

                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    Nachricht erfolgreich gesendet.
                                </div>
                            <?php endif; ?>

                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" action="/contactform">

                                <input type="text" name="website" style="display:none">

                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control"
                                        value="<?php echo htmlspecialchars($name); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">E-Mail</label>
                                    <input type="email" name="email" class="form-control"
                                        value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Betreff</label>
                                    <input type="text" name="subject" class="form-control"
                                        value="<?php echo htmlspecialchars($subject); ?>" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Nachricht</label>
                                    <textarea name="message" class="form-control" rows="5" required><?php echo htmlspecialchars($message); ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-light w-100">
                                    Nachricht senden
                                </button>

                            </form>
                        </div>
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
                    © <?php echo date("Y"); ?> Jannis Diemer
                </div>

            </div>
        </footer>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>