<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} ?>

<html>
    <head> 
    	<link rel="icon" type="image/png" href="/Images/quickbio_round.png" style="border-radius:50%;"> 
        <meta charset="utf-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"> 
        <title>Terms of Service | QuickBio</title> 
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link href="/styles/city.css" rel="stylesheet" type="text/css" /> 
        <link href="/styles/style.css" rel="stylesheet" type="text/css" /> 
        <link href="/styles/text-window.css" rel="stylesheet" type="text/css" /> 
		<link href="/styles/button.css" rel="stylesheet" type="text/css" />
    </head> 
    <body style="overflow-y: auto;">
        <div class="text-white d-flex justify-content-center align-items-center" id="main">
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
                <h2 id="description">Terms of Service</h2>

                <div class="container mt-4">
                    <div class="card editor-card shadow">
                        <div class="card-body">
                            <div class="form-control editor-area">

								<h3>1. General Information</h3>
								<p>These Terms of Service govern the use of the website QuickBio (https://quickbio.net).
By using this website, you agree to these terms.
If you do not agree, please do not use the service.</p>
								
								<h3>2. Service Description</h3>
								<p>QuickBio provides users with the ability to create a personal profile page that can include links, descriptions, and media.
The service is provided free of charge unless stated otherwise.</p>

								<h3>3. User Accounts</h3>
								<p>To use certain features, you must create an account.

You agree to:
* Provide accurate information
* Keep your login credentials secure
* Be responsible for all activity under your account
We reserve the right to suspend or delete accounts at any time.</p>
								
								<h3>4. Acceptable Use</h3>
								<p>You agree **not to use QuickBio** for:
* Illegal activities
* Hate speech, harassment, or threats
* Pornographic or explicit content (if not allowed by platform rules)
* Scam, phishing, or misleading content
* Distribution of malware or harmful software
We reserve the right to remove content or accounts that violate these rules.</p>
								
								<h3>5. User Content</h3>
								<p>You retain ownership of the content you upload.
However, by using QuickBio, you grant us a non-exclusive right to:
* Store your content
* Display your profile publicly
You are fully responsible for your content.</p>
								
								<h3>6. Availability of Service</h3>
								<p>We do not guarantee that the service will be:
* Always available
* Error-free
* Secure at all times
We may modify or discontinue features at any time.</p>
								
								<h3>7. Account Termination</h3>
								<p>You may delete your account at any time.
We may suspend or delete accounts if:
* Terms are violated
* Illegal activity is detected
* Required by law</p>
								
								<h3>8. Limitation of Liability</h3>
								<p>QuickBio is provided **"as is"**.
We are not liable for:
* Data loss
* Downtime
* Damages caused by user content
* External links shared on profiles</p>
								
								<h3>9. External Links</h3>
								<p>Profiles may contain links to third-party websites.
We are not responsible for:
* Their content
* Their privacy practices
* Any damages resulting from their use</p>
								
								<h3>10. Data Protection</h3>
								<p>Please refer to our **Privacy Policy** for information on how we handle personal data.</p>
								
								<h3>11. Changes to Terms</h3>
								<p>We may update these Terms at any time.
Changes become effective upon publication on the website.</p>
								
								<h3>12. Governing Law</h3>
								<p>These Terms are governed by the laws of **Germany**.</p>
								
								<h3>13. Contact</h3>
								<p>If you have any questions, please contact:
									<a href="mailto:contact@quickbio.net">📧 [noreply@quickbio.net]</a></p>
								
								<p>Last updated: 19.03.2026</p>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
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