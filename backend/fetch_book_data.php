<?php
require '../db_connection.php';

if (isset($_GET['accession_no'])) {
    $stmt = $pdo->prepare("SELECT title FROM books WHERE accession_no = ?");
    $stmt->execute([$_GET['accession_no']]);
    $book = $stmt->fetch();

    echo json_encode($book ?: []);
}
?>
