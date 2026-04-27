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

$success = "";
$error = "";
$receipt = null;

/* ===== RECORD DELIVERY & CREATE RECEIPT ===== */
if($_SERVER['REQUEST_METHOD']=="POST" && isset($_POST['record_delivery'])){
    $branch=(int)$_POST['branch'];
    $bigTrays=max(0,(int)$_POST['big_trays']);
    $smallTrays=max(0,(int)$_POST['small_trays']);

    if(!isset($branches_list[$branch])){
        $error="Invalid branch.";
    } elseif($bigTrays==0 && $smallTrays==0){
        $error="Enter trays.";
    } else {
        $stmt=$conn->prepare("INSERT INTO deliveries (branch_id,big_trays,small_trays,delivery_datetime,created_at) VALUES(?,?,?,NOW(),NOW())");
        $stmt->bind_param("iii",$branch,$bigTrays,$smallTrays);
        $stmt->execute();
        $delivery_id = $stmt->insert_id;
        $stmt->close();

        $total_eggs = ($bigTrays*12)+($smallTrays*6);
        $receipt = [
            "id"=>$delivery_id,
            "branch"=>$branches_list[$branch],
            "big_trays"=>$bigTrays,
            "small_trays"=>$smallTrays,
            "total_eggs"=>$total_eggs,
            "date"=>date("Y-m-d H:i")
        ];

        $success="Delivery recorded! Receipt ready to print.";
    }
}

/* ===== FETCH DELIVERY HISTORY ===== */
$deliveries_history = [];
$result_deliveries = $conn->query("SELECT d.id, d.big_trays, d.small_trays, d.delivery_datetime, b.branch_name 
                                   FROM deliveries d 
                                   JOIN branches b ON d.branch_id=b.id 
                                   ORDER BY d.id DESC");
while($row = $result_deliveries->fetch_assoc()){
    $row['total_eggs'] = ($row['big_trays']*12)+($row['small_trays']*6);
    $deliveries_history[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Purchase Order - Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Verdana;}
body{background:#f0fdf4;color:#2d6a4f;transition:0.3s;}
body.dark{background:#121821;color:#e0e0e0;}
body.dark .sidebar{background:#0f172a;}
body.dark .sidebar a.active,
body.dark .sidebar a:hover{background:#2563eb;color:#fff;}
body.dark form input,body.dark form select{background:#334155;color:#e0e0e0;border:1px solid #555;}
body.dark form button{background:#2563eb;color:#fff;}

.wrapper{display:flex;min-height:100vh;}
.sidebar{width:240px;background:#38b000;color:#fff;padding:25px;display:flex;flex-direction:column;justify-content:space-between;position:fixed;top:0;left:0;height:100vh;}
.sidebar a{padding:12px;margin-bottom:12px;background:#2d6a4f;color:#fff;text-decoration:none;border-radius:12px;font-weight:bold;display:flex;align-items:center;gap:10px;transition:0.3s;}
.sidebar a.active{background:#70d6ff;color:#000;}
.sidebar a:hover{transform:translateX(5px);}
.sidebar .logout{background:#d00000;margin-top:20px;}
.sidebar .logout:hover{background:#9d0208;}

.main-content{flex:1;margin-left:260px;padding:30px;}
h1{margin-bottom:20px;}
.alert{padding:12px;border-radius:12px;margin-bottom:20px;font-weight:600;}
.alert.success{background:#d1fae5;color:#16a34a;}
.alert.error{background:#fcd5ce;color:#b91c1c;}

form input,form select{width:100%;padding:12px;border-radius:12px;border:1px solid #ccc;margin-bottom:12px;font-size:16px;}
form button{padding:12px 20px;background:#38b000;color:#fff;border:none;border-radius:12px;font-weight:bold;cursor:pointer;transition:0.3s;font-size:16px;}
form button:hover{background:#2d6a4f;}

#darkToggle{position:fixed;top:15px;right:15px;padding:10px 18px;background:#334155;color:#fff;border:none;border-radius:10px;cursor:pointer;z-index:1000;}
#darkToggle:hover{background:#2563eb;}

.receipt{background:#fff;padding:20px;border-radius:15px;margin-top:20px;box-shadow:0 10px 20px rgba(0,0,0,0.1);}
.print-btn{margin-top:20px;padding:10px 20px;border:none;background:#38b000;color:#fff;border-radius:10px;font-weight:bold;cursor:pointer;}
.print-btn:hover{background:#2d6a4f;}

table{width:100%;border-collapse:collapse;margin-top:20px;background:#fff;border-radius:12px;overflow:hidden;}
th,td{padding:10px;text-align:center;border-bottom:1px solid #ddd;font-size:14px;}
th{background:#38b000;color:#fff;}
tr:nth-child(even){background:#f0fdf4;}

@media(max-width:768px){.main-content{margin-left:0;padding:20px;}}
</style>
</head>
<body>
<button id="darkToggle">🌙 Dark Mode</button>
<div class="wrapper">
    
<div class="sidebar">
    <div>
        <h2>Admin Panel</h2>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="branches.php"><i class="fas fa-store"></i> Branches</a>
        <a href="deliveries.php"><i class="fas fa-truck"></i> Deliveries</a>
        <a href="purchase_order.php" class="active"><i class="fas fa-file-invoice"></i> Purchase Orders</a>
        <a href="sales.php"><i class="fas fa-chart-line"></i> Sales Report</a>
        <a href="reports.php"><i class="fas fa-file-alt"></i> Reports</a>
        <a href="stocks.php"><i class="fas fa-boxes"></i> Stocks</a>
        <a href="users.php"><i class="fas fa-users"></i> Users</a>
    </div>
    <a href="../index.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
<h1>Purchase Order Management</h1>
<?php if($success) echo "<div class='alert success'>$success</div>"; ?>
<?php if($error) echo "<div class='alert error'>$error</div>"; ?>

<div class="card">
<h2>Record Delivery & Generate PO</h2>
<form method="post">
<label>Branch:</label>
<select name="branch">
<?php foreach($branches_list as $id=>$name): ?>
<option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
<?php endforeach; ?>
</select>
<label>Big Tray (1 dozen):</label><input type="number" name="big_trays" min="0" value="0">
<label>Small Tray (Half dozen):</label><input type="number" name="small_trays" min="0" value="0">
<button type="submit" name="record_delivery">Deliver Eggs</button>
</form>
</div>

<?php if($receipt): ?>
<div class="receipt" id="receipt">

    <h2 style="text-align:center;">FRESH FARM EGG SUPPLY</h2>
    <p style="text-align:center;">Purchase Order Receipt</p>
    <hr style="margin:15px 0;">

    <p><strong>PO Number:</strong> PO-<?= $receipt['id'] ?></p>
    <p><strong>Status:</strong> Approved</p>
    <p><strong>Date Issued:</strong> <?= $receipt['date'] ?></p>
    <p><strong>Prepared By:</strong> <?= $_SESSION['user']['name'] ?></p>

    <hr style="margin:15px 0;">

    <h3>Branch Details</h3>
    <p><strong>Branch Name:</strong> <?= htmlspecialchars($receipt['branch']) ?></p>

    <hr style="margin:15px 0;">

    <h3>Order Details</h3>
    <p><strong>Big Trays:</strong> <?= $receipt['big_trays'] ?></p>
    <p><strong>Small Trays:</strong> <?= $receipt['small_trays'] ?></p>
    <p><strong>Total Trays:</strong> <?= $receipt['big_trays'] + $receipt['small_trays'] ?></p>
    <p><strong>Total Eggs:</strong> <?= $receipt['total_eggs'] ?> pcs</p>

    <hr style="margin:15px 0;">

    <h3>Remarks</h3>
    <p>Egg stocks prepared for scheduled branch delivery.</p>

    <br><br>
    <div style="display:flex;justify-content:space-between;">
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
<?php endif; ?>

<h2>Past Purchase Orders</h2>
<table>
<tr>
<th>PO Number</th>
<th>Branch</th>
<th>Big Trays</th>
<th>Small Trays</th>
<th>Total Eggs</th>
<th>Date</th>
<th>Print</th>
</tr>
<?php if(!empty($deliveries_history)): foreach($deliveries_history as $d): ?>
<tr>
<td>PO-<?= $d['id'] ?></td>
<td><?= htmlspecialchars($d['branch_name']) ?></td>
<td><?= $d['big_trays'] ?></td>
<td><?= $d['small_trays'] ?></td>
<td><?= $d['total_eggs'] ?> pcs</td>
<td><?= date("Y-m-d H:i", strtotime($d['delivery_datetime'])) ?></td>
<td><button class="print-btn" onclick="printPO(<?= $d['id'] ?>)">Print</button></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="7">No past purchase orders found.</td></tr>
<?php endif; ?>
</table>

</div>
</div>

<script>
const toggleBtn = document.getElementById('darkToggle');
// Load saved mode
if(localStorage.getItem('darkMode') === 'enabled'){
    document.body.classList.add('dark');
    toggleBtn.textContent = "☀️ Light Mode";
}
// Toggle dark mode
toggleBtn.addEventListener('click', () => {
    document.body.classList.toggle('dark');
    const isDark = document.body.classList.contains('dark');
    toggleBtn.textContent = isDark ? "☀️ Light Mode" : "🌙 Dark Mode";
    localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
});

function printPO(id){
    window.open('print_po.php?id='+id,'_blank');
}
</script>
</body>
</html> 