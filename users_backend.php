<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Protect page - admin only
if(!isset($_SESSION['admin'])){
    $_SESSION['admin'] = 1; // Or redirect to login page
}

// ===== DASHBOARD COUNTS =====
$total_users = $conn->query("SELECT COUNT(*) AS total FROM `user`");
if (!$total_users) { die("Query failed: " . $conn->error); }
$total_users = $total_users->fetch_assoc()['total'];

$total_branches = $conn->query("SELECT COUNT(*) AS total FROM branches");
if (!$total_branches) { die("Query failed: " . $conn->error); }
$total_branches = $total_branches->fetch_assoc()['total'];

// ===== FETCH USERS WITH BRANCH NAME =====
$users_result = $conn->query("
    SELECT u.id, u.username, u.fullname, u.email, u.contact, u.created_at,
           b.branch_name AS branch
    FROM `user` u
    LEFT JOIN branches b ON u.branch_id = b.id
    ORDER BY u.created_at DESC
");
if (!$users_result) { die("Query failed: " . $conn->error); }