<?php
    declare(strict_types=1);

    session_start();

    if (!isset($_SESSION["loggedin"])) {
        $_SESSION["loggedin"] = false;
    }

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = trim($path ?? '', '/');

    $segments = $path === '' ? [] : explode('/', $path);

    $allowedPages = [
        'activate',
        'code-gen',
        'contactform',
        'create',
        'datenschutz',
        'delete',
        'editing',
        'impressum',
        'login',
        'logout',
        'reset',
        'tos',
        'verify-activate',
        'verify-delete',
        'verify-reset'
    ];

    $page = 'default';
    $id = null;

    if (count($segments) === 0) {
        $page = 'default';

    } elseif (count($segments) === 1) {

        if (in_array($segments[0], $allowedPages, true)) {
            $page = $segments[0];
        } else {
            $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $segments[0]);
            $page = !empty($id) ? 'personal' : 'default';
        }

    } elseif (count($segments) === 2) {

        $first = $segments[0];
        $second = $segments[1];
        $cleanCode = preg_replace('/[^a-zA-Z0-9]/', '', $second);

        if ($first === 'reset' && $cleanCode !== '') {
            $page = 'verify-reset';
            $id = $cleanCode;

        } elseif ($first === 'delete' && $cleanCode !== '') {
            $page = 'verify-delete';
            $id = $cleanCode;

        } elseif ($first === 'activate' && $cleanCode !== '') {
            $page = 'verify-activate';
            $id = $cleanCode;

        } else {
            $page = 'default';
        }

    } else {
        $page = 'default';
    }

    switch ($page) {
        case "code-gen":
            include __DIR__ . "/pages/code_gen.php";
            break;

        case "contactform":
            include __DIR__ . "/pages/contactform.php";
            break;

        case "create":
            include __DIR__ . "/pages/create.php";
            break;

        case "datenschutz":
            include __DIR__ . "/pages/datenschutz.php";
            break;

        case "delete":
            include __DIR__ . "/pages/delete.php";
            break;

        case "editing":
            include __DIR__ . "/pages/editing.php";
            break;

        case "impressum":
            include __DIR__ . "/pages/impressum.php";
            break;

        case "login":
            include __DIR__ . "/pages/login.php";
            break;

        case "logout":
            include __DIR__ . "/pages/logout.php";
            break;

        case "reset":
            include __DIR__ . "/pages/reset.php";
            break;

        case "tos":
            include __DIR__ . "/pages/tos.php";
            break;

        case "verify-activate":
            include __DIR__ . "/pages/verify-activate.php";
            break;

        case "verify-delete":
            include __DIR__ . "/pages/verify-delete.php";
            break;

        case "verify-reset":
            include __DIR__ . "/pages/verify-reset.php";
            break;

        case "personal":
            include __DIR__ . "/pages/personal.php";
            break;

        default:
            include __DIR__ . "/pages/default.php";
            break;
    }
?>