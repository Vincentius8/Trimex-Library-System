<?php
// ─── SESSION & CONFIG ───────────────────────────────
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 120);
ini_set('upload_max_filesize', '20M');
ini_set('post_max_size', '22M');

$sessionLifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_set_cookie_params($sessionLifetime);
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
$admin_id = $_SESSION['admin_id'];

// ─── DB CONNECTION ──────────────────────────────────
require_once "db_connection.php";

// ─── FETCH FIRSTNAME FOR GREETING ───────────────────
$admin_firstname = "";
try {
    $stmt = $pdo->prepare("SELECT firstname FROM admin WHERE admin_id = :id LIMIT 1");
    $stmt->execute(['id' => $admin_id]);
    $admin_firstname = $stmt->fetchColumn() ?: '';
} catch (PDOException $e) {
    error_log("ERROR fetching admin firstname: " . $e->getMessage());
}

// ─── IMAGE SETUP ────────────────────────────────────
$uploadDir = __DIR__ . "/../uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
const TARGET_BYTES = 10 * 1024;
const TRIMEX_DOMAIN = '@trimexcolleges.edu.ph';

// ─── HELPERS ────────────────────────────────────────
function isTrimexEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL)
        && substr(strtolower($email), -strlen(TRIMEX_DOMAIN)) === TRIMEX_DOMAIN;
}

// ─── ADD ADMIN ──────────────────────────────────────
if (isset($_POST['add_admin'])) {
    [$sid, $fn, $ln, $em] = array_map('trim', [
        $_POST['school_id'],
        $_POST['firstname'],
        $_POST['lastname'],
        $_POST['email']
    ]);

    if (!isTrimexEmail($em)) {
        $_SESSION['error_message'] = "Email must end with " . TRIMEX_DOMAIN;
        header("Location: admin_management.php");
        exit;
    }

    $pic  = uploadProfileImage($_FILES['profile_image']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO admin (school_id, firstname, lastname, email, password, profile_image)
             VALUES (:sid, :fn, :ln, :em, :pw, :pic)"
        );
        $stmt->execute([
            'sid' => $sid,
            'fn' => $fn,
            'ln' => $ln,
            'em' => $em,
            'pw' => $pass,
            'pic'=> $pic ?? ''
        ]);
    } catch (PDOException $e) {
        error_log("ERROR adding admin: " . $e->getMessage());
    }

    header("Location: admin_management.php");
    exit;
}

// ─── UPDATE ADMIN ───────────────────────────────────
if (isset($_POST['update_admin'])) {
    $id = intval($_POST['admin_id']);
    [$sid, $fn, $ln, $em] = array_map('trim', [
        $_POST['school_id'],
        $_POST['firstname'],
        $_POST['lastname'],
        $_POST['email']
    ]);

    if (!isTrimexEmail($em)) {
        $_SESSION['error_message'] = "Email must end with " . TRIMEX_DOMAIN;
        header("Location: admin_management.php");
        exit;
    }

    $pic = uploadProfileImage($_FILES['profile_image']);

    $sql   = "UPDATE admin SET school_id = :sid, firstname = :fn, lastname = :ln, email = :em";
    $params = [
        'sid' => $sid,
        'fn'  => $fn,
        'ln'  => $ln,
        'em'  => $em,
        'id'  => $id
    ];

    if (!empty($_POST['password'])) {
        $sql .= ", password = :pw";
        $params['pw'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    if ($pic !== null) {
        $sql .= ", profile_image = :pic";
        $params['pic'] = $pic;
    }

    $sql .= " WHERE admin_id = :id";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("ERROR updating admin: " . $e->getMessage());
    }

    header("Location: admin_management.php");
    exit;
}

// ─── DELETE ADMIN ───────────────────────────────────
if (isset($_POST['confirm_delete'])) {
    $del = intval($_POST['delete_admin_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM admin WHERE admin_id = :id");
        $stmt->execute(['id' => $del]);
    } catch (PDOException $e) {
        error_log("ERROR deleting admin: " . $e->getMessage());
    }

    header("Location: admin_management.php");
    exit;
}

// ─── FETCH FOR EDIT FORM ────────────────────────────
$edit_admin = null;
if (isset($_GET['edit_admin'])) {
    $eid = intval($_GET['edit_admin']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE admin_id = :id");
        $stmt->execute(['id' => $eid]);
        $edit_admin = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("ERROR fetching admin for edit: " . $e->getMessage());
    }
}

// ─── FETCH FOR ADMIN TABLE LIST ─────────────────────
try {
    $stmt = $pdo->query("SELECT * FROM admin");
    $result_admins = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("ERROR fetching admins: " . $e->getMessage());
    $result_admins = [];
}

// ─── IMAGE HANDLERS ─────────────────────────────────
function saveJpegGD($src, $target, $quality = 75) {
    $img = @imagecreatefromjpeg($src);
    if ($img) {
        imagejpeg($img, $target, $quality);
        imagedestroy($img);
        return true;
    }
    return false;
}

function saveGifGD($src, $target) {
    $img = @imagecreatefromgif($src);
    if ($img) {
        imagegif($img, $target);
        imagedestroy($img);
        return true;
    }
    return false;
}

function uploadProfileImage($file) {
    global $uploadDir;
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;

    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed)) return null;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid('admin_', true) . '.' . $ext;
    $target = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) return null;

    return $filename;
}
