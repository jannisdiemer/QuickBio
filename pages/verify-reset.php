<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require __DIR__ . "/../mysql.php";

    $code = (isset($id) && is_string($id)) ? trim($id) : "";
    $error = "";
    $success = "";
    $validCode = false;

    if (!empty($code)) {
        $stmt = $mysql->prepare("SELECT * FROM accounts WHERE reset_code = :reset_code LIMIT 1");
        $stmt->bindParam(":reset_code", $code, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $validCode = true;
        } else {
            $error = "Invalid or expired reset link.";
        }
    } else {
        $error = "No reset code provided.";
    }

    if ($validCode && isset($_POST["submit"])) {
        $password = $_POST["password"] ?? "";
        $rpassword = $_POST["rpassword"] ?? "";

        if (empty($password) || empty($rpassword)) {
            $error = "Please fill in all fields.";
        } elseif ($password !== $rpassword) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 8) {
            $error = "Your password must be at least 8 characters long.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $mysql->prepare("
                UPDATE accounts
                SET PASSWORD = :password, reset_code = NULL
                WHERE reset_code = :reset_code
                LIMIT 1
            ");
            $stmt->bindParam(":password", $passwordHash, PDO::PARAM_STR);
            $stmt->bindParam(":reset_code", $code, PDO::PARAM_STR);

            if ($stmt->execute() && $stmt->rowCount() === 1) {
                $success = "Your password has been reset successfully.";
                $validCode = false;
            } else {
                $error = "Password could not be reset.";
            }
        }
    }
?>
<html>
    <head> 
    	<link rel="icon" type="image/png" href="/Images/quickbio_round.png"> 
        <meta charset="utf-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"> 
        <title>Reset Password | QuickBio</title> 
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link href="/styles/city.css" rel="stylesheet" type="text/css" /> 
        <link href="/styles/style.css" rel="stylesheet" type="text/css" /> 
        <link href="/styles/text-window.css" rel="stylesheet" type="text/css" /> 
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
								<img src="/Images/quickbio.png" id="profilepic" alt="Profilbild" />
							</div>
						</div>

						<h1 id="name">Reset Password</h1>
                        <h2 id="description">Choose a new password</h2>

                        <div class="container mt-4">
                            <div class="card editor-card shadow border-0" style="background-color: rgb(33, 40, 59); color: white;">
                                <div class="card-body">

                                    <?php if (!empty($error)) { ?>
                                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                    <?php } ?>

                                    <?php if (!empty($success)) { ?>
                                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                                        <a class="text-light" href="/login">Go to login</a>
                                    <?php } ?>

                                    <?php if ($validCode) { ?>
                                       <form method="post" action="/reset/<?php echo urlencode($code); ?>">
                                            <div class="mb-3">
                                                <label class="form-label text-light">New Password</label>
                                                <input 
                                                    type="password" 
                                                    class="form-control custom-input" 
                                                    name="password" 
                                                    placeholder="Enter new password">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label text-light">Repeat Password</label>
                                                <input 
                                                    type="password" 
                                                    class="form-control custom-input" 
                                                    name="rpassword" 
                                                    placeholder="Repeat password">
                                            </div>

                                            <button type="submit" name="submit" class="btn custom-login-btn w-100" style="color : white; border-radius: 20px; border-color: white;">
                                                Reset Password
                                            </button>
                                        </form>
                                    <?php } ?>
								</div>
							</div>
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
                    © <?php echo date("Y"); ?> <a href="/jannisdiemer">Jannis Diemer</a>
                </div>

            </div>
        </footer>
    </body>
</html>