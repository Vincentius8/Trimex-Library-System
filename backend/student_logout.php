<?php
session_start();
// Destroy all session data
session_destroy();

// Redirect to student_login.php
header("Location: ../student_login.php");
exit;
