<?php
// 1) Session & Auth -----------------------------------------------------------
$sessionLifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_set_cookie_params($sessionLifetime);
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
$admin_id = $_SESSION['admin_id'];

// 2) Database & Admin Info ----------------------------------------------------
require_once "db_connection.php";

try {
    $stmt = $pdo->prepare("SELECT firstname, profile_image FROM admin WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $adminName = $admin['firstname'] ?? 'Admin';
    $adminProfile = $admin['profile_image'] ?? '';
} catch (PDOException $e) {
    die("Error fetching admin info: " . $e->getMessage());
}

$view = $_GET['view'] ?? 'active';
$currentPage = max(1, (int)($_GET['page'] ?? 1));

// 3) Image Utilities ----------------------------------------------------------
function resizeAndCompress($src, $dest, $maxBytes = 10240, $maxDim = 200) {
    $info = getimagesize($src);
    if (!$info) return false;

    switch ($info['mime']) {
        case 'image/jpeg': $orig = imagecreatefromjpeg($src); break;
        case 'image/png':  $orig = imagecreatefrompng($src);  break;
        case 'image/gif':  $orig = imagecreatefromgif($src);  break;
        default: return false;
    }

    $w = imagesx($orig);
    $h = imagesy($orig);
    $ratio = $w / $h;

    $newW = ($w > $h) ? $maxDim : $maxDim * $ratio;
    $newH = ($h >= $w) ? $maxDim : $maxDim / $ratio;

    $resized = imagecreatetruecolor($newW, $newH);
    imagecopyresampled($resized, $orig, 0, 0, 0, 0, $newW, $newH, $w, $h);
    imagedestroy($orig);

    $quality = 90;
    do {
        imagejpeg($resized, $dest, $quality);
        clearstatcache();
        $size = filesize($dest);
        $quality -= 10;
    } while ($size > $maxBytes && $quality >= 10);

    imagedestroy($resized);
    return ($size <= $maxBytes);
}

function processCoverUpload() {
    if (!isset($_FILES['cover_image']) || $_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
        return '';
    }

    $tmp = $_FILES['cover_image']['tmp_name'];
    $name = basename($_FILES['cover_image']['name']);
    $type = mime_content_type($tmp);
    $size = filesize($tmp);

    if (strpos($type, 'image/') !== 0 || $size > (4 * 1024 * 1024)) {
        die("Invalid image type or size too large.");
    }

    $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($name, PATHINFO_FILENAME)) . '.jpg';
    $destPath = 'uploads/' . $safeName;
    if (!is_dir('uploads')) mkdir('uploads', 0777, true);

    if (!move_uploaded_file($tmp, $destPath)) {
        die("Failed to move uploaded file.");
    }

    resizeAndCompress($destPath, $destPath);
    return $destPath;
}

// 4) POST Actions (Add / Edit / Discard) --------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $rawBase = trim($_POST['accession_no']);
        $baseNum = is_numeric($rawBase) ? (int)$rawBase : 0;
        $cover = processCoverUpload();
        $call_no = trim($_POST['call_no']);
        $author = trim($_POST['author']);
        $title = trim($_POST['title']);
        $publisher = trim($_POST['publisher']);
        $copies = max(1, (int)$_POST['copies']);
        $copyright = trim($_POST['copyright']);
        $course = trim($_POST['course']);
        $status = trim($_POST['availability']);
        $lastId = null;

        for ($i = 0; $i < $copies; $i++) {
            $accStr = is_numeric($rawBase) ? (string)($baseNum + $i) : $rawBase;
            $stmtCheck = $pdo->prepare("SELECT 1 FROM books WHERE accession_no = ? LIMIT 1");
            $stmtCheck->execute([$accStr]);
            if ($stmtCheck->fetch()) {
                $accStr .= '-duplicate';
            }
            if (!is_numeric($rawBase) && $copies > 1) {
                $accStr .= '-' . ($i + 1);
                $cpy = $i + 1;
            } else {
                $cpy = ($copies > 1) ? $i + 1 : 1;
            }

            $stmtInsert = $pdo->prepare("
                INSERT INTO books (
                    accession_no, call_no, author, title, publisher, copies,
                    copyright, course, status, book_image
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtInsert->execute([
                $accStr, $call_no, $author, $title, $publisher,
                $cpy, $copyright, $course, $status, $cover
            ]);

            $lastId = $pdo->lastInsertId();
        }

        $allCourses = $pdo->query("SELECT DISTINCT course FROM books WHERE status NOT IN ('missing', 'discarded')")->fetchAll(PDO::FETCH_COLUMN);
        $idx = array_search($course, $allCourses, true);
        $pageForShelf = floor(($idx !== false ? $idx : 0) / 3) + 1;

        header("Location: book_management.php?success=added&view=$view&page=$pageForShelf&added=$lastId");
        exit;

    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $coverPath = (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK)
            ? processCoverUpload()
            : trim($_POST['existing_cover']);

        $stmt = $pdo->prepare("
            UPDATE books SET
                accession_no = ?, call_no = ?, author = ?, title = ?, publisher = ?,
                copies = ?, copyright = ?, course = ?, status = ?, book_image = ?
            WHERE book_id = ?
        ");
        $stmt->execute([
            trim($_POST['accession_no']),
            trim($_POST['call_no']),
            trim($_POST['author']),
            trim($_POST['title']),
            trim($_POST['publisher']),
            (int)$_POST['copies'],
            trim($_POST['copyright']),
            trim($_POST['course']),
            trim($_POST['availability']),
            $coverPath,
            $id
        ]);

        header("Location: book_management.php?view=$view&page=$currentPage&edited=$id");
        exit;

    } elseif ($action === 'discard') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE books SET status = 'discarded' WHERE book_id = ?")->execute([$id]);
    }
}

// 5) DELETE via GET -----------------------------------------------------------
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM books WHERE book_id = ?")->execute([$deleteId]);
}

// 6) FETCH + SEARCH + PAGINATION ----------------------------------------------
$searchTerm = trim($_GET['search'] ?? '');

switch ($view) {
    case 'missing':
        $baseQuery = "SELECT * FROM books WHERE status = 'missing'";
        break;
    case 'discarded':
        $baseQuery = "SELECT * FROM books WHERE status = 'discarded'";
        break;
    case 'borrowed':
        $baseQuery = "
            SELECT books.*,
                   COALESCE(u.firstname, ru.firstname) AS firstname,
                   COALESCE(u.lastname, ru.lastname) AS lastname,
                   COALESCE(u.library_id, ru.library_id) AS library_id
            FROM books
            LEFT JOIN book_transactions bt ON books.book_id = bt.book_id AND bt.return_date IS NULL
            LEFT JOIN users u ON bt.user_id = u.user_id
            LEFT JOIN reservations r ON books.book_id = r.book_id AND r.status = 'approved' AND r.return_date IS NULL
            LEFT JOIN users ru ON r.user_id = ru.user_id
            WHERE books.status IN ('borrowed', 'reserved')
        ";
        break;
    default:
        $baseQuery = "SELECT * FROM books WHERE status NOT IN ('missing', 'discarded')";
        break;
}

$params = [];
if ($searchTerm !== '') {
    $searchLike = "%$searchTerm%";

    if ($view === 'borrowed') {
        $baseQuery .= "
            AND (
                books.title LIKE ? OR
                books.author LIKE ? OR
                books.publisher LIKE ? OR
                books.accession_no LIKE ? OR
                books.course LIKE ? OR
                u.firstname LIKE ? OR
                u.lastname LIKE ? OR
                ru.firstname LIKE ? OR
                ru.lastname LIKE ?
            )
        ";
        $params = array_fill(0, 9, $searchLike);
    } else {
        $baseQuery .= "
            AND (
                title LIKE ? OR
                author LIKE ? OR
                publisher LIKE ? OR
                accession_no LIKE ? OR
                course LIKE ?
            )
        ";
        $params = array_fill(0, 5, $searchLike);
    }
}

try {
    $stmt = $pdo->prepare($baseQuery);
    $stmt->execute($params);
    $allBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching book data: " . $e->getMessage());
}

// 7) Group by course → shelf format + paginate
$groupedBooks = [];
foreach ($allBooks as $b) {
    $course = $b['course'] ?: 'Uncategorized';
    $groupedBooks[$course][] = $b;
}

$allShelves = array_map(fn($c, $books) => ['course' => $c, 'books' => $books], array_keys($groupedBooks), $groupedBooks);
$pages = array_chunk($allShelves, 3);
$totalPages = max(1, count($pages));
$currentPage = min($totalPages, max(1, $currentPage));
$currentShelves = $pages[$currentPage - 1] ?? [];
?>
