<?php include 'backend/branches_backend.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Branches - Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',Tahoma,Verdana;}
body{background:#f0fdf4;color:#2d6a4f;transition:0.3s;}
body.dark{background:#121821;color:#e0e0e0;}
body.dark .sidebar{background:#0f172a;}
body.dark .sidebar a.active,
body.dark .sidebar a:hover{background:#2563eb;color:#fff;}
body.dark .card,
body.dark .card-summary,
body.dark table{background:#1e293b;color:#e0e0e0;}
body.dark th{background:#2563eb;color:#fff;}
body.dark tr:nth-child(even){background:#1e293b;}
body.dark tr:hover{background:#334155;}
body.dark #darkToggle{background:#334155;color:#fff;}

/* Wrapper & Sidebar */
.wrapper{display:flex;min-height:100vh;}
.sidebar{width:240px;background:#38b000;color:#fff;padding:25px;display:flex;flex-direction:column;justify-content:space-between;position:fixed;top:0;left:0;height:100vh;}
.sidebar h2{text-align:center;font-size:1.8rem;margin-bottom:35px;}
.sidebar a{display:flex;align-items:center;gap:10px;padding:12px 18px;margin-bottom:12px;background:#2d6a4f;color:#fff;border-radius:10px;text-decoration:none;transition:0.3s;}
.sidebar a.active,.sidebar a:hover{background:#70d6ff;color:#000;}
.sidebar .logout{background:#d90429;margin-top:auto;}
.sidebar .logout:hover{background:#9b0a20;}

/* Main Content */
.main-content{flex:1;padding:30px 40px;margin-left:260px;}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:35px;}
.header h1{font-size:2.3rem;}
.header p{font-size:1.05rem;color:#52796f;}
#darkToggle{padding:8px 15px;border:none;border-radius:6px;background:#334155;color:#fff;cursor:pointer;}

/* Cards */
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:25px;margin-bottom:35px;}
.card-summary{background:#fff;padding:25px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.08);text-align:center;}
.card-summary h3{font-size:1.05rem;margin-bottom:8px;}
.card-summary p{font-size:1.7rem;font-weight:700;}

/* Table */
table{width:100%;border-collapse:collapse;border-radius:12px;overflow:hidden;}
th,td{padding:14px 12px;text-align:center;border-bottom:1px solid #ddd;font-size:0.95rem;}
th{background:#38b000;color:#fff;font-size:1rem;}
tr:nth-child(even){background:#f0fdf4;}
tr:hover{background:#e5f3e8;}

/* Buttons */
.view-btn{background:#38b000;color:#fff;border:none;padding:10px 15px;border-radius:8px;cursor:pointer;transition:0.3s;}
.view-btn:hover{background:#2d6a4f;}
.delete-btn{background:#d90429;color:#fff;border:none;padding:10px 14px;border-radius:8px;cursor:pointer;margin-left:8px;transition:0.3s;}
.delete-btn:hover{background:#9b0a20;}
.add-btn{background:#38b000;color:#fff;border:none;padding:10px 16px;border-radius:8px;cursor:pointer;font-weight:600;transition:0.3s;}
.add-btn:hover{background:#2d6a4f;}

/* Modal */
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);justify-content:center;align-items:center;z-index:1000;}
.modal-content{background:#fff;width:90%;max-width:900px;max-height:85vh;overflow-y:auto;border-radius:15px;padding:25px;position:relative;}
.close-btn{position:absolute;top:15px;right:20px;font-size:26px;cursor:pointer;color:#d90429;}
.summary-box{display:flex;gap:15px;justify-content:center;flex-wrap:wrap;margin:20px 0;}
.summary-card{background:#e0f2f1;padding:15px;border-radius:12px;min-width:160px;text-align:center;transition:0.3s;}
.summary-card:hover{background:#b2dfdb;}

/* Responsive */
@media(max-width:768px){
    .sidebar{position:relative;width:100%;height:auto;flex-direction:row;overflow-x:auto;padding:15px;}
    .main-content{margin-left:0;padding:20px;}
    .cards{grid-template-columns:repeat(auto-fit,minmax(140px,1fr));}
    #darkToggle{margin-top:10px;}
}
</style>
</head>
<body>
<div class="wrapper">

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php"><i class="fas fa-store"></i> Dashboard</a>
    <a href="branches.php" class="active"><i class="fas fa-tachometer-alt"></i> Branches</a>
    <a href="deliveries.php"><i class="fas fa-truck"></i> Deliveries</a>
    <a href="purchase_order.php"><i class="fas fa-file-invoice"></i> Purchase Orders</a>
    <a href="sales.php"><i class="fas fa-chart-line"></i> Sales Report</a>
    <a href="reports.php"><i class="fas fa-file-alt"></i> Reports</a>
    <a href="stocks.php"><i class="fas fa-boxes"></i> Stocks</a>
    <a href="users.php"><i class="fas fa-users"></i> Users</a>
    <a href="../index.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="header">
        <div>
            <h1>Branches Management</h1>
            <p>Click “View” to see deliveries or “Delete” to remove a branch</p>
        </div>
        <button id="darkToggle">🌙 Dark Mode</button>
    </div>

    <!-- KPI Cards -->
    <div class="cards">
        <div class="card-summary"><h3>Total Branches</h3><p><?= count($branches) ?></p></div>
        <div class="card-summary"><h3>Total Deliveries</h3><p><?= $total_deliveries ?></p></div>
        <div class="card-summary"><h3>Total Eggs Per Piece</h3><p><?= $total_eggs ?></p></div>
    </div>

    <!-- Branches Table -->
    <div class="card">
        <h2>Branch List</h2>

        <!-- Add Branch Button -->
        <div style="display:flex; justify-content:flex-end; margin-bottom:15px;">
            <button class="add-btn" onclick="openAddBranchModal()">
                <i class="fas fa-plus"></i> Add Branch
            </button>
        </div>

        <table>
            <tr><th>#</th><th>Branch Name</th><th>Action</th></tr>
            <?php $i=1; foreach($branches as $b): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($b['branch_name']) ?></td>
                <td>
                    <button class="view-btn" onclick="openDeliveries(<?= $b['id'] ?>)">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button class="delete-btn" onclick="deleteBranch(<?= $b['id'] ?>)">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="deliveriesModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2 id="modalBranchName"></h2>
        <div class="summary-box" id="modalSummary"></div>
        <div id="modalTable"></div>
    </div>
</div>

<script>
// Dark Mode
const body = document.body;
const toggle = document.getElementById('darkToggle');
if(localStorage.getItem('darkMode')==='enabled'){body.classList.add('dark'); toggle.textContent='☀️ Light Mode';}
toggle.addEventListener('click', ()=>{
    body.classList.toggle('dark');
    if(body.classList.contains('dark')){
        localStorage.setItem('darkMode','enabled'); toggle.textContent='☀️ Light Mode';
    }else{
        localStorage.setItem('darkMode','disabled'); toggle.textContent='🌙 Dark Mode';
    }
});

// Modal
function openDeliveries(id){
    fetch("backend/branches_backend.php?ajax_branch="+id)
    .then(r=>r.json())
    .then(d=>{
        document.getElementById("modalBranchName").innerText=d.branch.branch_name+" – Delivery Details";
        document.getElementById("modalSummary").innerHTML=`
            <div class="summary-card"><h3>Big Trays</h3><p>${d.summary.big}</p></div>
            <div class="summary-card"><h3>Small Trays</h3><p>${d.summary.small}</p></div>
            <div class="summary-card"><h3>Total Eggs</h3><p>${d.summary.eggs}</p></div>`;
        let t=`<table><tr><th>ID</th><th>Big</th><th>Small</th><th>Total Eggs</th><th>Date</th></tr>`;
        if(d.deliveries.length){
            d.deliveries.forEach(x=>{
                t+=`<tr>
                    <td>${x.id}</td>
                    <td>${x.big_trays}</td>
                    <td>${x.small_trays}</td>
                    <td>${x.big_trays*12 + x.small_trays*6}</td>
                    <td>${x.delivery_datetime}</td>
                </tr>`;
            });
        } else { t+=`<tr><td colspan="5">No deliveries found</td></tr>`; }
        t+=`</table>`;
        document.getElementById("modalTable").innerHTML=t;
        document.getElementById("deliveriesModal").style.display="flex";
    });
}
function closeModal(){document.getElementById("deliveriesModal").style.display="none";}

// Add Branch
function openAddBranchModal(){
    let branchName = prompt("Enter branch name:");
    if(branchName && branchName.trim()!==""){
        fetch("backend/add_branch.php", {
            method:"POST",
            headers:{"Content-Type":"application/x-www-form-urlencoded"},
            body:"branch_name="+encodeURIComponent(branchName)
        })
        .then(res=>res.text())
        .then(()=>{ alert("Branch added successfully!"); location.reload(); });
    }
}

// Delete Branch
function deleteBranch(id){
    if(confirm("Are you sure you want to delete this branch?")){
        fetch("backend/delete_branch.php?id="+id)
        .then(res=>res.text())
        .then(()=>{ alert("Branch deleted successfully!"); location.reload(); });
    }
}
</script>
</body>
</html>