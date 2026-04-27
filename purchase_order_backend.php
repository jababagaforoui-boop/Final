<?php
session_start();
include 'includes/db.php';

/* ===== AUTO ADMIN LOGIN (LOCAL TESTING) ===== */
if(!isset($_SESSION['user'])){
    $_SESSION['user'] = [
        "role"=>"admin",
        "name"=>"Administrator"
    ];
}

/* ===== FETCH BRANCHES ===== */
$branches_list = [];
$result_branches = $conn->query("SELECT id, branch_name FROM branches ORDER BY branch_name ASC");
while($row = $result_branches->fetch_assoc()){
    $branches_list[$row['id']] = $row['branch_name'];
}

/* ===== INITIALIZE VARIABLES ===== */
$success = "";
$error = "";
$receipt = null;

/* ===== RECORD DELIVERY & CREATE RECEIPT ===== */
if($_SERVER['REQUEST_METHOD']=="POST" && isset($_POST['record_delivery'])){
    $branch = (int)$_POST['branch'];
    $bigTrays = max(0,(int)$_POST['big_trays']);
    $smallTrays = max(0,(int)$_POST['small_trays']);

    if(!isset($branches_list[$branch])){
        $error = "Invalid branch.";
    } elseif($bigTrays==0 && $smallTrays==0){
        $error = "Enter trays.";
    } else {
        $stmt = $conn->prepare("INSERT INTO deliveries (branch_id,big_trays,small_trays,delivery_datetime,created_at) VALUES(?,?,?,NOW(),NOW())");
        $stmt->bind_param("iii",$branch,$bigTrays,$smallTrays);
        $stmt->execute();
        $delivery_id = $stmt->insert_id;
        $stmt->close();

        $total_eggs = ($bigTrays*12) + ($smallTrays*6);
        $receipt = [
            "id" => $delivery_id,
            "branch" => $branches_list[$branch],
            "big_trays" => $bigTrays,
            "small_trays" => $smallTrays,
            "total_eggs" => $total_eggs,
            "date" => date("Y-m-d H:i")
        ];

        $success = "Delivery recorded! Receipt ready to print.";
    }
}

/* ===== FETCH DELIVERY HISTORY ===== */
$deliveries_history = [];
$result_deliveries = $conn->query("SELECT d.id, d.big_trays, d.small_trays, d.delivery_datetime, b.branch_name 
                                   FROM deliveries d 
                                   JOIN branches b ON d.branch_id=b.id 
                                   ORDER BY d.id DESC");
while($row = $result_deliveries->fetch_assoc()){
    $row['total_eggs'] = ($row['big_trays']*12) + ($row['small_trays']*6);
    $deliveries_history[] = $row;
}
?>