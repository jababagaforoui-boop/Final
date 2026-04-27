<?php
session_start();

/* ----------------------
   DATABASE CONNECTION
----------------------- */
$servername = "localhost";
$username   = "root";
$password   = ""; 
$dbname     = "freshfarmegg";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

/* ----------------------
   SECURITY CHECK
----------------------- */
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client'){
    header("Location: login.php"); 
    exit();
}

$user = $_SESSION['user'];
$branch_id   = $user['branch_id'];
$branch_name = $user['branch_name'];

/* ----------------------
   PRICES
----------------------- */
$price_big_tray   = 106; // price per big tray
$price_small_tray = 56;  // price per small tray

/* ----------------------
   HANDLE SALES AND UPDATE STOCK
----------------------- */
$success_sale = '';
$stock_alerts = [];

if(isset($_POST['add_sale'])){
    $big_trays_sold   = (int)$_POST['big_trays_sold'];
    $small_trays_sold = (int)$_POST['small_trays_sold'];

    $stmt = $conn->prepare("SELECT big_trays, small_trays FROM inventory WHERE branch_id=? LIMIT 1");
    $stmt->bind_param("i",$branch_id);
    $stmt->execute();
    $inventory = $stmt->get_result()->fetch_assoc();

    $current_big   = $inventory['big_trays'] ?? 0;
    $current_small = $inventory['small_trays'] ?? 0;

    $new_big   = max(0, $current_big - $big_trays_sold);
    $new_small = max(0, $current_small - $small_trays_sold);

    $stmt_update = $conn->prepare("UPDATE inventory SET big_trays=?, small_trays=?, updated_at=NOW() WHERE branch_id=?");
    $stmt_update->bind_param("iii",$new_big,$new_small,$branch_id);
    $stmt_update->execute();

    $stmt_sale = $conn->prepare("INSERT INTO sales(branch_id, big_trays_sold, small_trays_sold, sale_datetime) VALUES(?,?,?,NOW())");
    $stmt_sale->bind_param("iii",$branch_id,$big_trays_sold,$small_trays_sold);
    $stmt_sale->execute();

    $success_sale = "✅ Sale recorded successfully!";

    $low_threshold = 5;
    if($new_big <= $low_threshold) $stock_alerts[] = "⚠ Low Big Trays Stock: Only $new_big left!";
    if($new_small <= $low_threshold) $stock_alerts[] = "⚠ Low Small Trays Stock: Only $new_small left!";
}

/* ----------------------
   HANDLE REQUEST TO ADMIN
----------------------- */
$success_request = '';
$error_request = '';

if(isset($_POST['request_admin'])){
    $request_big   = (int)$_POST['request_big_trays'];
    $request_small = (int)$_POST['request_small_trays'];
    $message       = trim($_POST['message']);

    if($request_big < 0 || $request_small < 0 || empty($message)){
        $error_request = "⚠ Please fill all fields correctly.";
    } else {
        $stmt_req = $conn->prepare("INSERT INTO requests(branch_id, big_trays, small_trays, message, status, request_datetime) VALUES (?,?,?,?, 'pending', NOW())");
        $stmt_req->bind_param("iiis", $branch_id, $request_big, $request_small, $message);
        $stmt_req->execute();
        $success_request = "📨 Request sent successfully!";
    }
}

/* ----------------------
   FETCH SALES
----------------------- */
$stmt = $conn->prepare("SELECT * FROM sales WHERE branch_id=? ORDER BY sale_datetime DESC");
$stmt->bind_param("i",$branch_id);
$stmt->execute();
$result = $stmt->get_result();
$sales = $result->fetch_all(MYSQLI_ASSOC);

/* ----------------------
   CALCULATE TOTALS
----------------------- */
$total_big_trays = 0;
$total_small_trays = 0;
$total_income = 0;

foreach($sales as $s){
    $total_big_trays   += $s['big_trays_sold'];
    $total_small_trays += $s['small_trays_sold'];
    $total_income += ($s['big_trays_sold'] * $price_big_tray) + ($s['small_trays_sold'] * $price_small_tray);
}

/* NEW EGG CALCULATION */
$total_eggs = ($total_big_trays * 12) + ($total_small_trays * 6);

/* ----------------------
   FETCH ADMIN REPLIES
----------------------- */
$stmt_reply = $conn->prepare("SELECT * FROM requests WHERE branch_id=? AND admin_reply IS NOT NULL ORDER BY request_datetime DESC");
$stmt_reply->bind_param("i", $branch_id);
$stmt_reply->execute();
$admin_replies = $stmt_reply->get_result()->fetch_all(MYSQLI_ASSOC);

if(!isset($_SESSION['shown_replies'])) $_SESSION['shown_replies'] = [];
?>