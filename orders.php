<?php
$backend_path = __DIR__ . '/backend/orders_backend.php';
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
<title>Orders - <?php echo htmlspecialchars($branch_name); ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{display:flex;background:#f0fdf4;min-height:100vh;}
.sidebar{width:220px;background:#38b000;color:#fff;height:100vh;position:fixed;display:flex;flex-direction:column;padding:20px;}
.sidebar h2{margin-bottom:40px;font-size:1.6em;text-align:center;}
.sidebar a{display:block;padding:12px 20px;margin-bottom:15px;background:#2d6a4f;border-radius:10px;color:#fff;text-decoration:none;font-weight:bold;transition:0.3s;}
.sidebar a:hover{background:#70d6ff;color:#000;transform:translateX(5px);}
.sidebar .logout{background:#d00000;margin-top:auto;}
.sidebar .logout:hover{background:#9d0208;transform:translateX(5px);}
.main-content{margin-left:220px;padding:30px;flex:1;}
.card{background:#fff;border-radius:20px;padding:30px;box-shadow:0 10px 30px rgba(0,0,0,0.1);margin-bottom:25px;}
.card h2{color:#2d6a4f;margin-bottom:25px;font-size:1.4em;}
.alert-msg{color:#b91c1c;font-weight:bold;margin-bottom:20px;}
.success-msg{color:green;margin-bottom:15px;font-weight:bold;}
.error-msg{color:red;margin-bottom:15px;font-weight:bold;}

/* KPI / Stock Cards */
.dashboard{display:flex;gap:20px;flex-wrap:wrap;margin-bottom:30px;}
.stock-card{flex:1 1 200px;background:#22c55e;color:#fff;border-radius:15px;padding:20px;text-align:center;box-shadow:0 4px 12px rgba(0,0,0,0.08);transition:0.3s;}
.stock-card:hover{box-shadow:0 6px 20px rgba(0,0,0,0.15);}
.stock-card i{font-size:28px;margin-bottom:10px;display:block;}
.stock-card.green{background:#22c55e;}
.stock-card.yellow{background:#facc15;color:#000;}
.stock-card.red{background:#ef4444;color:#fff;}
.stock-card h3{font-size:2.2em;margin-bottom:10px;}
.stock-card p{font-size:1.1em;font-weight:bold;}

/* Summary & Forms */
.summary-box{background:#f0fdf4;color:#2d6a4f;padding:20px;border-radius:15px;box-shadow:0 4px 12px rgba(0,0,0,0.1);margin-bottom:20px;}
.section-header{font-size:1.3em;color:#2d6a4f;border-bottom:3px solid #38b000;padding-bottom:10px;margin-bottom:15px;}
input,textarea,button{padding:12px;margin-bottom:15px;width:100%;border-radius:10px;border:1px solid #ccc;font-size:1em;}
button{background:#38b000;color:#fff;border:none;font-weight:bold;cursor:pointer;transition:0.3s;}
button:hover{background:#2d6a4f;}
table{width:100%;border-collapse:collapse;margin-top:20px;}
th,td{padding:12px;border-bottom:1px solid #ccc;text-align:center;}
th{background:#38b000;color:#fff;}
tr:hover{background:#f0fdf4;}
.flex-grid{display:flex;flex-wrap:wrap;gap:20px;}
.flex-grid .summary-box{flex:1 1 45%;}
@media(max-width:850px){.sidebar{position:relative;width:100%;height:auto;flex-direction:row;overflow-x:auto;}.sidebar a{margin-bottom:0;margin-right:10px;}.main-content{margin-left:0;}}
</style>
<script>
window.addEventListener('DOMContentLoaded', () => {
    <?php foreach($admin_replies as $reply): 
        $reply_id = $reply['id'];
        if(!in_array($reply_id, $_SESSION['shown_replies'])): 
    ?>
        <?php if($reply['status']=='confirmed'): ?>
            alert("✅ Admin confirmed your request for <?php echo $reply['big_trays']; ?> Big Trays and <?php echo $reply['small_trays']; ?> Small Trays!");
        <?php elseif($reply['status']=='rejected'): ?>
            alert("❌ Admin rejected your request for <?php echo $reply['big_trays']; ?> Big Trays and <?php echo $reply['small_trays']; ?> Small Trays!");
        <?php endif; ?>
        <?php $_SESSION['shown_replies'][] = $reply_id; ?>
    <?php endif; endforeach; ?>
});
</script>
</head>
<body>

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
        <h2>📋 Orders & Sales - <?php echo htmlspecialchars($branch_name); ?></h2>

        <!-- Stock Alerts -->
        <?php if(!empty($stock_alerts)) foreach($stock_alerts as $alert) echo "<div class='alert-msg'>$alert</div>"; ?>

        <!-- KPI Cards -->
        <div class="dashboard">
            <div class="stock-card green"><i class="fas fa-egg"></i><h3><?php echo $total_big_trays; ?></h3><p>Big Trays Sold</p></div>
            <div class="stock-card green"><i class="fas fa-egg"></i><h3><?php echo $total_small_trays; ?></h3><p>Small Trays Sold</p></div>
            <div class="stock-card yellow"><i class="fas fa-box"></i><h3><?php echo $total_eggs; ?></h3><p>Total Eggs Sold</p></div>
            <div class="stock-card green"><i class="fas fa-money-bill"></i><h3>₱<?php echo number_format($total_income,2); ?></h3><p>Total Income</p></div>
        </div>

        <!-- Admin Replies -->
        <?php if(!empty($admin_replies)): ?>
        <div class="flex-grid">
            <?php foreach($admin_replies as $reply): ?>
                <div class="summary-box">
                    <div class="section-header">📬 Admin Reply</div>
                    <strong>Request:</strong> Big: <?php echo $reply['big_trays']; ?>, Small: <?php echo $reply['small_trays']; ?><br>
                    <strong>Message:</strong> <?php echo htmlspecialchars($reply['message']); ?><br>
                    <strong>Status:</strong> <?php echo ucfirst($reply['status']); ?><br>
                    <strong>Admin Reply:</strong> <?php echo htmlspecialchars($reply['admin_reply']); ?><br>
                    <em>Requested on: <?php echo $reply['request_datetime']; ?></em>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Place Sale -->
        <div class="summary-box">
            <div class="section-header">📦 Place Trays Sale</div>
            <?php if($success_sale) echo "<div class='success-msg'>$success_sale</div>"; ?>
            <form method="post">
                <input type="number" name="big_trays_sold" placeholder="Big Trays Sold" min="0" required>
                <input type="number" name="small_trays_sold" placeholder="Small Trays Sold" min="0" required>
                <button type="submit" name="add_sale">Add Sale</button>
            </form>
        </div>

        <!-- Request Admin -->
        <div class="summary-box">
            <div class="section-header">📨 Request Eggs to Admin</div>
            <?php if($success_request) echo "<div class='success-msg'>$success_request</div>"; ?>
            <?php if($error_request) echo "<div class='error-msg'>$error_request</div>"; ?>
            <form method="post">
                <input type="number" name="request_big_trays" placeholder="Big Trays to Request" min="0" required>
                <input type="number" name="request_small_trays" placeholder="Small Trays to Request" min="0" required>
                <textarea name="message" placeholder="Message to admin" required></textarea>
                <button type="submit" name="request_admin">Send Request</button>
            </form>
        </div>

        <!-- Sales History Table -->
        <?php if($sales): ?>
        <div class="summary-box">
            <div class="section-header">📝 Sales History</div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Big Trays</th>
                        <th>Small Trays</th>
                        <th>Eggs</th>
                        <th>Income</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($sales as $s): ?>
                    <tr>
                        <td><?php echo $s['id']; ?></td>
                        <td><?php echo $s['big_trays_sold']; ?></td>
                        <td><?php echo $s['small_trays_sold']; ?></td>
                        <td><?php echo ($s['big_trays_sold']*12 + $s['small_trays_sold']*6); ?></td>
                        <td>₱<?php echo number_format(($s['big_trays_sold']*$price_big_tray)+($s['small_trays_sold']*$price_small_tray),2); ?></td>
                        <td><?php echo $s['sale_datetime']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>