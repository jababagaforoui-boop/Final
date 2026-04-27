<?php
// ===== SESSION =====
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// ===== INCLUDE DATABASE =====
$path_to_db = __DIR__ . '/../includes/db.php'; // correct path for admin/includes

if(!file_exists($path_to_db)){
    die("Database file not found! Checked path: $path_to_db");
}

include $path_to_db;

/* ===== AUTO ADMIN LOGIN (LOCAL TESTING) ===== */
if(!isset($_SESSION['user'])){
    $_SESSION['user'] = [
        "role"=>"admin",
        "name"=>"Administrator"
    ];
}

/* ===== SETTINGS ===== */
$month = date('Y-m');

/* ===== FETCH BRANCHES ===== */
$branches_list = [];
$result_branches = $conn->query("SELECT id, branch_name FROM branches ORDER BY branch_name ASC");
while($row = $result_branches->fetch_assoc()){
    $branches_list[$row['id']] = $row['branch_name'];
}

/* ===== FETCH OR CREATE MONTHLY STOCK ===== */
$stock_query = $conn->query("SELECT * FROM stocks WHERE month='$month' LIMIT 1");
if($stock_query->num_rows === 0){
    $conn->query("INSERT INTO stocks (month,big_trays,small_trays) VALUES ('$month',0,0)");
    $stock_query = $conn->query("SELECT * FROM stocks WHERE month='$month' LIMIT 1");
}
$stock = $stock_query->fetch_assoc();

$success="";
$error="";

/* ===== ADD STOCK ===== */
if($_SERVER['REQUEST_METHOD']=="POST" && isset($_POST['record_delivery'])){
    $branch=(int)$_POST['branch'];
    $bigTrays=max(0,(int)$_POST['big_trays']);
    $smallTrays=max(0,(int)$_POST['small_trays']);

    if(!isset($branches_list[$branch])){
        $error="Invalid branch.";
    } elseif($bigTrays==0 && $smallTrays==0){
        $error="Enter trays.";
    } elseif($bigTrays>$stock['big_trays'] || $smallTrays>$stock['small_trays']){
        $error="Not enough stock!";
    } else {
        // Insert delivery
        $stmt=$conn->prepare("INSERT INTO deliveries (branch_id,big_trays,small_trays,delivery_datetime,created_at) VALUES(?,?,?,NOW(),NOW())");
        $stmt->bind_param("iii",$branch,$bigTrays,$smallTrays);
        $stmt->execute(); $stmt->close();

        // Update branch inventory
        $stmt=$conn->prepare("SELECT big_trays,small_trays FROM inventory WHERE branch_id=? LIMIT 1");
        $stmt->bind_param("i",$branch); $stmt->execute(); $res=$stmt->get_result(); $inv=$res->fetch_assoc();
        if($inv){
            $new_big=$inv['big_trays']+$bigTrays;
            $new_small=$inv['small_trays']+$smallTrays;
            $stmt2=$conn->prepare("UPDATE inventory SET big_trays=?,small_trays=?,updated_at=NOW() WHERE branch_id=?");
            $stmt2->bind_param("iii",$new_big,$new_small,$branch); $stmt2->execute();
        } else {
            $stmt2=$conn->prepare("INSERT INTO inventory (branch_id,big_trays,small_trays,created_at,updated_at) VALUES(?,?,?,NOW(),NOW())");
            $stmt2->bind_param("iii",$branch,$bigTrays,$smallTrays); $stmt2->execute();
        }

        // Update admin stock
        $stock['big_trays']-=$bigTrays;
        $stock['small_trays']-=$smallTrays;
        $conn->query("UPDATE stocks SET big_trays={$stock['big_trays']}, small_trays={$stock['small_trays']} WHERE month='$month'");
        $success="Delivery recorded!";
    }
}

/* ===== FETCH DELIVERIES ===== */
$deliveries=[]; 
$result=$conn->query("SELECT d.*,b.branch_name FROM deliveries d JOIN branches b ON d.branch_id=b.id ORDER BY d.created_at DESC");
while($row=$result->fetch_assoc()){$deliveries[]=$row;}

/* ===== MONTHLY TOTALS ===== */
$totalData=$conn->query("SELECT COUNT(*) as total_deliveries,SUM(big_trays) as total_big,SUM(small_trays) as total_small FROM deliveries WHERE DATE_FORMAT(created_at,'%Y-%m')='$month'")->fetch_assoc();
$total_deliveries=$totalData['total_deliveries'] ?? 0;
$total_big=$totalData['total_big'] ?? 0;
$total_small=$totalData['total_small'] ?? 0;
$totalEggsMonth=($total_big*12)+($total_small*6);

/* ===== BRANCH CHART DATA ===== */
$chartLabels=[];$chartBig=[];$chartSmall=[];$chartTotal=[];
foreach($branches_list as $id=>$name){
    $data = $conn->query("SELECT SUM(big_trays) as big, SUM(small_trays) as small FROM deliveries WHERE branch_id=$id AND DATE_FORMAT(created_at,'%Y-%m')='$month'")->fetch_assoc();
    $chartLabels[]=$name;
    $chartBig[]=$data['big'] ?? 0;
    $chartSmall[]=$data['small'] ?? 0;
    $chartTotal[]=(($data['big'] ?? 0)*12)+(($data['small'] ?? 0)*6);
}

/* ===== DAILY CHART DATA ===== */
$daysInMonth = date('t'); $dailyLabels=[]; $dailyBig=[]; $dailySmall=[]; $dailyTotal=[];
for($d=1;$d<=$daysInMonth;$d++){
    $day = str_pad($d,2,'0',STR_PAD_LEFT);
    $dailyLabels[]="$month-$day";
    $row = $conn->query("SELECT SUM(big_trays) as big,SUM(small_trays) as small FROM deliveries WHERE DATE(created_at)='$month-$day'")->fetch_assoc();
    $dailyBig[]=$row['big']??0;
    $dailySmall[]=$row['small']??0;
    $dailyTotal[]=(($row['big'] ?? 0)*12)+(($row['small'] ?? 0)*6);
}

/* ===== GOALS ===== */
$goalOrders=100;
$goalDeliveredPercent=min(100, round(($total_deliveries/$goalOrders)*100));
?>