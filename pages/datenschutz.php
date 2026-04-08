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
        <title>Datenschutzerklärung | QuickBio</title> 
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
                <h2 id="description">Datenschutzerklärung</h2>

                <div class="container mt-4">
                    <div class="card editor-card shadow">
                        <div class="card-body">
                            <div class="form-control editor-area">

								<h3>1. Allgemeine Hinweise</h3>
								<p>Der Schutz Ihrer persönlichen Daten ist uns wichtig.
Diese Datenschutzerklärung informiert Sie darüber, welche Daten wir auf der Website **QuickBio (https://quickbio.net)** erheben und wie wir diese verwenden.</p>
								
								<h3>2. Verantwortlicher</h3>
								<p>Verantwortlich für die Datenverarbeitung:
QuickBio
Jannis Diemer
Im Engelsfeld 16
66399 Bliesmengen-Bolchen
Deutschland
									<a href="mailto:contact@quickbio.net">📧 [contact@quickbio.net]</a></p>

								<h3>3. Erhebung und Speicherung personenbezogener Daten</h3>
								<p>a) Beim Besuch der Website

Beim Aufrufen der Website werden automatisch Informationen erfasst:

* IP-Adresse
* Datum und Uhrzeit der Anfrage
* Browsertyp und Version
* Betriebssystem
* Referrer URL

Diese Daten dienen der technischen Bereitstellung und Sicherheit der Website.

---

 b) Bei Erstellung eines Accounts

Bei der Registrierung speichern wir:

* Benutzername
* E-Mail-Adresse
* Passwort (verschlüsselt gespeichert)

Diese Daten sind notwendig, um Ihr Konto zu erstellen und zu verwalten.

---

 c) Profilinformationen

Wenn Sie Ihr Profil bearbeiten, können folgende Daten gespeichert werden:

* Name
* Beschreibungstexte
* Links (z. B. Instagram, YouTube, etc.)
* Profilbild

Diese Informationen sind öffentlich sichtbar, wenn Ihr Profil aufgerufen wird.

---

 d) Upload von Profilbildern

Beim Hochladen eines Profilbildes wird dieses auf unseren Servern gespeichert.
Bitte laden Sie keine sensiblen oder urheberrechtlich geschützten Inhalte ohne Erlaubnis hoch.

---

 e) E-Mail-Kommunikation

Wir verwenden Ihre E-Mail-Adresse für:

* Account-Aktivierung
* Passwort-Reset
* Account-Löschung

Die E-Mails werden automatisiert versendet.</p>
								
								<h3>4. Zweck der Datenverarbeitung</h3>
								<p>Wir verarbeiten Ihre Daten ausschließlich für:

* Bereitstellung der Plattform
* Verwaltung von Benutzerkonten
* Sicherheit und Missbrauchsvermeidung
* Kommunikation mit Nutzern</p>
								
								<h3>5. Rechtsgrundlage</h3>
								<p>Die Verarbeitung erfolgt gemäß Art. 6 DSGVO:

* Art. 6 Abs. 1 lit. b DSGVO → Vertragserfüllung (Account, Nutzung der Plattform)
* Art. 6 Abs. 1 lit. f DSGVO → berechtigtes Interesse (Sicherheit, Stabilität)</p>
								
								<h3>6. Speicherung der Daten</h3>
								<p>Ihre Daten werden gespeichert, solange:

* Ihr Account besteht
* gesetzliche Aufbewahrungspflichten bestehen

Sie können Ihren Account jederzeit löschen.</p>
								
								<h3>7. Weitergabe von Daten</h3>
								<p>Ihre Daten werden **nicht an Dritte verkauft**.

Eine Weitergabe erfolgt nur, wenn:

* dies gesetzlich erforderlich ist
* dies zur technischen Bereitstellung notwendig ist (z. B. Hosting)</p>
								
								<h3>8. Ihre Rechte</h3>
								<p>Sie haben folgende Rechte:

* Auskunft über Ihre gespeicherten Daten
* Berichtigung falscher Daten
* Löschung Ihrer Daten
* Einschränkung der Verarbeitung
* Widerspruch gegen Verarbeitung
* Datenübertragbarkeit

									Kontaktieren Sie uns dazu unter: <a href="mailto:contact@quickbio.net">[contact@quickbio.net]</a></p>
								
								<h3>9. Sicherheit</h3>
								<p>Wir verwenden technische und organisatorische Maßnahmen, um Ihre Daten zu schützen.
Dennoch kann keine vollständige Sicherheit im Internet garantiert werden.</p>
								
								<h3>10. Änderungen dieser Datenschutzerklärung</h3>
								<p>Wir behalten uns vor, diese Datenschutzerklärung anzupassen.
Die jeweils aktuelle Version ist auf der Website verfügbar.</p>
								
								<h3>11. Kontakt</h3>
								<p>Bei Fragen zum Datenschutz: <a href="mailto:contact@quickbio.net">[contact@quickbio.net]</a></p>
								
								<p>Stand: 19.03.2026</p>
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