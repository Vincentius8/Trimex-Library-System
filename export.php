<?php
// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Ensure that the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Include database connection (must return $conn as PDO)
require_once "db_connection.php";

// Set headers for Excel file output
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=books_inventory.xls");
header("Pragma: no-cache");
header("Expires: 0");

$output = "";
$output .= "<table border='1'>";
$output .= "<tr>
    <th>Accession No</th>
    <th>Call No</th>
    <th>Author</th>
    <th>Title</th>
    <th>Publisher</th>
    <th>Copies</th>
    <th>Copyright</th>
    <th>Course</th>
</tr>";

// Retrieve the search query from the GET parameter "book_search"
$book_search = "";
if (isset($_GET['book_search'])) {
    $book_search = trim($_GET['book_search']);
}

try {
    // MODIFIED: Use PDO prepared statement if search is not empty
    if (!empty($book_search)) {
        $sql = "SELECT * FROM books
                WHERE accession_no LIKE :search 
                   OR call_no LIKE :search 
                   OR author LIKE :search 
                   OR title LIKE :search 
                   OR publisher LIKE :search 
                   OR copies LIKE :search 
                   OR copyright LIKE :search 
                   OR course LIKE :search";
        $stmt = $conn->prepare($sql);
        $search_param = "%" . $book_search . "%";
        $stmt->bindParam(":search", $search_param, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // MODIFIED
    } else {
        // MODIFIED: Use PDO query directly
        $stmt = $conn->query("SELECT * FROM books");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // MODIFIED
    }

    if ($result && count($result) > 0) {
        foreach ($result as $row) {
            $output .= "<tr>
                <td>" . htmlspecialchars($row['accession_no']) . "</td>
                <td>" . htmlspecialchars($row['call_no']) . "</td>
                <td>" . htmlspecialchars($row['author']) . "</td>
                <td>" . htmlspecialchars($row['title']) . "</td>
                <td>" . htmlspecialchars($row['publisher']) . "</td>
                <td>" . htmlspecialchars($row['copies']) . "</td>
                <td>" . htmlspecialchars($row['copyright']) . "</td>
                <td>" . htmlspecialchars($row['course']) . "</td>
            </tr>";
        }
    }

} catch (PDOException $e) {
    // MODIFIED: Catch and handle DB error securely
    error_log("Database error: " . $e->getMessage());
    $output .= "<tr><td colspan='8'>Error retrieving data.</td></tr>";
}

$output .= "</table>";

echo $output;
exit();
?>
