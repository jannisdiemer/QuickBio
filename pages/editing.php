<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }

    if (empty($_SESSION["loggedin"]) || empty($_SESSION["username"])) {
        header("Location: /login");
        exit;
    }

    require __DIR__ . "/../mysql.php";

    $id = $_SESSION["username"];

    $stmt = $mysql->prepare("SELECT * FROM accounts WHERE ID = :id AND activated = 1 LIMIT 1");
    $stmt->bindParam(":id", $id, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: /");
        exit;
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    function e($value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    function clean_username(string $value, string $pattern): string {
        return preg_replace($pattern, '', trim($value));
    }

    function clean_url(string $value): string {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return '';
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return '';
        }

        return $value;
    }

    $allowedDesigns = ['city.css', 'custom.css', 'minimal-light.css'];

    $vorname = $user["Vorname"] ?? "";
    $nachname = $user["Nachname"] ?? "";
    $email = $user["Email"] ?? "";
    $design = $user["Design"] ?? "city.css";
    $profilepic = $user["Profilepicture"] ?? "default.png";
    $instagram = $user["Instagram"] ?? "";
    $twitter = $user["Twitter"] ?? "";
    $reddit = $user["Reddit"] ?? "";
    $github = $user["Github"] ?? "";
    $onlyfans = $user["Onlyfans"] ?? "";
    $youtube = $user["YouTube"] ?? "";
    $linkedin = $user["Linkedin"] ?? "";
    $discord = $user["Discord"] ?? "";
    $snapchat = $user["Snapchat"] ?? "";
    $facebook = $user["Facebook"] ?? "";
    $whatsapp = $user["WhatsApp"] ?? "";
    $tiktok = $user["TikTok"] ?? "";
    $pinterest = $user["Pinterest"] ?? "";
    $telegram = $user["Telegram"] ?? "";
    $other = $user["Other"] ?? "";
    $description = $user["Informations"] ?? "";
    $shortintroduction = $user["ShortIntroduction"] ?? "";

    if (!in_array($design, $allowedDesigns, true)) {
        $design = 'city.css';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["submit"])) {
        $submittedToken = $_POST['csrf_token'] ?? '';

        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            http_response_code(403);
            exit('Ungültige Anfrage.');
        }

        $vorname = trim($_POST["vorname"] ?? "");
        $nachname = trim($_POST["nachname"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $design = trim($_POST["design"] ?? "city.css");
        $instagram = clean_username($_POST["instagram"] ?? "", '/[^a-zA-Z0-9._]/');
        $twitter = clean_username($_POST["twitter"] ?? "", '/[^a-zA-Z0-9_]/');
        $reddit = clean_username($_POST["reddit"] ?? "", '/[^a-zA-Z0-9_-]/');
        $github = clean_username($_POST["github"] ?? "", '/[^a-zA-Z0-9-]/');
        $onlyfans = clean_username($_POST["onlyfans"] ?? "", '/[^a-zA-Z0-9._-]/');
        $youtube = clean_username($_POST["youtube"] ?? "", '/[^a-zA-Z0-9_-]/');
        $linkedin = clean_username($_POST["linkedin"] ?? "", '/[^a-zA-Z0-9-]/');
        $discord = clean_url($_POST["discord"] ?? "");
        $snapchat = clean_username($_POST["snapchat"] ?? "", '/[^a-zA-Z0-9._-]/');
        $facebook = clean_username($_POST["facebook"] ?? "", '/[^a-zA-Z0-9.]/');
        $whatsapp = preg_replace('/\D+/', '', $_POST["whatsapp"] ?? "");
        $tiktok = clean_username($_POST["tiktok"] ?? "", '/[^a-zA-Z0-9._]/');
        $pinterest = clean_username($_POST["pinterest"] ?? "", '/[^a-zA-Z0-9._-]/');
        $telegram = clean_username($_POST["telegram"] ?? "", '/[^a-zA-Z0-9_]/');
        $other = clean_url($_POST["other"] ?? "");
        $description = trim($_POST["informations"] ?? "");
        $shortintroduction = trim($_POST["shortintroduction"] ?? "");

        if (!in_array($design, $allowedDesigns, true)) {
            $design = 'city.css';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            exit('Ungültige E-Mail-Adresse.');
        }

        if (mb_strlen($vorname) > 100 || mb_strlen($nachname) > 100 || mb_strlen($shortintroduction) > 160) {
            exit('Ein oder mehrere Felder sind zu lang.');
        }

        if (mb_strlen($description) > 5000) {
            exit('Die Beschreibung ist zu lang.');
        }

        $currentProfilePic = $user["Profilepicture"] ?? "default.png";
        $profilepic = $currentProfilePic;

        if (isset($_FILES["profilepicture"]) && $_FILES["profilepicture"]["error"] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . "/../Profilepics/";

            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                exit("Upload-Ordner konnte nicht erstellt werden.");
            }

            $tmpName = $_FILES["profilepicture"]["tmp_name"];
            $originalName = $_FILES["profilepicture"]["name"] ?? '';
            $fileSize = $_FILES["profilepicture"]["size"] ?? 0;

            $imageInfo = @getimagesize($tmpName);
            if ($imageInfo === false) {
                exit("Die hochgeladene Datei ist kein gültiges Bild.");
            }

            if ($fileSize > 5 * 1024 * 1024) {
                exit("Das Bild ist zu groß. Maximal 5 MB.");
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tmpName);
            finfo_close($finfo);

            $allowedMimeTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            if (!isset($allowedMimeTypes[$mimeType])) {
                exit("Nur JPG, PNG und WEBP sind erlaubt.");
            }

            $extension = $allowedMimeTypes[$mimeType];
            $newFilename = $id . "_" . bin2hex(random_bytes(8)) . "." . $extension;
            $destination = $uploadDir . $newFilename;

            if (!move_uploaded_file($tmpName, $destination)) {
                exit("Das neue Profilbild konnte nicht gespeichert werden.");
            }

            if (!empty($currentProfilePic) && $currentProfilePic !== "default.png") {
                $oldFile = $uploadDir . basename($currentProfilePic);
                if (is_file($oldFile)) {
                    unlink($oldFile);
                }
            }

            $profilepic = $newFilename;
        }

        $stmt = $mysql->prepare("
            UPDATE accounts
            SET
                Vorname = :vorname,
                Nachname = :nachname,
                Email = :email,
                Design = :design,
                Profilepicture = :profilepicture,
                Instagram = :instagram,
                Twitter = :twitter,
                Reddit = :reddit,
                Github = :github,
                Onlyfans = :onlyfans,
                YouTube = :youtube,
                Linkedin = :linkedin,
                Discord = :discord,
                Snapchat = :snapchat,
                Facebook = :facebook,
                WhatsApp = :whatsapp,
                TikTok = :tiktok,
                Pinterest = :pinterest,
                Telegram = :telegram,
                Other = :other,
                Informations = :informations,
                ShortIntroduction = :shortintroduction
            WHERE ID = :id
        ");

        $stmt->bindParam(":id", $id, PDO::PARAM_STR);
        $stmt->bindParam(":vorname", $vorname, PDO::PARAM_STR);
        $stmt->bindParam(":nachname", $nachname, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":design", $design, PDO::PARAM_STR);
        $stmt->bindParam(":profilepicture", $profilepic, PDO::PARAM_STR);
        $stmt->bindParam(":instagram", $instagram, PDO::PARAM_STR);
        $stmt->bindParam(":twitter", $twitter, PDO::PARAM_STR);
        $stmt->bindParam(":reddit", $reddit, PDO::PARAM_STR);
        $stmt->bindParam(":github", $github, PDO::PARAM_STR);
        $stmt->bindParam(":onlyfans", $onlyfans, PDO::PARAM_STR);
        $stmt->bindParam(":youtube", $youtube, PDO::PARAM_STR);
        $stmt->bindParam(":linkedin", $linkedin, PDO::PARAM_STR);
        $stmt->bindParam(":discord", $discord, PDO::PARAM_STR);
        $stmt->bindParam(":snapchat", $snapchat, PDO::PARAM_STR);
        $stmt->bindParam(":facebook", $facebook, PDO::PARAM_STR);
        $stmt->bindParam(":whatsapp", $whatsapp, PDO::PARAM_STR);
        $stmt->bindParam(":tiktok", $tiktok, PDO::PARAM_STR);
        $stmt->bindParam(":pinterest", $pinterest, PDO::PARAM_STR);
        $stmt->bindParam(":telegram", $telegram, PDO::PARAM_STR);
        $stmt->bindParam(":other", $other, PDO::PARAM_STR);
        $stmt->bindParam(":informations", $description, PDO::PARAM_STR);
        $stmt->bindParam(":shortintroduction", $shortintroduction, PDO::PARAM_STR);

        if ($stmt->execute()) {
            header("Location: /editing?saved=1");
            exit;
        }

        error_log("Editing update failed: " . print_r($stmt->errorInfo(), true));
        exit('Beim Speichern ist ein Fehler aufgetreten.');
    }

    $safeDesign = basename($design);
    $safeProfilePic = basename($profilepic ?: 'default.png');
    $fullName = trim($vorname . ' ' . $nachname);
    if ($fullName === '') {
        $fullName = $id;
    }
?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <title>Editing | <?php echo e($id); ?></title>

        <link id="theme-stylesheet" href="/styles/<?php echo e($safeDesign); ?>" rel="stylesheet" type="text/css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link href="/styles/<?php echo e($safeDesign); ?>" rel="stylesheet" type="text/css">
        <link href="/styles/style.css" rel="stylesheet" type="text/css">
        <link href="/styles/text-window.css" rel="stylesheet" type="text/css">
        <link href="/styles/button.css" rel="stylesheet" type="text/css">
        <link href="/styles/editing.css" rel="stylesheet" type="text/css">
    </head>

    <body>
        <form method="post" action="/editing" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="design" id="design-input" value="<?php echo e($safeDesign); ?>">

            <div class="container-fluid vh-100">
                <div class="row h-100">
                    <div class="col-md-3 h-100 bg-dark text-white">
                        <div class="h-100 d-flex flex-column p-3 overflow-auto">
                            <h4 class="mb-3">Profil</h4>

                            <div class="mb-3">
                                <label class="form-label">Vorname</label>
                                <input type="text" name="vorname" id="input-vorname" class="form-control custom-input" value="<?php echo e($vorname); ?>" maxlength="100">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nachname</label>
                                <input type="text" name="nachname" id="input-nachname" class="form-control custom-input" value="<?php echo e($nachname); ?>" maxlength="100">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kurzbeschreibung</label>
                                <input type="text" name="shortintroduction" id="input-shortintroduction" class="form-control custom-input" value="<?php echo e($shortintroduction); ?>" maxlength="160">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Beschreibung</label>
                                <textarea name="informations" id="input-informations" class="form-control custom-input" rows="5"><?php echo e($description); ?></textarea>
                            </div>

                            <h4 class="mb-3 mt-2">Links</h4>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="input-email" class="form-control custom-input" value="<?php echo e($email); ?>" placeholder="your@email.com">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Instagram</label>
                                <input type="text" name="instagram" id="input-instagram" class="form-control custom-input" value="<?php echo e($instagram); ?>" placeholder="username">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">YouTube</label>
                                <input type="text" name="youtube" id="input-youtube" class="form-control custom-input" value="<?php echo e($youtube); ?>" placeholder="channel id">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">X / Twitter</label>
                                <input type="text" name="twitter" id="input-twitter" class="form-control custom-input" value="<?php echo e($twitter); ?>" placeholder="username">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">LinkedIn</label>
                                <input type="text" name="linkedin" id="input-linkedin" class="form-control custom-input" value="<?php echo e($linkedin); ?>" placeholder="username">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reddit</label>
                                <input type="text" name="reddit" id="input-reddit" class="form-control custom-input" value="<?php echo e($reddit); ?>" placeholder="username">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">GitHub</label>
                                <input type="text" name="github" id="input-github" class="form-control custom-input" value="<?php echo e($github); ?>" placeholder="username">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">OnlyFans</label>
                                <input type="text" name="onlyfans" id="input-onlyfans" class="form-control custom-input" value="<?php echo e($onlyfans); ?>" placeholder="username">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Discord Link</label>
                                <input type="url" name="discord" id="input-discord" class="form-control custom-input" value="<?php echo e($discord); ?>" placeholder="https://discord.gg/...">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Snapchat</label>
                                <input type="text" name="snapchat" id="input-snapchat" class="form-control custom-input" value="<?php echo e($snapchat); ?>" placeholder="username">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Facebook</label>
                                <input type="text" name="facebook" id="input-facebook" class="form-control custom-input" value="<?php echo e($facebook); ?>" placeholder="username">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">WhatsApp</label>
                                <input type="text" name="whatsapp" id="input-whatsapp" class="form-control custom-input" value="<?php echo e($whatsapp); ?>" placeholder="49123456789">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">TikTok</label>
                                <input type="text" name="tiktok" id="input-tiktok" class="form-control custom-input" value="<?php echo e($tiktok); ?>" placeholder="username">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Pinterest</label>
                                <input type="text" name="pinterest" id="input-pinterest" class="form-control custom-input" value="<?php echo e($pinterest); ?>" placeholder="username">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Telegram</label>
                                <input type="text" name="telegram" id="input-telegram" class="form-control custom-input" value="<?php echo e($telegram); ?>" placeholder="username">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Other Link</label>
                                <input type="url" name="other" id="input-other" class="form-control custom-input" value="<?php echo e($other); ?>" placeholder="https://...">
                            </div>

                            <div class="pt-3 pb-2">
                                <button type="submit" name="submit" class="btn btn-success w-100">
                                    <i class="bi bi-save"></i> Save
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 h-100 text-white d-flex justify-content-center align-items-center position-relative" id="main">
                        <div class="share-btn-wrapper">
                            <button class="share-btn" type="button" onclick="shareProfile()" aria-label="Profil teilen">
                                <svg viewBox="0 0 24 24" class="share-icon">
                                    <path fill="currentColor" d="M18 16a3 3 0 0 0-2.4 1.2l-6.6-3.3a3.1 3.1 0 0 0 0-1.8l6.6-3.3A3 3 0 1 0 15 7a3.1 3.1 0 0 0 .1.8L8.5 11a3 3 0 1 0 0 2l6.6 3.2A3 3 0 1 0 18 16z"/>
                                </svg>
                            </button>
                        </div>

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
                                <li>
                                    <a class="dropdown-item" href="/<?php echo e($id); ?>">Mein Profil</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/editing">Profil bearbeiten</a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="/logout">Logout</a>
                                </li>
                            </ul>
                        </div>

                        <div id="card">
                            <div id="header">
                                <div id="pfp-wrap" aria-label="Profilbild bearbeiten" style="cursor:pointer;" onclick="document.getElementById('profilepic-upload').click();">
                                    <img src="/Profilepics/<?php echo e($safeProfilePic); ?>" id="profilepic" alt="Profilbild">
                                    <div id="pfp-edit" aria-hidden="true">
                                        <i class="bi bi-pencil"></i>
                                    </div>
                                </div>

                                <input
                                    type="file"
                                    id="profilepic-upload"
                                    name="profilepicture"
                                    accept="image/png,image/jpeg,image/jpg,image/webp"
                                    style="display:none;"
                                    onchange="previewProfilePicture(event)"
                                >
                            </div>

                            <h1 id="preview-name"><?php echo e($fullName); ?></h1>
                            <h2 id="preview-shortintro"><?php echo e($shortintroduction); ?></h2>

                            <div class="container mt-4">
                                <div class="card editor-card shadow">
                                    <div class="card-body">
                                        <div class="form-control editor-area" id="preview-description"><?php echo e($description); ?></div>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <div id="links">
                                <div id="container">
                                    <a class="social-btn instagram" id="preview-instagram" aria-label="Instagram" <?php if (empty($instagram)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm10 2H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3zm-5 4.5a5.5 5.5 0 1 1 0 11a5.5 5.5 0 0 1 0-11zm0 2a3.5 3.5 0 1 0 0 7a3.5 3.5 0 0 0 0-7zM18 6.8a1.2 1.2 0 1 1 0 2.4a1.2 1.2 0 0 1 0-2.4z"/></svg>
                                    </a>

                                    <a class="social-btn youtube" id="preview-youtube" aria-label="YouTube" <?php if (empty($youtube)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.7 3.5 12 3.5 12 3.5s-7.7 0-9.4.6A3 3 0 0 0 .5 6.2 31.7 31.7 0 0 0 0 12a31.7 31.7 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.7.6 9.4.6 9.4.6s7.7 0 9.4-.6a3 3 0 0 0 2.1-2.1A31.7 31.7 0 0 0 24 12a31.7 31.7 0 0 0-.5-5.8zM9.8 15.5V8.5l6.2 3.5-6.2 3.5z"/></svg>
                                    </a>

                                    <a class="social-btn x" id="preview-twitter" aria-label="X" <?php if (empty($twitter)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M18 2h3l-7.5 8.6L22 22h-6.8l-5.3-7L3.8 22H.8l8-9.2L2 2h7l4.8 6.3L18 2z"/></svg>
                                    </a>

                                    <a class="social-btn linkedin" id="preview-linkedin" aria-label="LinkedIn" <?php if (empty($linkedin)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M4.98 3.5A2.5 2.5 0 1 1 5 8.5a2.5 2.5 0 0 1-.02-5zM3 9h4v12H3zM10 9h3.6v1.7h.1c.5-.9 1.8-1.9 3.7-1.9 4 0 4.7 2.6 4.7 6v7.2h-4V16c0-1.4 0-3.3-2-3.3s-2.3 1.6-2.3 3.2v6.1H10z"/></svg>
                                    </a>

                                    <a class="social-btn mail" id="preview-email" aria-label="Mail" <?php if (empty($email)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M2 4h20v16H2V4zm10 7L4 6v12h16V6l-8 5z"/></svg>
                                    </a>

                                    <a class="social-btn reddit" id="preview-reddit" aria-label="Reddit" <?php if (empty($reddit)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M14.2 15.3c.1.1.1.3 0 .4-.5.5-1.3.7-2.2.7-.9 0-1.7-.2-2.2-.7-.1-.1-.1-.3 0-.4.1-.1.3-.1.4 0 .4.3 1 .5 1.8.5s1.4-.2 1.8-.5c.1-.1.3-.1.4 0zM9.5 13.2a1 1 0 1 1-2 0a1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0a1 1 0 0 1 2 0zm5.5-1.2c0-1.3-.9-2.4-2.1-2.7.1-.3.1-.6.1-.9 0-1.5-1.2-2.7-2.7-2.7-1.1 0-2 .6-2.5 1.5-1.5-.9-3.3-1.4-5.3-1.5l1.1-3.4 2.9.7a2 2 0 1 0 .2-1l-3.3-.8c-.3-.1-.6.1-.7.4L8.3 5.1c-2 .1-3.9.6-5.4 1.5A2.7 2.7 0 0 0 .4 8.2c0 .3 0 .6.1.9A2.8 2.8 0 0 0 0 12c0 1.4 1 2.6 2.3 2.8C3 18 7.1 20.5 12 20.5S21 18 21.7 14.8c1.3-.2 2.3-1.4 2.3-2.8z"/></svg>
                                    </a>

                                    <a class="social-btn github" id="preview-github" aria-label="GitHub" <?php if (empty($github)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M12 .5a12 12 0 0 0-3.8 23.4c.6.1.8-.2.8-.6v-2.1c-3.3.7-4-1.4-4-1.4-.6-1.4-1.3-1.8-1.3-1.8-1.1-.7.1-.7.1-.7 1.2.1 1.9 1.3 1.9 1.3 1.1 1.8 2.8 1.3 3.5 1 .1-.8.4-1.3.7-1.6-2.7-.3-5.5-1.3-5.5-6A4.7 4.7 0 0 1 6.2 8c-.1-.3-.5-1.5.1-3.1 0 0 1-.3 3.2 1.2A11 11 0 0 1 12 5.8c.8 0 1.7.1 2.5.3 2.2-1.5 3.2-1.2 3.2-1.2.6 1.6.2 2.8.1 3.1a4.7 4.7 0 0 1 1.3 3.3c0 4.7-2.8 5.7-5.5 6 .4.3.8 1 .8 2v3c0 .4.2.7.8.6A12 12 0 0 0 12 .5z"/></svg>
                                    </a>

                                    <a class="social-btn onlyfans" id="preview-onlyfans" aria-label="OnlyFans" <?php if (empty($onlyfans)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M18.2 7.3c-1.2-.8-2.8-1.2-4.7-1.2H6.8C4.2 6.1 2 8.3 2 10.9s2.2 4.8 4.8 4.8h4.5l-1.2 2.2h3.4l1.2-2.2h.8c3.7 0 6.5-2.1 6.5-5.1 0-1.5-.8-2.7-2.2-3.3zm-2.8 5.1h-5.7l1.6-3h2.9c1.3 0 2 .5 2 1.4 0 1-.8 1.6-2.1 1.6z"/></svg>
                                    </a>

                                    <a class="social-btn discord" id="preview-discord" aria-label="Discord" <?php if (empty($discord)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M20.3 4.4A16.7 16.7 0 0 0 16.2 3l-.2.4c1.6.4 2.3 1 2.9 1.5-2.5-1.1-5.1-1.1-7.8 0 .6-.5 1.5-1.1 2.9-1.5L13.8 3a16.7 16.7 0 0 0-4.1 1.4C7.1 8.3 6.4 12 6.6 15.6a16.9 16.9 0 0 0 5 2.5l.6-1c-1-.3-1.9-.8-2.7-1.4l.2.1c1.6.7 3.4 1 5.1 1 1.7 0 3.5-.3 5.1-1l.2-.1c-.8.6-1.7 1.1-2.7 1.4l.6 1a16.9 16.9 0 0 0 5-2.5c.3-4.1-.5-7.8-2.7-11.2zM10 13.5c-.8 0-1.4-.7-1.4-1.6s.6-1.6 1.4-1.6c.8 0 1.4.7 1.4 1.6 0 .9-.6 1.6-1.4 1.6zm4 0c-.8 0-1.4-.7-1.4-1.6s.6-1.6 1.4-1.6c.8 0 1.4.7 1.4 1.6 0 .9-.6 1.6-1.4 1.6z"/></svg>
                                    </a>

                                    <a class="social-btn snapchat" id="preview-snapchat" aria-label="Snapchat" <?php if (empty($snapchat)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M12 2.2c2.8 0 5 2.2 5 5v2.2c0 .4.2.8.6 1l1 .6c.5.3.5 1 0 1.3l-1.3.7c-.2.1-.3.3-.2.5.2.8.8 1.5 1.7 1.7.3.1.4.5.2.7-.7.7-1.8 1.2-3 1.3-.1 0-.2.1-.3.2-.5 1.1-1.7 1.8-3.7 1.8s-3.2-.7-3.7-1.8c0-.1-.1-.2-.3-.2-1.2-.1-2.3-.6-3-1.3-.2-.2-.1-.6.2-.7.9-.2 1.5-.9 1.7-1.7.1-.2 0-.4-.2-.5l-1.3-.7c-.5-.3-.5-1 0-1.3l1-.6c.4-.2.6-.6.6-1V7.2c0-2.8 2.2-5 5-5z"/></svg>
                                    </a>

                                    <a class="social-btn facebook" id="preview-facebook" aria-label="Facebook" <?php if (empty($facebook)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M13.5 22v-8h2.7l.4-3h-3.1V9.1c0-.9.3-1.5 1.6-1.5h1.7V5c-.3 0-1.4-.1-2.6-.1-2.6 0-4.3 1.5-4.3 4.4V11H7v3h2.9v8h3.6z"/></svg>
                                    </a>

                                    <a class="social-btn whatsapp" id="preview-whatsapp" aria-label="WhatsApp" <?php if (empty($whatsapp)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M20.5 3.5A11.8 11.8 0 0 0 1.9 17.6L.5 23.5l6-1.4a11.8 11.8 0 0 0 5.5 1.4h.1A11.9 11.9 0 0 0 20.5 3.5zm-8.4 18c-1.8 0-3.6-.5-5.1-1.4l-.4-.2-3.6.8.8-3.5-.2-.4a9.8 9.8 0 1 1 8.5 4.7zm5.4-7.3c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.2-.7.2s-.8 1-.9 1.1c-.2.2-.3.2-.6.1-1.7-.8-2.8-1.5-4-3.5-.3-.5.3-.5.8-1.7.1-.2.1-.4 0-.6l-.9-2.2c-.2-.5-.5-.4-.7-.4h-.6c-.2 0-.6.1-.9.4-.3.3-1.1 1.1-1.1 2.7s1.1 3.1 1.2 3.4c.2.2 2.2 3.4 5.3 4.7.7.3 1.3.5 1.8.7.8.2 1.5.2 2 .1.6-.1 1.8-.8 2-1.6.3-.8.3-1.5.2-1.6-.1-.1-.3-.2-.6-.4z"/></svg>
                                    </a>

                                    <a class="social-btn tiktok" id="preview-tiktok" aria-label="TikTok" <?php if (empty($tiktok)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M16.6 3c.5 2.3 1.8 3.7 4 4v3.1c-1.5.1-2.8-.3-4-.9v6.1c0 3.1-2.5 5.7-5.7 5.7S5.3 18.4 5.3 15.3s2.5-5.7 5.7-5.7c.2 0 .4 0 .6.1v3.2a2.6 2.6 0 0 0-.6-.1 2.5 2.5 0 1 0 2.5 2.5V3h3.1z"/></svg>
                                    </a>

                                    <a class="social-btn pinterest" id="preview-pinterest" aria-label="Pinterest" <?php if (empty($pinterest)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M12 2a10 10 0 0 0-3.6 19.3c0-.8 0-2 .3-2.9l1.5-6.2s-.4-.8-.4-2c0-1.8 1.1-3.2 2.4-3.2 1.1 0 1.7.8 1.7 1.8 0 1.1-.7 2.8-1 4.4-.3 1.3.7 2.3 2 2.3 2.4 0 4.3-2.5 4.3-6.2 0-3.2-2.3-5.5-5.6-5.5-3.8 0-6.1 2.9-6.1 5.9 0 1.2.5 2.5 1 3.2.1.1.1.3.1.5l-.4 1.5c-.1.2-.2.3-.5.2-1.7-.7-2.8-2.8-2.8-4.5 0-3.7 2.7-7.1 7.8-7.1 4.1 0 7.2 2.9 7.2 6.8 0 4.1-2.6 7.4-6.1 7.4-1.2 0-2.3-.6-2.7-1.4l-.8 3c-.3 1-.9 2.2-1.4 3A10 10 0 1 0 12 2z"/></svg>
                                    </a>

                                    <a class="social-btn telegram" id="preview-telegram" aria-label="Telegram" <?php if (empty($telegram)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M21.9 4.6c.3-1.1-.4-1.6-1.4-1.3L2.7 10.1c-1.2.5-1.2 1.2-.2 1.5l4.6 1.4 10.6-6.7c.5-.3 1-.1.6.2l-8.6 7.8-.3 4.5c.4 0 .6-.2.9-.4l2.2-2.1 4.5 3.3c.8.4 1.4.2 1.6-.8l3.3-15.2z"/></svg>
                                    </a>

                                    <a class="social-btn other" id="preview-other" aria-label="Other" <?php if (empty($other)) echo 'style="display:none;"'; ?>>
                                        <svg viewBox="0 0 24 24" class="social-icon"><path fill="currentColor" d="M10.6 13.4a1 1 0 0 1 0-1.4l3.4-3.4a3 3 0 1 1 4.2 4.2l-1.6 1.6a1 1 0 1 1-1.4-1.4l1.6-1.6a1 1 0 1 0-1.4-1.4L12 13.4a1 1 0 0 1-1.4 0zm2.8-2.8a1 1 0 0 1 0 1.4L10 15.4a3 3 0 0 1-4.2-4.2l1.6-1.6a1 1 0 0 1 1.4 1.4l-1.6 1.6a1 1 0 1 0 1.4 1.4l3.4-3.4a1 1 0 0 1 1.4 0z"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 h-100 bg-dark text-white d-flex justify-content-center align-items-center">
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-md-11">

                                    <div class="rounded-4 shadow overflow-hidden mt-3 design-option <?php echo ($design === 'custom.css') ? 'design-selected' : ''; ?>"
                                        style="background-color: rgb(33, 40, 59); cursor:pointer;"
                                        data-design="custom.css">
                                        <div class="row g-0">
                                            <div class="col-6">
                                                <img src="Images/custom.png" class="w-100 h-100" style="object-fit: cover;" alt="Custom Design">
                                            </div>
                                            <div class="col-6 d-flex align-items-center justify-content-center text-white">
                                                <h4>Custom</h4>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-4 shadow overflow-hidden mt-3 design-option <?php echo ($design === 'city.css') ? 'design-selected' : ''; ?>"
                                        style="background-color: rgb(33, 40, 59); cursor:pointer;"
                                        data-design="city.css">
                                        <div class="row g-0">
                                            <div class="col-6">
                                                <img src="Images/city.png" class="w-100 h-100" style="object-fit: cover;" alt="City Design">
                                            </div>
                                            <div class="col-6 d-flex align-items-center justify-content-center text-white">
                                                <h4>City</h4>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-4 shadow overflow-hidden mt-3 design-option <?php echo ($design === 'minimal-light.css') ? 'design-selected' : ''; ?>"
                                        style="background-color: rgb(33, 40, 59); cursor:pointer;"
                                        data-design="minimal-light.css">
                                        <div class="row g-0">
                                            <div class="col-6">
                                                <img src="Images/minimal-light.png" class="w-100 h-100" style="object-fit: cover;" alt="Minimal Light Design">
                                            </div>
                                            <div class="col-6 d-flex align-items-center justify-content-center text-white">
                                                <h4>Minimal Light</h4>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (isset($_GET['saved'])) { ?>
                                        <div class="alert alert-success mt-4 mb-0">
                                            Änderungen gespeichert.
                                        </div>
                                    <?php } ?>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
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
        <script src="/scripts/profilepic.js"></script>
    </body>
</html>