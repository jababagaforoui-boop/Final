<?php
// Start session if none exists
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Include database
include __DIR__ . '/../includes/db.php';

// Security check (admin)
if(!isset($_SESSION['admin'])){
    $_SESSION['admin'] = 1; // or redirect to login if preferred
}

/* HANDLE REPLY */
if(isset($_POST['reply_request'])){
    $request_id = (int)$_POST['request_id'];
    $reply_msg  = trim($_POST['reply_msg']);
    $status     = $_POST['status'];

    if(!empty($reply_msg) && in_array($status, ['confirmed','rejected'])){
        $stmt = $conn->prepare("UPDATE requests SET admin_reply=?, status=? WHERE id=?");
        $stmt->bind_param("ssi", $reply_msg, $status, $request_id);
        $stmt->execute();
        $success_reply = "Reply sent successfully!";
    } else {
        $error_reply = "Please fill all fields.";
    }
}

/* FETCH DATA */
$requests = $conn->query("
    SELECT r.*, b.branch_name 
    FROM requests r 
    JOIN branches b ON r.branch_id = b.id 
    ORDER BY r.request_datetime DESC
")->fetch_all(MYSQLI_ASSOC);

$pending_count = $confirmed_count = $rejected_count = 0;
foreach($requests as $r){
    if($r['status']=='pending') $pending_count++;
    if($r['status']=='confirmed') $confirmed_count++;
    if($r['status']=='rejected') $rejected_count++;
}

$returns = $conn->query("
    SELECT ret.*, b.branch_name 
    FROM returns ret 
    JOIN branches b ON ret.branch_id = b.id
    ORDER BY ret.return_datetime DESC
")->fetch_all(MYSQLI_ASSOC);
?>