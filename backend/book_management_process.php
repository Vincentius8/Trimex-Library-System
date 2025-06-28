<?php
// 1) Session & Auth -----------------------------------------------------------
$sessionLifetime = 30 * 24 * 60 * 60; // 30 days
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_set_cookie_params($sessionLifetime);

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
$admin_id = $_SESSION['admin_id'];

// 2) PDO Database Connection & Admin Info -------------------------------------
require_once "db_connection.php"; // Make sure this file sets up $pdo (not $conn)

try {
    $stmt = $pdo->prepare("SELECT firstname, profile_image FROM admin WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $admin_firstname = $admin['firstname'] ?? 'Admin';
    $admin_profile   = $admin['profile_image'] ?? '';
    $adminName       = $admin_firstname;
} catch (PDOException $e) {
    die("Error fetching admin info: " . $e->getMessage());
}

// Persist view & page
$view        = $_GET['view'] ?? 'active';
$currentPage = max(1, (int)($_GET['page'] ?? 1));


// 3) Image Helpers ------------------------------------------------------------
function resizeAndCompress($srcPath, $destPath, $targetBytes = 10240, $maxDim = 200) {
    $info = getimagesize($srcPath);
    if (!$info) return false;
    switch ($info['mime']) {
        case 'image/jpeg': $orig = imagecreatefromjpeg($srcPath); break;
        case 'image/png':  $orig = imagecreatefrompng($srcPath);  break;
        case 'image/gif':  $orig = imagecreatefromgif($srcPath);  break;
        default: return false;
    }
    $w = imagesx($orig); $h = imagesy($orig);
    $ratio = $w / $h;
    if ($w > $h) {
        $newW = min($w, $maxDim);
        $newH = $newW / $ratio;
    } else {
        $newH = min($h, $maxDim);
        $newW = $newH * $ratio;
    }
    $newImg = imagecreatetruecolor($newW, $newH);
    imagecopyresampled($newImg, $orig, 0,0,0,0, $newW, $newH, $w, $h);
    imagedestroy($orig);
    $quality = 90;
    do {
        imagejpeg($newImg, $destPath, $quality);
        clearstatcache();
        $size = filesize($destPath);
        $quality -= 10;
    } while ($size > $targetBytes && $quality >= 10);
    imagedestroy($newImg);
    return ($size <= $targetBytes);
}

function processCoverUpload() {
    $maxUpload  = 4 * 1024 * 1024; // 4MB
    $targetSize = 10 * 1024;       // 10KB
    $maxDim     = 200;
    if (empty($_FILES['cover_image']['tmp_name']) || $_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
        return "";
    }
    $tmp  = $_FILES['cover_image']['tmp_name'];
    $name = $_FILES['cover_image']['name'];
    $type = $_FILES['cover_image']['type'];
    $size = $_FILES['cover_image']['size'];
    if (strpos($type, 'image/') !== 0 || $size > $maxUpload) {
        die("Error: Invalid or too-large image.");
    }
    $dir = 'uploads/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $base     = pathinfo($name, PATHINFO_FILENAME);
    $safe     = preg_replace('/[^A-Za-z0-9_\-]/','_',$base);
    $filename = time() . "_{$safe}.jpg";
    $destPath = $dir . $filename;
    if (!move_uploaded_file($tmp, $destPath)) {
        die("Error: Could not save upload.");
    }
    resizeAndCompress($destPath, $destPath, $targetSize, $maxDim);
    return $dir . $filename;
}

// 4) CRUD Operations ----------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $rawBase = trim($_POST['accession_no']);
        $baseNum = is_numeric($rawBase) ? (int)$rawBase : 0;
        $cover   = processCoverUpload();

        $call_no   = trim($_POST['call_no']);
        $author    = trim($_POST['author']);
        $title     = trim($_POST['title']);
        $publisher = trim($_POST['publisher']);
        $copies    = max(1, (int)$_POST['copies']);
        $copyright = trim($_POST['copyright']);
        $course    = trim($_POST['course']);
        $status    = trim($_POST['availability']);

        $lastId = null;

        for ($i = 0; $i < $copies; $i++) {
            if (is_numeric($rawBase)) {
                $candidate = $baseNum + $i;
                $accStr = (string)$candidate;
            } else {
                $accStr = $rawBase;
            }

            // Check for duplicates
            $stmtCheck = $pdo->prepare("SELECT 1 FROM books WHERE accession_no = ? LIMIT 1");
            $stmtCheck->execute([$accStr]);
            if ($stmtCheck->fetch()) {
                $accStr .= '-duplicate';
            }

            if (!is_numeric($rawBase) && $copies > 1) {
                $accStr .= '-' . ($i + 1);
                $cpy = $i + 1;
            } else {
                $cpy = $copies > 1 ? $i + 1 : 1;
            }

            $stmtInsert = $pdo->prepare("
                INSERT INTO books (
                    accession_no, call_no, author, title, publisher, copies,
                    copyright, course, status, book_image
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmtInsert->execute([
                $accStr, $call_no, $author, $title, $publisher, $cpy,
                $copyright, $course, $status, $cover
            ]);

            $lastId = $pdo->lastInsertId();
        }

        // Shelf calculation: Re-fetch current view’s books, grouped by course
        switch ($view) {
            case 'missing':
                $baseQuery = "SELECT * FROM books WHERE status = 'missing'";
                break;
            case 'discarded':
                $baseQuery = "SELECT * FROM books WHERE status = 'discarded'";
                break;
            case 'borrowed':
                $baseQuery = "SELECT * FROM books WHERE status IN ('borrowed','reserved')";
                break;
            default:
                $baseQuery = "SELECT * FROM books WHERE status NOT IN ('missing','discarded')";
        }

        $res = $pdo->query($baseQuery);
        $all = $res->fetchAll(PDO::FETCH_ASSOC);
        $grouped = [];

        foreach ($all as $b) {
            $c = $b['course'] ?: 'Uncategorized';
            $grouped[$c][] = $b;
        }

        $shelves = array_keys($grouped);
        $idx = array_search($course, $shelves, true);
        $pageForShelf = floor(($idx < 0 ? 0 : $idx) / 3) + 1;

        header("Location: book_management.php?success=added&view={$view}&page={$pageForShelf}&added={$lastId}");
        exit;

    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];

        $coverPath = isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK
            ? processCoverUpload()
            : trim($_POST['existing_cover']);

        $accession = trim($_POST['accession_no']);
        $call_no   = trim($_POST['call_no']);
        $author    = trim($_POST['author']);
        $title     = trim($_POST['title']);
        $publisher = trim($_POST['publisher']);
        $copies    = (int)$_POST['copies'];
        $copyright = trim($_POST['copyright']);
        $course    = trim($_POST['course']);
        $status    = trim($_POST['availability']);

        $stmtUpdate = $pdo->prepare("
            UPDATE books SET
              accession_no = ?, call_no = ?, author = ?, title = ?, publisher = ?,
              copies = ?, copyright = ?, course = ?, status = ?, book_image = ?
            WHERE book_id = ?
        ");

        $stmtUpdate->execute([
            $accession, $call_no, $author, $title, $publisher,
            $copies, $copyright, $course, $status, $coverPath, $id
        ]);

        header("Location: book_management.php?view={$view}&page={$currentPage}&edited={$id}");
        exit;

    } elseif ($action === 'discard') {
        $id = (int)$_POST['id'];
        $stmtDiscard = $pdo->prepare("UPDATE books SET status = 'discarded' WHERE book_id = ?");
        $stmtDiscard->execute([$id]);
    }
}

// 5) DELETE via GET -----------------------------------------------------------
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmtDelete = $pdo->prepare("DELETE FROM books WHERE book_id = ?");
    $stmtDelete->execute([$deleteId]);
}

// 6) Prepare Data for Listing ------------------------------------------------
$view = $_GET['view'] ?? 'active';
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
                   COALESCE(u.lastname, ru.lastname)   AS lastname,
                   COALESCE(u.library_id, ru.library_id) AS library_id
            FROM books
            LEFT JOIN book_transactions bt
              ON books.book_id = bt.book_id AND bt.return_date IS NULL
            LEFT JOIN users u ON bt.user_id = u.user_id
            LEFT JOIN reservations r
              ON books.book_id = r.book_id AND r.status = 'approved' AND r.return_date IS NULL
            LEFT JOIN users ru ON r.user_id = ru.user_id
            WHERE books.status IN ('borrowed', 'reserved')
        ";
        break;
    default:
        $baseQuery = "SELECT * FROM books WHERE status NOT IN ('missing', 'discarded')";
}

$params = [];
if ($searchTerm !== '') {
    // Append search conditions
    $baseQuery .= ($view === 'borrowed')
        ? " AND (books.title LIKE :search OR books.author LIKE :search OR books.publisher LIKE :search OR books.accession_no LIKE :search OR books.course LIKE :search)"
        : " AND (title LIKE :search OR author LIKE :search OR publisher LIKE :search OR accession_no LIKE :search OR course LIKE :search)";
    $params['search'] = "%{$searchTerm}%";
}

try {
    $stmt = $pdo->prepare($baseQuery);
    $stmt->execute($params);
    $allBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching book data: " . $e->getMessage());
}

// Group by course
$groupedBooks = [];
foreach ($allBooks as $b) {
    $c = $b['course'] ?: 'Uncategorized';
    $groupedBooks[$c][] = $b;
}

// Group into "shelves"
$allShelves = [];
foreach ($groupedBooks as $course => $books) {
    $allShelves[] = ['course' => $course, 'books' => $books];
}

// Paginate shelves (3 per page)
$pages = array_chunk($allShelves, 3);
$totalPages = max(1, count($pages));
$currentPage = min($totalPages, max(1, (int)($_GET['page'] ?? 1)));
$currentShelves = $pages[$currentPage - 1] ?? [];

