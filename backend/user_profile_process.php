<?php
session_start();
require_once "db_connection.php"; // ensures $pdo is loaded

if (!isset($_SESSION['user_id'])) {
    header("Location: student_login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// ─── HELPERS ────────────────────────────────────────
function compressImage($sourcePath, $destPath, $targetSizeBytes = 10240)
{
    $info = getimagesize($sourcePath);
    if (!$info) return false;

    switch ($info['mime']) {
        case 'image/jpeg': $img = imagecreatefromjpeg($sourcePath); break;
        case 'image/png':  $img = imagecreatefrompng($sourcePath);  break;
        case 'image/gif':  $img = imagecreatefromgif($sourcePath);  break;
        default:           return false;
    }

    $quality = 90;
    do {
        imagejpeg($img, $destPath, $quality);
        clearstatcache();
        $size    = filesize($destPath);
        $quality -= 10;
    } while ($size > $targetSizeBytes && $quality >= 10);

    imagedestroy($img);
    return ($size <= $targetSizeBytes);
}

function handleProfileImageUpload(): string
{
    if (
        empty($_FILES['profile_image']['tmp_name']) ||
        $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK
    ) {
        return '';
    }

    $tmp      = $_FILES['profile_image']['tmp_name'];
    $name     = pathinfo($_FILES['profile_image']['name'], PATHINFO_FILENAME);
    $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);

    $dir = "uploads/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $filename = "profile_{$safeName}_" . time() . ".jpg";
    $dest     = $dir . $filename;

    if (!move_uploaded_file($tmp, $dest)) {
        die("Error uploading image.");
    }

    compressImage($dest, $dest, 10 * 1024);
    return $filename;
}

// ─── POST ACTIONS ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_profile'])) {
            $first = trim($_POST['firstname']);
            $last  = trim($_POST['lastname']);
            $email = trim($_POST['email']);
            $imgFilename = handleProfileImageUpload();

            if ($imgFilename !== '') {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET firstname = :firstname, lastname = :lastname, email = :email, profile_image = :profile_image 
                    WHERE user_id = :user_id
                ");
                $stmt->execute([
                    ':firstname' => $first,
                    ':lastname' => $last,
                    ':email' => $email,
                    ':profile_image' => $imgFilename,
                    ':user_id' => $user_id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET firstname = :firstname, lastname = :lastname, email = :email 
                    WHERE user_id = :user_id
                ");
                $stmt->execute([
                    ':firstname' => $first,
                    ':lastname' => $last,
                    ':email' => $email,
                    ':user_id' => $user_id
                ]);
            }
        } elseif (isset($_POST['change_password'])) {
            $current = $_POST['current_password'];
            $newPass = $_POST['new_password'];

            $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $user_id]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($current, $hash)) {
                die("Current password incorrect.");
            }

            $newHash = password_hash($newPass, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
            $stmt->execute([
                ':password' => $newHash,
                ':user_id' => $user_id
            ]);
        }

        header("Location: user_profile.php");
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// ─── FETCH USER INFO ───────────────────────────────
$stmt = $pdo->prepare("
    SELECT library_id, firstname, lastname, email, profile_image 
    FROM users 
    WHERE user_id = :user_id
");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── CURRENT RECORDS ───────────────────────────────
$currentRecords = [];

// Borrowed books
$stmt = $pdo->prepare("
    SELECT b.book_image, b.title, b.author, bt.borrow_date, bt.fine_amount
    FROM book_transactions bt
    JOIN books b ON bt.book_id = b.book_id
    WHERE bt.user_id = :user_id AND bt.return_date IS NULL
    ORDER BY bt.borrow_date DESC
");
$stmt->execute([':user_id' => $user_id]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $row['entry_type'] = 'borrowed';
    $currentRecords[] = $row;
}

// Reserved books
$stmt = $pdo->prepare("
    SELECT b.book_image, b.title, b.author, r.reservation_date, r.fine_amount, r.status
    FROM reservations r
    JOIN books b ON r.book_id = b.book_id
    WHERE r.user_id = :user_id AND r.return_date IS NULL AND r.status IN ('pending', 'approved')
    ORDER BY r.reservation_date DESC
");
$stmt->execute([':user_id' => $user_id]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $row['entry_type'] = 'reserved';
    $currentRecords[] = $row;
}

// ─── HISTORY ───────────────────────────────────────
$historyRecords = [];

// Borrow history
$stmt = $pdo->prepare("
    SELECT b.book_image, b.title, b.author, bt.borrow_date, bt.return_date, bt.fine_amount
    FROM book_transactions bt
    JOIN books b ON bt.book_id = b.book_id
    WHERE bt.user_id = :user_id AND bt.return_date IS NOT NULL
    ORDER BY bt.return_date DESC
");
$stmt->execute([':user_id' => $user_id]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $row['entry_type'] = 'borrowed_history';
    $historyRecords[] = $row;
}

// Reservation history
$stmt = $pdo->prepare("
    SELECT b.book_image, b.title, b.author, r.reservation_date, r.return_date, r.fine_amount
    FROM reservations r
    JOIN books b ON r.book_id = b.book_id
    WHERE r.user_id = :user_id AND r.return_date IS NOT NULL
    ORDER BY r.return_date DESC
");
$stmt->execute([':user_id' => $user_id]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $row['entry_type'] = 'reserved_history';
    $historyRecords[] = $row;
}

// ─── NOTIFICATIONS ─────────────────────────────────
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = :user_id 
    ORDER BY created_at DESC
");
$stmt->execute([':user_id' => $user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
