<?php
session_start();
include 'includes/db.php';

if(!isset($_GET['id'])){
    die("No Purchase Order ID provided.");
}

$id = (int)$_GET['id'];

/* ===== FETCH SINGLE DELIVERY ===== */
$stmt = $conn->prepare("SELECT d.id, d.big_trays, d.small_trays, d.delivery_datetime, b.branch_name 
                        FROM deliveries d 
                        JOIN branches b ON d.branch_id=b.id 
                        WHERE d.id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$delivery = $result->fetch_assoc();
$stmt->close();

if(!$delivery){
    die("Purchase Order not found.");
}

$total_eggs = ($delivery['big_trays']*12)+($delivery['small_trays']*6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Print Purchase Order - PO-<?= $delivery['id'] ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body{font-family:'Segoe UI',Tahoma,Verdana;background:#f0f0f0;color:#000;padding:20px;}
.receipt{background:#fff;padding:30px;border-radius:15px;margin:auto;max-width:700px;box-shadow:0 10px 25px rgba(0,0,0,0.1);}
h2,h3{margin-bottom:10px;}
p{margin:5px 0;font-size:15px;}
hr{margin:15px 0;}
.signature{display:flex;justify-content:space-between;margin-top:50px;}
.signature div{text-align:center;}
.print-btn{margin-top:20px;padding:10px 20px;border:none;background:#38b000;color:#fff;border-radius:10px;font-weight:bold;cursor:pointer;}
.print-btn:hover{background:#2d6a4f;}
@media print{.print-btn{display:none;}}
</style>
</head>
<body>
<div class="receipt" id="receipt">

    <h2 style="text-align:center;">FRESH FARM EGG SUPPLY</h2>
    <p style="text-align:center;">Purchase Order Receipt</p>
    <hr>

    <p><strong>PO Number:</strong> PO-<?= $delivery['id'] ?></p>
    <p><strong>Status:</strong> Approved</p>
    <p><strong>Date Issued:</strong> <?= date("Y-m-d H:i", strtotime($delivery['delivery_datetime'])) ?></p>
    <p><strong>Prepared By:</strong> <?= $_SESSION['user']['name'] ?? 'Administrator' ?></p>

    <hr>
    <h3>Branch Details</h3>
    <p><strong>Branch Name:</strong> <?= htmlspecialchars($delivery['branch_name']) ?></p>

    <hr>
    <h3>Order Details</h3>
    <p><strong>Big Trays:</strong> <?= $delivery['big_trays'] ?></p>
    <p><strong>Small Trays:</strong> <?= $delivery['small_trays'] ?></p>
    <p><strong>Total Trays:</strong> <?= $delivery['big_trays'] + $delivery['small_trays'] ?></p>
    <p><strong>Total Eggs:</strong> <?= $total_eggs ?> pcs</p>

    <hr>
    <h3>Remarks</h3>
    <p>Egg stocks prepared for scheduled branch delivery.</p>

    <div class="signature">
        <div>
            <p>______________________</p>
            <p>Admin Signature</p>
        </div>
        <div>
            <p>______________________</p>
            <p>Branch Receiver</p>
        </div>
    </div>

    <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i> Print Receipt
    </button>
</div>
</body>
</html>