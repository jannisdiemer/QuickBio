<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require __DIR__ . "/../mysql.php";

    if (!isset($id) || !is_string($id) || $id === '') {
        header("Location: /");
        exit;
    }

    $stmt = $mysql->prepare("SELECT * FROM accounts WHERE ID = :id AND activated = 1 LIMIT 1");
    $stmt->bindParam(":id", $id, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: /");
        exit;
    }

    $vorname = $user["Vorname"] ?? "";
    $nachname = $user["Nachname"] ?? "";
    $email = $user["Email"] ?? "";
    $design = $user["Design"] ?? "city.css";
    $profilepicture = $user["Profilepicture"] ?? "default.png";

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

    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }

    $fullName = trim($vorname . ' ' . $nachname);
    if ($fullName === '') {
        $fullName = $id;
    }

    $metaDescription = trim($shortintroduction);
    if ($metaDescription === '') {
        $metaDescription = trim($description);
    }
    if ($metaDescription === '') {
        $metaDescription = "Schau dir das Profil von " . $fullName . " auf QuickBio an.";
    }
    $metaDescription = mb_substr($metaDescription, 0, 180);

    $baseUrl = 'https://quickbio.net';
    $profileUrl = $baseUrl . '/' . rawurlencode($id);

    $safeProfilePicture = basename((string)$profilepicture);
    $imageUrl = $baseUrl . '/Profilepics/' . rawurlencode($safeProfilePicture);

    $imageFilePath = $_SERVER['DOCUMENT_ROOT'] . '/Profilepics/' . $safeProfilePicture;
    if (is_file($imageFilePath)) {
        $imageUrl .= '?v=' . filemtime($imageFilePath);
    }

    $safeDesign = basename((string)$design);
    if ($safeDesign === '') {
        $safeDesign = 'city.css';
    }
?>

<html>
    <head>
        <link rel="icon" type="image/png" href="/Profilepics/<?php echo e($safeProfilePicture); ?>">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

        <title><?php echo e($fullName); ?> | QuickBio</title>
        <meta name="description" content="<?php echo e($metaDescription); ?>">
        <link rel="canonical" href="<?php echo e($profileUrl); ?>">

        <meta property="og:title" content="<?php echo e($fullName); ?> | QuickBio">
        <meta property="og:description" content="<?php echo e($metaDescription); ?>">
        <meta property="og:type" content="profile">
        <meta property="og:url" content="<?php echo e($profileUrl); ?>">
        <meta property="og:image" content="<?php echo e($imageUrl); ?>">
        <meta property="og:image:alt" content="<?php echo e($fullName); ?> Profilbild">
        <meta property="og:site_name" content="QuickBio">
        <meta property="og:locale" content="de_DE">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo e($fullName); ?> | QuickBio">
        <meta name="twitter:description" content="<?php echo e($metaDescription); ?>">
        <meta name="twitter:image" content="<?php echo e($imageUrl); ?>">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link href="/styles/<?php echo e($safeDesign); ?>" rel="stylesheet" type="text/css" />
        <link href="/styles/style.css" rel="stylesheet" type="text/css" />
        <link href="/styles/text-window.css" rel="stylesheet" type="text/css" />
		<link href="/styles/button.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <div class="h-100 text-white d-flex justify-content-center align-items-center" id="main">
			<div class="share-btn-wrapper">
                <button class="share-btn" onclick="shareProfile()" aria-label="Profil teilen">
                    <svg viewBox="0 0 24 24" class="share-icon">
                        <path fill="currentColor" d="M18 16a3 3 0 0 0-2.4 1.2l-6.6-3.3a3.1 3.1 0 0 0 0-1.8l6.6-3.3A3 3 0 1 0 15 7a3.1 3.1 0 0 0 .1.8L8.5 11a3 3 0 1 0 0 2l6.6 3.2A3 3 0 1 0 18 16z"/>
                    </svg>
                </button>
            </div>
			
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
                    <div id="pfp-wrap" aria-label="Profilbild">
                        <img src="/Profilepics/<?php echo e($safeProfilePicture); ?>" id="profilepic" alt="Profilbild" />
                    </div>
                </div>

                <h1 id="name"><?php echo e($fullName); ?></h1>
                <h2 id="description"><?php echo e($shortintroduction); ?></h2>

                <div class="container mt-4">
                    <div class="card editor-card shadow">
                        <div class="card-body">
                            <div class="form-control editor-area">
                                <?php echo nl2br(e($description)); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <br>

                <div id="links">
                    <div id="container">

                        <?php if (!empty($instagram)) { ?>
                        <a class="social-btn instagram" href="https://www.instagram.com/<?php echo e($instagram); ?>" aria-label="Instagram">
                            <svg width="24px" height="24px" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="social-icon">
                                    <path d="M12 16C14.2091 16 16 14.2091 16 12C16 9.79086 14.2091 8 12 8C9.79086 8 8 9.79086 8 12C8 14.2091 9.79086 16 12 16Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    <path d="M3 16V8C3 5.23858 5.23858 3 8 3H16C18.7614 3 21 5.23858 21 8V16C21 18.7614 18.7614 21 16 21H8C5.23858 21 3 18.7614 3 16Z" stroke="currentColor" stroke-width="1.5"></path>
                                    <path d="M17.5 6.51L17.51 6.49889" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($youtube)) { ?>
                        <a class="social-btn youtube" href="https://www.youtube.com/channel/<?php echo e($youtube); ?>" aria-label="YouTube">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.7 3.5 12 3.5 12 3.5s-7.7 0-9.4.6A3 3 0 0 0 .5 6.2 31.7 31.7 0 0 0 0 12a31.7 31.7 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.7.6 9.4.6 9.4.6s7.7 0 9.4-.6a3 3 0 0 0 2.1-2.1A31.7 31.7 0 0 0 24 12a31.7 31.7 0 0 0-.5-5.8zM9.8 15.5V8.5l6.2 3.5-6.2 3.5z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($twitter)) { ?>
                        <a class="social-btn x" href="https://twitter.com/<?php echo e($twitter); ?>" aria-label="X">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M18 2h3l-7.5 8.6L22 22h-6.8l-5.3-7L3.8 22H.8l8-9.2L2 2h7l4.8 6.3L18 2z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($linkedin)) { ?>
                        <a class="social-btn linkedin" href="https://www.linkedin.com/in/<?php echo e($linkedin); ?>" aria-label="LinkedIn">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M4.98 3.5A2.5 2.5 0 1 1 5 8.5a2.5 2.5 0 0 1-.02-5zM3 9h4v12H3zM10 9h3.6v1.7h.1c.5-.9 1.8-1.9 3.7-1.9 4 0 4.7 2.6 4.7 6v7.2h-4V16c0-1.4 0-3.3-2-3.3s-2.3 1.6-2.3 3.2v6.1H10z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($email)) { ?>
                        <a class="social-btn mail" href="mailto:<?php echo e($email); ?>" aria-label="Mail">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M2 4h20v16H2V4zm10 7L4 6v12h16V6l-8 5z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($reddit)) { ?>
                        <a class="social-btn reddit" href="https://www.reddit.com/user/<?php echo e($reddit); ?>" aria-label="Reddit">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M14.2 15.3c.1.1.1.3 0 .4-.5.5-1.3.7-2.2.7-.9 0-1.7-.2-2.2-.7-.1-.1-.1-.3 0-.4.1-.1.3-.1.4 0 .4.3 1 .5 1.8.5s1.4-.2 1.8-.5c.1-.1.3-.1.4 0zM9.5 13.2a1 1 0 1 1-2 0a1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0a1 1 0 0 1 2 0zm5.5-1.2c0-1.3-.9-2.4-2.1-2.7.1-.3.1-.6.1-.9 0-1.5-1.2-2.7-2.7-2.7-1.1 0-2 .6-2.5 1.5-1.5-.9-3.3-1.4-5.3-1.5l1.1-3.4 2.9.7a2 2 0 1 0 .2-1l-3.3-.8c-.3-.1-.6.1-.7.4L8.3 5.1c-2 .1-3.9.6-5.4 1.5A2.7 2.7 0 0 0 .4 8.2c0 .3 0 .6.1.9A2.8 2.8 0 0 0 0 12c0 1.4 1 2.6 2.3 2.8C3 18 7.1 20.5 12 20.5S21 18 21.7 14.8c1.3-.2 2.3-1.4 2.3-2.8z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($github)) { ?>
                        <a class="social-btn github" href="https://github.com/<?php echo e($github); ?>" aria-label="GitHub">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M12 .5a12 12 0 0 0-3.8 23.4c.6.1.8-.2.8-.6v-2.1c-3.3.7-4-1.4-4-1.4-.6-1.4-1.3-1.8-1.3-1.8-1.1-.7.1-.7.1-.7 1.2.1 1.9 1.3 1.9 1.3 1.1 1.8 2.8 1.3 3.5 1 .1-.8.4-1.3.7-1.6-2.7-.3-5.5-1.3-5.5-6A4.7 4.7 0 0 1 6.2 8c-.1-.3-.5-1.5.1-3.1 0 0 1-.3 3.2 1.2A11 11 0 0 1 12 5.8c.8 0 1.7.1 2.5.3 2.2-1.5 3.2-1.2 3.2-1.2.6 1.6.2 2.8.1 3.1a4.7 4.7 0 0 1 1.3 3.3c0 4.7-2.8 5.7-5.5 6 .4.3.8 1 .8 2v3c0 .4.2.7.8.6A12 12 0 0 0 12 .5z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($onlyfans)) { ?>
                        <a class="social-btn onlyfans" href="https://onlyfans.com/<?php echo e($onlyfans); ?>" aria-label="OnlyFans">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M18.2 7.3c-1.2-.8-2.8-1.2-4.7-1.2H6.8C4.2 6.1 2 8.3 2 10.9s2.2 4.8 4.8 4.8h4.5l-1.2 2.2h3.4l1.2-2.2h.8c3.7 0 6.5-2.1 6.5-5.1 0-1.5-.8-2.7-2.2-3.3zm-2.8 5.1h-5.7l1.6-3h2.9c1.3 0 2 .5 2 1.4 0 1-.8 1.6-2.1 1.6z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($discord)) { ?>
                        <a class="social-btn discord" href="<?php echo e($discord); ?>" aria-label="Discord">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M20.3 4.4A16.7 16.7 0 0 0 16.2 3l-.2.4c1.6.4 2.3 1 2.9 1.5-2.5-1.1-5.1-1.1-7.8 0 .6-.5 1.5-1.1 2.9-1.5L13.8 3a16.7 16.7 0 0 0-4.1 1.4C7.1 8.3 6.4 12 6.6 15.6a16.9 16.9 0 0 0 5 2.5l.6-1c-1-.3-1.9-.8-2.7-1.4l.2.1c1.6.7 3.4 1 5.1 1 1.7 0 3.5-.3 5.1-1l.2-.1c-.8.6-1.7 1.1-2.7 1.4l.6 1a16.9 16.9 0 0 0 5-2.5c.3-4.1-.5-7.8-2.7-11.2zM10 13.5c-.8 0-1.4-.7-1.4-1.6s.6-1.6 1.4-1.6c.8 0 1.4.7 1.4 1.6 0 .9-.6 1.6-1.4 1.6zm4 0c-.8 0-1.4-.7-1.4-1.6s.6-1.6 1.4-1.6c.8 0 1.4.7 1.4 1.6 0 .9-.6 1.6-1.4 1.6z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($snapchat)) { ?>
                        <a class="social-btn snapchat" href="https://www.snapchat.com/add/<?php echo e($snapchat); ?>" aria-label="Snapchat">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M12 2.2c2.8 0 5 2.2 5 5v2.2c0 .4.2.8.6 1l1 .6c.5.3.5 1 0 1.3l-1.3.7c-.2.1-.3.3-.2.5.2.8.8 1.5 1.7 1.7.3.1.4.5.2.7-.7.7-1.8 1.2-3 1.3-.1 0-.2.1-.3.2-.5 1.1-1.7 1.8-3.7 1.8s-3.2-.7-3.7-1.8c0-.1-.1-.2-.3-.2-1.2-.1-2.3-.6-3-1.3-.2-.2-.1-.6.2-.7.9-.2 1.5-.9 1.7-1.7.1-.2 0-.4-.2-.5l-1.3-.7c-.5-.3-.5-1 0-1.3l1-.6c.4-.2.6-.6.6-1V7.2c0-2.8 2.2-5 5-5z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($facebook)) { ?>
                        <a class="social-btn facebook" href="https://www.facebook.com/<?php echo e($facebook); ?>" aria-label="Facebook">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M13.5 22v-8h2.7l.4-3h-3.1V9.1c0-.9.3-1.5 1.6-1.5h1.7V5c-.3 0-1.4-.1-2.6-.1-2.6 0-4.3 1.5-4.3 4.4V11H7v3h2.9v8h3.6z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($whatsapp)) { ?>
                        <a class="social-btn whatsapp" href="https://wa.me/<?php echo e($whatsapp); ?>" aria-label="WhatsApp">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M20.5 3.5A11.8 11.8 0 0 0 1.9 17.6L.5 23.5l6-1.4a11.8 11.8 0 0 0 5.5 1.4h.1A11.9 11.9 0 0 0 20.5 3.5zm-8.4 18c-1.8 0-3.6-.5-5.1-1.4l-.4-.2-3.6.8.8-3.5-.2-.4a9.8 9.8 0 1 1 8.5 4.7zm5.4-7.3c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.2-.7.2s-.8 1-.9 1.1c-.2.2-.3.2-.6.1-1.7-.8-2.8-1.5-4-3.5-.3-.5.3-.5.8-1.7.1-.2.1-.4 0-.6l-.9-2.2c-.2-.5-.5-.4-.7-.4h-.6c-.2 0-.6.1-.9.4-.3.3-1.1 1.1-1.1 2.7s1.1 3.1 1.2 3.4c.2.2 2.2 3.4 5.3 4.7.7.3 1.3.5 1.8.7.8.2 1.5.2 2 .1.6-.1 1.8-.8 2-1.6.3-.8.3-1.5.2-1.6-.1-.1-.3-.2-.6-.4z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($tiktok)) { ?>
                        <a class="social-btn tiktok" href="https://www.tiktok.com/@<?php echo e($tiktok); ?>" aria-label="TikTok">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M16.6 3c.5 2.3 1.8 3.7 4 4v3.1c-1.5.1-2.8-.3-4-.9v6.1c0 3.1-2.5 5.7-5.7 5.7S5.3 18.4 5.3 15.3s2.5-5.7 5.7-5.7c.2 0 .4 0 .6.1v3.2a2.6 2.6 0 0 0-.6-.1 2.5 2.5 0 1 0 2.5 2.5V3h3.1z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($pinterest)) { ?>
                        <a class="social-btn pinterest" href="https://www.pinterest.com/<?php echo e($pinterest); ?>" aria-label="Pinterest">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M12 2a10 10 0 0 0-3.6 19.3c0-.8 0-2 .3-2.9l1.5-6.2s-.4-.8-.4-2c0-1.8 1.1-3.2 2.4-3.2 1.1 0 1.7.8 1.7 1.8 0 1.1-.7 2.8-1 4.4-.3 1.3.7 2.3 2 2.3 2.4 0 4.3-2.5 4.3-6.2 0-3.2-2.3-5.5-5.6-5.5-3.8 0-6.1 2.9-6.1 5.9 0 1.2.5 2.5 1 3.2.1.1.1.3.1.5l-.4 1.5c-.1.2-.2.3-.5.2-1.7-.7-2.8-2.8-2.8-4.5 0-3.7 2.7-7.1 7.8-7.1 4.1 0 7.2 2.9 7.2 6.8 0 4.1-2.6 7.4-6.1 7.4-1.2 0-2.3-.6-2.7-1.4l-.8 3c-.3 1-.9 2.2-1.4 3A10 10 0 1 0 12 2z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($telegram)) { ?>
                        <a class="social-btn telegram" href="https://t.me/<?php echo e($telegram); ?>" aria-label="Telegram">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M21.9 4.6c.3-1.1-.4-1.6-1.4-1.3L2.7 10.1c-1.2.5-1.2 1.2-.2 1.5l4.6 1.4 10.6-6.7c.5-.3 1-.1.6.2l-8.6 7.8-.3 4.5c.4 0 .6-.2.9-.4l2.2-2.1 4.5 3.3c.8.4 1.4.2 1.6-.8l3.3-15.2z"/>
                            </svg>
                        </a>
                        <?php } ?>

                        <?php if (!empty($other)) { ?>
                        <a class="social-btn other" href="<?php echo e($other); ?>" aria-label="Other">
                            <svg viewBox="0 0 24 24" class="social-icon">
                                <path fill="currentColor" d="M10.6 13.4a1 1 0 0 1 0-1.4l3.4-3.4a3 3 0 1 1 4.2 4.2l-1.6 1.6a1 1 0 1 1-1.4-1.4l1.6-1.6a1 1 0 1 0-1.4-1.4L12 13.4a1 1 0 0 1-1.4 0zm2.8-2.8a1 1 0 0 1 0 1.4L10 15.4a3 3 0 0 1-4.2-4.2l1.6-1.6a1 1 0 0 1 1.4 1.4l-1.6 1.6a1 1 0 1 0 1.4 1.4l3.4-3.4a1 1 0 0 1 1.4 0z"/>
                            </svg>
                        </a>
                        <?php } ?>

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

        <script src="/scripts/share.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>