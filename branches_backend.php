<?php
session_start();
include __DIR__ . '/../includes/db.php';

/* ===== AUTO ADMIN LOGIN (LOCAL TESTING) ===== */
if(!isset($_SESSION['user'])){
    $_SESSION['user'] = [
        "role"=>"admin",
        "name"=>"Administrator"
    ];
}

/* ===== AJAX VIEW DELIVERIES ===== */
if(isset($_GET['ajax_branch'])){
    $id = (int)$_GET['ajax_branch'];
    $branch = $conn->query("SELECT * FROM branches WHERE id=$id")->fetch_assoc();
    if(!$branch){ exit; }

    $deliveries = [];
    $summary = ['big'=>0,'small'=>0,'eggs'=>0];

    $res = $conn->query("SELECT * FROM deliveries WHERE branch_id=$id ORDER BY delivery_datetime DESC");
    while($d = $res->fetch_assoc()){
        $deliveries[] = $d;
        $summary['big'] += $d['big_trays'];
        $summary['small'] += $d['small_trays'];
        $summary['eggs'] += ($d['big_trays']*12)+($d['small_trays']*6);
    }

    echo json_encode([
        'branch'=>$branch,
        'summary'=>$summary,
        'deliveries'=>$deliveries
    ]);
    exit;
}

/* ===== FETCH ALL BRANCHES FROM DATABASE ===== */
$branches_result = $conn->query("SELECT * FROM branches ORDER BY branch_name ASC");
$branches = [];
while($row = $branches_result->fetch_assoc()){
    $branches[] = $row;
}

/* ===== DASHBOARD COUNTS ===== */
$total_deliveries = $conn->query("SELECT COUNT(*) t FROM deliveries")->fetch_assoc()['t'];
$total_eggs = $conn->query("SELECT SUM(big_trays*12 + small_trays*6) t FROM deliveries")->fetch_assoc()['t'];
?>