<?php
session_start();
include 'includes/db.php';

/* ----------------------
   SECURITY CHECK
----------------------- */
if(!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'client'){
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$branch_id   = $user['branch_id'];
$branch_name = $user['branch_name'] ?? 'Branch';

/* ----------------------
   EGGS PER TRAY
----------------------- */
$big_tray_eggs = 12;
$small_tray_eggs = 6;

/* ----------------------
   FETCH INVENTORY
----------------------- */
$stmt = $conn->prepare("SELECT * FROM inventory WHERE branch_id=?");
$stmt->bind_param("i",$branch_id);
$stmt->execute();
$inventory = $stmt->get_result()->fetch_assoc();

$big_remaining   = $inventory['big_trays'] ?? 0;
$small_remaining = $inventory['small_trays'] ?? 0;

/* ----------------------
   FETCH SALES
----------------------- */
$stmt_sales = $conn->prepare("SELECT SUM(big_trays_sold) AS big_sold, SUM(small_trays_sold) AS small_sold FROM sales WHERE branch_id=?");
$stmt_sales->bind_param("i",$branch_id);
$stmt_sales->execute();
$sales = $stmt_sales->get_result()->fetch_assoc();
$total_big_sold   = $sales['big_sold'] ?? 0;
$total_small_sold = $sales['small_sold'] ?? 0;

/* ----------------------
   FETCH RETURNS
----------------------- */
$stmt_ret = $conn->prepare("SELECT SUM(big_trays) AS big_returned, SUM(small_trays) AS small_returned FROM returns WHERE branch_id=?");
$stmt_ret->bind_param("i",$branch_id);
$stmt_ret->execute();
$ret = $stmt_ret->get_result()->fetch_assoc();
$total_big_returned   = $ret['big_returned'] ?? 0;
$total_small_returned = $ret['small_returned'] ?? 0;

/* ----------------------
   CALCULATIONS
----------------------- */
$eggs_sold =
    ($total_big_sold * $big_tray_eggs) +
    ($total_small_sold * $small_tray_eggs);

$eggs_returned =
    ($total_big_returned * $big_tray_eggs) +
    ($total_small_returned * $small_tray_eggs);

$total_eggs_remaining =
    ($big_remaining * $big_tray_eggs) +
    ($small_remaining * $small_tray_eggs);

/* ----------------------
   PROFIT
----------------------- */
$big_price = 144;
$small_price = 36;
$total_profit = ($total_big_sold * $big_price) + ($total_small_sold * $small_price);

/* ----------------------
   LOW STOCK ALERTS
----------------------- */
$stock_alerts = [];
$low_threshold = 5;
if($big_remaining <= $low_threshold) $stock_alerts[] = "⚠ Low Big Trays Stock: Only $big_remaining left!";
if($small_remaining <= $low_threshold) $stock_alerts[] = "⚠ Low Small Trays Stock: Only $small_remaining left!";

/* ----------------------
   SEND DATA TO FRONTEND
----------------------- */
$data = [
    'branch_name' => $branch_name,
    'big_remaining' => $big_remaining,
    'small_remaining' => $small_remaining,
    'total_eggs_remaining' => $total_eggs_remaining,
    'eggs_sold' => $eggs_sold,
    'eggs_returned' => $eggs_returned,
    'total_profit' => $total_profit,
    'stock_alerts' => $stock_alerts,
    'big_tray_eggs' => $big_tray_eggs,
    'small_tray_eggs' => $small_tray_eggs
];

/* ----------------------
   OPTIONAL: If used for JS fetch
----------------------- */
// echo json_encode($data);