<?php
// FRONTEND: client/add_deliveries.php

// Include the backend to fetch all data
$backend_path = __DIR__ . '/backend/add_deliveries_backend.php';
if(file_exists($backend_path)){
    include $backend_path;
} else {
    die("Error: Backend data not available.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Deliveries - <?php echo htmlspecialchars($branch_name); ?></title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* --- your existing CSS --- */
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{display:flex;background:#f0fdf4;min-height:100vh;}
.sidebar{width:220px;background:#38b000;color:#fff;height:100vh;position:fixed;display:flex;flex-direction:column;padding:20px;}
.sidebar h2{margin-bottom:40px;font-size:1.6em;text-align:center;}
.sidebar a{display:block;padding:12px 20px;margin-bottom:15px;background:#2d6a4f;border-radius:10px;color:#fff;text-decoration:none;font-weight:bold;transition:0.3s;}
.sidebar a:hover{background:#70d6ff;color:#000;transform:translateX(5px);}
.sidebar .logout{background:#d00000;margin-top:auto;}
.sidebar .logout:hover{background:#9d0208;transform:translateX(5px);}
.main-content{margin-left:220px;padding:40px;flex:1;}
.card{background:#fff;border-radius:20px;padding:30px;box-shadow:0 10px 30px rgba(0,0,0,0.1);margin-bottom:25px;}
.card h2{color:#2d6a4f;margin-bottom:25px;font-size:1.4em;}
.alert-msg{color:#b91c1c;font-weight:bold;margin-bottom:20px;}
.dashboard{display:flex;gap:20px;flex-wrap:wrap;margin-bottom:30px;}
.stock-card{flex:1 1 200px;background:#22c55e;color:#fff;border-radius:15px;padding:20px;text-align:center;box-shadow:0 4px 12px rgba(0,0,0,0.08);transition:0.3s;}
.stock-card:hover{box-shadow:0 6px 20px rgba(0,0,0,0.15);}
.stock-card i{font-size:28px;margin-bottom:10px;display:block;}
.stock-card.green{background:#22c55e;}
.stock-card.yellow{background:#facc15;color:#000;}
.stock-card.red{background:#ef4444;color:#fff;}
.stock-card h3{font-size:2.2em;margin-bottom:10px;}
.stock-card p{font-size:1.1em;font-weight:bold;}
.stats-card{background:#e0f2fe;color:#0369a1;padding:20px;border-radius:15px;margin-bottom:20px;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
.stats-card h3{margin-bottom:10px;}
.recent-log{border-top:1px solid #ccc;margin-top:25px;padding-top:20px;}
.recent-log h3{margin-bottom:15px;color:#2d6a4f;}
.recent-log table{width:100%;border-collapse:collapse;}
.recent-log th,.recent-log td{padding:12px;border-bottom:1px solid #ddd;text-align:center;}
</style>
</head>
<body>

<!-- POPUP NOTIFICATION -->
<?php
if(isset($_GET['new_delivery']) && $_GET['new_delivery']==1){
    echo "<script>alert('📢 New delivery has been added by Admin!');</script>";
}
?>

<div class="sidebar">
<h2>Client Panel</h2>
<a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
<a href="add_deliveries.php"><i class="fas fa-truck"></i> Deliveries</a>
<a href="orders.php"><i class="fas fa-list"></i> Orders</a>
<a href="stocks.php"><i class="fas fa-boxes"></i> Stocks</a>
<a href="returns.php"><i class="fas fa-undo"></i> Returns</a>
<a href="profile.php"><i class="fas fa-user"></i> Profile</a>
<a href="../index.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
<div class="card">
<h2>📦 Add Deliveries - <?php echo htmlspecialchars($branch_name); ?></h2>
<p>Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong>! 👋</p>

<?php if($low_stock_alert) echo "<div class='alert-msg'>$low_stock_alert</div>"; ?>

<div class="dashboard">
<div class="stock-card green"><i class="fas fa-egg"></i><h3><?php echo $inventory['big_trays']; ?></h3><p>Big Trays</p></div>
<div class="stock-card green"><i class="fas fa-egg"></i><h3><?php echo $inventory['small_trays']; ?></h3><p>Small Trays</p></div>
<div class="stock-card green"><i class="fas fa-egg"></i><h3><?php echo $day_eggs; ?></h3><p>Eggs Today</p></div>
<div class="stock-card green"><i class="fas fa-egg"></i><h3><?php echo $week_eggs; ?></h3><p>Eggs This Week</p></div>
<div class="stock-card green"><i class="fas fa-box"></i><h3><?php echo $total_eggs_month; ?></h3><p>Total Eggs This Month</p></div>
</div>

<div class="stats-card">
<h3>Delivery History (Last 7 Days)</h3>
<canvas id="deliveryChart"></canvas>
</div>

<div class="recent-log">
<h3>📝 Recent Deliveries</h3>
<table>
<tr>
<th>Date</th>
<th>Big Trays</th>
<th>Small Trays</th>
<th>Total Eggs</th>
</tr>
<?php if($delivery_logs): ?>
<?php foreach($delivery_logs as $log): ?>
<tr>
<td><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
<td><?php echo $log['big_trays']; ?></td>
<td><?php echo $log['small_trays']; ?></td>
<td><?php echo ($log['big_trays']*12)+($log['small_trays']*6); ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="4">No deliveries recorded yet.</td></tr>
<?php endif; ?>
</table>
</div>

</div>
</div>

<script>
const ctx=document.getElementById('deliveryChart');
new Chart(ctx,{
type:'line',
data:{
labels:<?php echo json_encode($chart_labels); ?>,
datasets:[{
label:'Eggs Delivered',
data:<?php echo json_encode($chart_data); ?>,
borderWidth:3,
borderColor:'#16a34a',
fill:true,
backgroundColor:'rgba(34,197,94,0.2)',
tension:0.3
}]
},
options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}
});
</script>

</body>
</html>