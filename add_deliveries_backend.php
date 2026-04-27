<?php
session_start();
include __DIR__ . '/../includes/db.php'; // path to your db.php

/* ===== SECURITY ===== */
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=="client"){
    header("Location: ../login.php");
    exit();
}


$user = $_SESSION['user'];
$branch_id   = $user['branch_id'];
$branch_name = $user['branch_name'] ?? 'Branch';
$user_name   = $user['name'] ?? 'User';

/* ===== FETCH INVENTORY ===== */
$stmt = $conn->prepare("SELECT big_trays, small_trays, egg_pieces FROM inventory WHERE branch_id=? LIMIT 1");
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$inventory = $stmt->get_result()->fetch_assoc();
if (!$inventory) {
    $inventory = ['big_trays' => 0, 'small_trays' => 0, 'egg_pieces' => 0];
}

/* ===== DELIVERIES TOTAL ===== */
$stmt_del = $conn->prepare("SELECT SUM(big_trays) as big, SUM(small_trays) as small FROM deliveries WHERE branch_id=?");
$stmt_del->bind_param("i", $branch_id);
$stmt_del->execute();
$res_del = $stmt_del->get_result()->fetch_assoc();
$delivered_big   = $res_del['big'] ?? 0;
$delivered_small = $res_del['small'] ?? 0;

/* ===== COMBINED INVENTORY ===== */
$big_trays_total   = $inventory['big_trays'] + $delivered_big;
$small_trays_total = $inventory['small_trays'] + $delivered_small;
$egg_pieces_total  = $inventory['egg_pieces'] ?? 0;

/* ===== DAILY MONITOR ===== */
$stmt_day = $conn->prepare("
    SELECT SUM(big_trays) as day_big, SUM(small_trays) as day_small
    FROM deliveries
    WHERE branch_id=? AND DATE(created_at) = CURDATE()
");
$stmt_day->bind_param("i", $branch_id);
$stmt_day->execute();
$day = $stmt_day->get_result()->fetch_assoc();
$day_big  = $day['day_big'] ?? 0;
$day_small= $day['day_small'] ?? 0;
$day_eggs = ($day_big * 12) + ($day_small * 6);

/* ===== WEEKLY MONITOR ===== */
$stmt_week = $conn->prepare("
    SELECT SUM(big_trays) as week_big, SUM(small_trays) as week_small
    FROM deliveries
    WHERE branch_id=? AND YEARWEEK(created_at,1) = YEARWEEK(CURDATE(),1)
");
$stmt_week->bind_param("i", $branch_id);
$stmt_week->execute();
$week = $stmt_week->get_result()->fetch_assoc();
$week_big  = $week['week_big'] ?? 0;
$week_small= $week['week_small'] ?? 0;
$week_eggs = ($week_big * 12) + ($week_small * 6);

/* ===== MONTHLY TOTAL ===== */
$current_month = date('Y-m');
$stmt_month = $conn->prepare("
    SELECT SUM(big_trays) as total_big, SUM(small_trays) as total_small
    FROM deliveries
    WHERE branch_id=? AND DATE_FORMAT(created_at,'%Y-%m')=?
");
$stmt_month->bind_param("is", $branch_id, $current_month);
$stmt_month->execute();
$month_result = $stmt_month->get_result()->fetch_assoc();
$total_big_month   = $month_result['total_big'] ?? 0;
$total_small_month = $month_result['total_small'] ?? 0;
$total_eggs_month  = ($total_big_month * 12) + ($total_small_month * 6);

/* ===== RECENT DELIVERIES ===== */
$stmt_logs = $conn->prepare("
    SELECT * FROM deliveries
    WHERE branch_id=?
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt_logs->bind_param("i", $branch_id);
$stmt_logs->execute();
$delivery_logs = $stmt_logs->get_result()->fetch_all(MYSQLI_ASSOC);

/* ===== CHART DATA ===== */
$chart_labels = [];
$chart_data   = [];
$chart_query = $conn->query("
    SELECT DATE(created_at) as d, SUM(big_trays*12 + small_trays*6) as eggs
    FROM deliveries
    WHERE branch_id=$branch_id
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) ASC
    LIMIT 7
");
while ($row = $chart_query->fetch_assoc()) {
    $chart_labels[] = $row['d'];
    $chart_data[]   = $row['eggs'];
}

/* ===== LOW STOCK ALERT ===== */
$low_stock_alert = "";
if ($big_trays_total < 10) { $low_stock_alert .= "⚠️ Low Big Tray stock "; }
if ($small_trays_total < 20) { $low_stock_alert .= "⚠️ Low Small Tray stock"; }

/* ===== RETURN DATA ===== */
return [
    'branch_name' => $branch_name,
    'user_name' => $user_name,
    'inventory' => $inventory,
    'day_eggs' => $day_eggs,
    'week_eggs' => $week_eggs,
    'total_eggs_month' => $total_eggs_month,
    'delivery_logs' => $delivery_logs,
    'chart_labels' => $chart_labels,
    'chart_data' => $chart_data,
    'low_stock_alert' => $low_stock_alert
];
?>