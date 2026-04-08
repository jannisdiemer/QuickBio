<?php 
	if (session_status() === PHP_SESSION_NONE) {
    	session_start();
	} 
?>

<html>
    <head> 
    	<link rel="icon" type="image/png" href="/Images/quickbio_round.png" style="border-radius:50%;"> 
        <meta charset="utf-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"> 
        <title>Impressum | QuickBio</title> 
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link href="/styles/city.css" rel="stylesheet" type="text/css" /> 
        <link href="/styles/style.css" rel="stylesheet" type="text/css" /> 
        <link href="/styles/text-window.css" rel="stylesheet" type="text/css" /> 
		<link href="/styles/button.css" rel="stylesheet" type="text/css" />
    </head> 
    <body>
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
							<a class="dropdown-item" href="/create">
								Registrieren
							</a>
						</li>
					<?php } ?>
				</ul>
			</div>
			
            <div id="card">
                <div id="header">
					 <div id="pfp-wrap" aria-label="Profilbild bearbeiten">
                    	<img src="/Images/quickbio.png" id="profilepic" alt="Profilbild" />
					</div>
                </div>

                <h1 id="name">QuickBio</h1>
                <h2 id="description">Impressum</h2>

                <div class="container mt-4">
                    <div class="card editor-card shadow">
                        <div class="card-body">
                            <div class="form-control editor-area">
								Verantwortlich für den Inhalt nach §18 Abs. 2 MStV:<br>
                                Name: 		Jannis Diemer<br>
								Adresse: 	Im Engelsfeld 16<br>
											66399 Bliesmengen-Bolchen<br>
											Deutschland<br>
								E-Mail:		jannis.diemer@quickbio.net<br>
								Telefon: 	+49 176 60152780<br>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <div id="links">
                   <div id="container">
						<!-- Instagram -->
                        <a class="social-btn instagram" href="https://www.instagram.com/overrash.media.group" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" class="social-icon">
                            <path fill="currentColor" d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm10 2H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3zm-5 4.5a5.5 5.5 0 1 1 0 11a5.5 5.5 0 0 1 0-11zm0 2a3.5 3.5 0 1 0 0 7a3.5 3.5 0 0 0 0-7zM18 6.8a1.2 1.2 0 1 1 0 2.4a1.2 1.2 0 0 1 0-2.4z"/>
                        </svg>
                        </a>

                        <!-- X -->
                        <a class="social-btn x" href="https://overrash-media-group.net" aria-label="X">
                       		<img src="/Images/overrash.png" class="social-icon" alt="Custom">
                        </a>

                        <!-- Mail -->
                        <a class="social-btn mail" href="mailto:support@overrash-media-group.net" aria-label="Mail">
                        <svg viewBox="0 0 24 24" class="social-icon">
                            <path fill="currentColor" d="M2 4h20v16H2V4zm10 7L4 6v12h16V6l-8 5z"/>
                        </svg>
                        </a>

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