<?php
require '../db_connection.php';

if (isset($_GET['library_id'])) {
    $libraryId = $_GET['library_id'];

    $stmt = $pdo->prepare("SELECT firstname, lastname FROM users WHERE library_id = ?");
    $stmt->execute([$libraryId]);
    $user = $stmt->fetch();

    if ($user) {
        echo json_encode([
            'firstname' => $user['firstname'],
            'lastname' => $user['lastname']
        ]);
    } else {
        echo json_encode([]);
    }
}
?>
