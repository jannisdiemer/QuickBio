<?php
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
		header("Location: /editing");
		exit;
	}

	$error = "none";

	if(isset($_POST["submit"])){
		require __DIR__ . "/../mysql.php";
		$stmt = $mysql->prepare("SELECT * FROM accounts WHERE ID = :id AND activated = 1 LIMIT 1");
	$stmt->bindParam(":id", $_POST["username"]);
	$stmt->execute();
		$row = $stmt->fetch();

		if($row){
			if(password_verify($_POST["password"], $row["PASSWORD"])){
				$_SESSION["loggedin"] = true;
				$_SESSION["username"] = $row["ID"];
				header("Location: /" . urlencode($_SESSION["username"]));
				exit;
			} else {
				$error = "Wrong password";
			}
		} else {
			$error = "No such active username";
		}
	}
?>

<html>
    <head> 
    	<link rel="icon" type="image/png" href="/Images/quickbio_round.png" style="border-radius:50%;"> 
        <meta charset="utf-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"> 
        <title>Login | QuickBio</title> 
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link href="/styles/city.css" rel="stylesheet" type="text/css" /> 
        <link href="/styles/style.css" rel="stylesheet" type="text/css" /> 
        <link href="/styles/text-window.css" rel="stylesheet" type="text/css" /> 
		<link href="/styles/button.css" rel="stylesheet" type="text/css" />
    </head> 
    <body>
        <div class="container-fluid vh-100">
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

						<h1 id="name">Login</h1>
						<h2 id="description"><?php if($error == "none") { ?>Access your profile<?php } else { echo $error;}?></h2>

						<div class="container mt-4">
							<div class="card editor-card shadow border-0" style="background-color: rgb(33, 40, 59); color: white;">
								<div class="card-body">
									<form method="post" action="/login">

										<div class="mb-3">
											<label class="form-label text-light">Username</label>
											<input 
												type="text" 
												class="form-control custom-input" 
												name="username" 
												placeholder="Your username">
										</div>

										<div class="mb-3">
											<label class="form-label text-light">Password</label>
											<input 
												type="password" 
												class="form-control custom-input" 
												name="password" 
												placeholder="Enter password">
										</div>

										<a class="text-light" href="/create">Create a new account</a><br><br>

										<button type="submit" name="submit" class="btn custom-login-btn w-100" style="color : white; border-radius: 20px; border-color: white;">
											Login
										</button>
									</form>
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

		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>