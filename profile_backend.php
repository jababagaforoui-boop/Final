<?php
// ===== SESSION =====
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// ===== DATABASE =====
$path_to_db = __DIR__ . '/../config/db.php'; // adjust path for admin/config
if(!file_exists($path_to_db)){
    die("Database file not found! Checked path: $path_to_db");
}
include $path_to_db;

// ===== PROTECT PAGE =====
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ===== SAFE SESSION VARIABLES =====
$user       = $_SESSION['user'];
$user_id    = $user['id'] ?? 0;
$user_name  = $user['name'] ?? 'Admin';
$user_email = $user['email'] ?? 'admin@example.com';
$user_role  = $user['role'] ?? 'admin';
$profile_pic = $user['profile_pic'] ?? 'default.png';

$success = '';
$error   = '';

// --------------------
// Profile Picture Upload
// --------------------
if (isset($_POST['upload'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $file = $_FILES['profile_image'];
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = "Only JPG, PNG, GIF files are allowed.";
        } elseif ($file['size'] > 2*1024*1024) {
            $error = "File size must be less than 2MB.";
        } else {
            if (!is_dir('../uploads')) mkdir('../uploads', 0777, true);
            $new_name = uniqid('profile_') . '.' . $ext;
            $destination = "../uploads/" . $new_name;
            if (move_uploaded_file($file['tmp_name'], $destination)) {

                $_SESSION['user']['profile_pic'] = $new_name;
                $profile_pic = $new_name;

                if ($user_id > 0) {
                    $stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
                    if ($stmt) {
                        $stmt->bind_param("si", $new_name, $user_id);
                        $stmt->execute();
                        $stmt->close();
                        $success = "Profile picture updated successfully!";
                    } else {
                        $error = "Database error: ".$conn->error;
                    }
                } else {
                    $error = "User ID not found. Cannot update profile picture.";
                }

            } else {
                $error = "Failed to upload image.";
            }
        }
    } else {
        $error = "No file selected.";
    }
}

// --------------------
// Profile Info Update
// --------------------
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (empty($name) || empty($email)) {
        $error = "Name and Email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = "Password and confirmation do not match.";
    } else {
        if ($user_id > 0) {
            if (!empty($password)) {
                $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
                if ($stmt) {
                    $stmt->bind_param("sssi", $name, $email, $hashed_pass, $user_id);
                    $stmt->execute();
                    $stmt->close();
                    $_SESSION['user']['name'] = $name;
                    $_SESSION['user']['email'] = $email;
                    $user_name = $name;
                    $user_email = $email;
                    $success = "Profile updated successfully!";
                } else {
                    $error = "Database error: ".$conn->error;
                }
            } else {
                $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
                if ($stmt) {
                    $stmt->bind_param("ssi", $name, $email, $user_id);
                    $stmt->execute();
                    $stmt->close();
                    $_SESSION['user']['name'] = $name;
                    $_SESSION['user']['email'] = $email;
                    $user_name = $name;
                    $user_email = $email;
                    $success = "Profile updated successfully!";
                } else {
                    $error = "Database error: ".$conn->error;
                }
            }
        } else {
            $error = "User ID not found. Cannot update profile info.";
        }
    }
}

// --------------------
// Handle logout
// --------------------
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: ../home.php?logout=success");
    exit();
}

// --------------------
// Info Panel Data
// --------------------
$total_branches = $conn->query("SELECT COUNT(*) as cnt FROM branches")->fetch_assoc()['cnt'] ?? 0;
$total_users    = $conn->query("SELECT COUNT(*) as cnt FROM users")->fetch_assoc()['cnt'] ?? 0;
$total_sales    = $conn->query("SELECT SUM(total_amount) as total FROM sales")->fetch_assoc()['total'] ?? 0;
$total_eggs     = $conn->query("SELECT SUM(egg_pieces_sold) as total_eggs FROM sales")->fetch_assoc()['total_eggs'] ?? 0;

?>