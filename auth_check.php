<?php
// Start session if none exists
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Function to check admin role
function check_admin() {
    if(!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin'){
        header("Location: ../login.php");
        exit();
    }
}

// Optional: check client role
function check_client() {
    if(!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'client'){
        header("Location: ../index.php");
        exit();
    }
}
?>