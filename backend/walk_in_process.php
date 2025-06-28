<?php
require '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle Walk-In Borrow
        if (isset($_POST['walk_in_borrow'])) {
            $libraryId = $_POST['walk_in_user_library'];
            $accession = $_POST['walk_in_book_accession'];
            $borrowDate = $_POST['walk_in_borrow_date'];
            $dueDate = $_POST['walk_in_due_date'];

            // Get user_id
            $userStmt = $pdo->prepare("SELECT user_id FROM users WHERE library_id = ?");
            $userStmt->execute([$libraryId]);
            $user = $userStmt->fetch();

            // Get book_id and check availability
            $bookStmt = $pdo->prepare("SELECT book_id, status FROM books WHERE accession_no = ?");
            $bookStmt->execute([$accession]);
            $book = $bookStmt->fetch();

            if (!$user || !$book) {
                header("Location: ../walk_in.php?borrow_msg=❌ Invalid Library ID or Accession Number.");
                exit;
            }

            if (strtolower($book['status']) !== 'available') {
                header("Location: ../walk_in.php?borrow_msg=❌ Book is currently borrowed.");
                exit;
            }

            // Check if the user has 3 or more active borrows (unreturned books)
            $checkBorrowLimit = $pdo->prepare("
                SELECT COUNT(*) AS active_borrows
                FROM book_transactions
                WHERE user_id = ? AND return_date IS NULL
            ");
            $checkBorrowLimit->execute([$user['user_id']]);
            $borrowStatus = $checkBorrowLimit->fetch();

            if ($borrowStatus['active_borrows'] >= 3) {
                header("Location: ../walk_in.php?borrow_msg=❌ Borrow limit reached. Return existing books first.");
                exit;
            }

            // Insert transaction
            $insert = $pdo->prepare("
                INSERT INTO book_transactions (user_id, book_id, borrow_date, due_date)
                VALUES (?, ?, ?, ?)
            ");
            $insert->execute([$user['user_id'], $book['book_id'], $borrowDate, $dueDate]);

            // Mark book as borrowed
            $updateBook = $pdo->prepare("UPDATE books SET status = 'borrowed' WHERE book_id = ?");
            $updateBook->execute([$book['book_id']]);

            header("Location: ../walk_in.php?borrow_msg=✅ Borrow recorded successfully.");
            exit;
        }

        // Handle Walk-In Return
        if (isset($_POST['walk_in_return'])) {
            $libraryId = $_POST['walk_in_user_library_return'];
            $accession = $_POST['walk_in_book_accession_return'];
            $returnDate = $_POST['walk_in_borrow_date_return'];

            // Fetch latest unreturned transaction
            $stmt = $pdo->prepare("
                SELECT bt.transaction_id, bt.due_date, b.book_id
                FROM book_transactions bt
                JOIN users u ON bt.user_id = u.user_id
                JOIN books b ON bt.book_id = b.book_id
                WHERE u.library_id = ? AND b.accession_no = ? AND bt.return_date IS NULL
                ORDER BY bt.borrow_date DESC LIMIT 1
            ");
            $stmt->execute([$libraryId, $accession]);
            $transaction = $stmt->fetch();

            if ($transaction) {
                $dueDate = $transaction['due_date'];
                $penalty = 0;

                if (strtotime($returnDate) > strtotime($dueDate)) {
                    $daysLate = floor((strtotime($returnDate) - strtotime($dueDate)) / 86400);
                    $penalty = $daysLate * 5;
                }

                // Update transaction record
                $update = $pdo->prepare("UPDATE book_transactions SET return_date = ?, fine_amount = ? WHERE transaction_id = ?");
                $update->execute([$returnDate, $penalty, $transaction['transaction_id']]);

                // Update book status
                $bookUpdate = $pdo->prepare("UPDATE books SET status = 'available' WHERE book_id = ?");
                $bookUpdate->execute([$transaction['book_id']]);

                header("Location: ../walk_in.php?return_msg=✅ Return recorded. Penalty: ₱{$penalty}");
                exit;
            } else {
                header("Location: ../walk_in.php?return_msg=❌ No active borrow found for this user/book.");
                exit;
            }
        }

    } catch (Exception $e) {
        die("Error processing request: " . $e->getMessage());
    }
}
?>
