<?php
session_start();
include 'includes/db.php';

/* SECURITY CHECK */
if(!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'client'){
    header("Location: ../index.php");
    exit();
}

$user = $_SESSION['user'];
$user_id     = $user['id'] ?? null;

$success_msg = '';
$error_msg = '';

/* HANDLE PROFILE UPDATE */
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])){
    $username = trim($_POST['username'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $contact  = trim($_POST['contact'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');
    
    if(empty($username)){
        $error_msg = "Username cannot be empty.";
    } elseif(!empty($password) && $password !== $confirm){
        $error_msg = "Passwords do not match.";
    } else {
        $photo_path = $user['photo'] ?? null;
        if(!empty($_FILES['photo']['name'])){
            $upload_dir = 'uploads/';
            if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $new_name = 'user_'.$user_id.'_'.time().'.'.$ext;
            $target_file = $upload_dir.$new_name;
            if(move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)){
                $photo_path = $target_file;
            } else {
                $error_msg = "Failed to upload photo.";
            }
        }

        if(!$error_msg && $user_id){
            if(!empty($password)){
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE user SET username=?, fullname=?, email=?, contact=?, password=?, photo=? WHERE id=?");
                $stmt->bind_param("ssssssi", $username, $fullname, $email, $contact, $hash, $photo_path, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE user SET username=?, fullname=?, email=?, contact=?, photo=? WHERE id=?");
                $stmt->bind_param("sssssi", $username, $fullname, $email, $contact, $photo_path, $user_id);
            }

            if($stmt->execute()){
                $success_msg = "Profile updated successfully!";
                $_SESSION['user']['username'] = $username;
                $_SESSION['user']['fullname'] = $fullname;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['contact'] = $contact;
                $_SESSION['user']['photo'] = $photo_path;
                $user = $_SESSION['user'];
            } else {
                $error_msg = "Update failed: ".$conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile - <?php echo htmlspecialchars($user['fullname'] ?? $user['username']); ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{display:flex;background:#f0fdf4;min-height:100vh;}

/* Sidebar */
.sidebar{
    width:220px;background:#38b000;color:#fff;height:100vh;position:fixed;display:flex;flex-direction:column;padding:20px;
}
.sidebar h2{text-align:center;margin-bottom:40px;}
.sidebar a{
    display:flex;align-items:center;padding:12px 15px;margin-bottom:15px;background:#2d6a4f;border-radius:10px;color:#fff;text-decoration:none;font-weight:bold;transition:0.3s;
}
.sidebar a i{margin-right:10px;}
.sidebar a:hover{background:#70d6ff;color:#000;}
.sidebar .logout{background:#d00000;margin-top:auto;}
.sidebar .logout:hover{background:#9d0208;}

/* Main content */
.main{margin-left:220px;padding:25px;flex:1;}

/* Cards */
.card{background:#fff;border-radius:15px;padding:25px;box-shadow:0 5px 15px rgba(0,0,0,0.08);margin-bottom:25px;}
.card h2{color:#2d6a4f;margin-bottom:20px;}

/* Profile Form */
input,textarea,button{padding:12px;margin-bottom:15px;width:100%;border-radius:10px;border:1px solid #ccc;font-size:1em;}
button{background:#38b000;color:#fff;border:none;font-weight:bold;cursor:pointer;transition:0.3s;}
button:hover{background:#2d6a4f;}
.success-msg{color:green;font-weight:bold;margin-bottom:15px;}
.error-msg{color:red;font-weight:bold;margin-bottom:15px;}
.profile-photo{width:120px;height:120px;border-radius:50%;object-fit:cover;margin-bottom:15px;border:2px solid #2d6a4f;display:block;margin-left:auto;margin-right:auto;}
.info-row{margin-bottom:15px;}
.info-label{font-weight:bold;margin-bottom:5px;display:block;}
.branch-info{color:#2d6a4f;font-weight:bold;margin-bottom:15px;text-align:center;}
</style>
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

<div class="main">

<div class="card">
<h2>👤 Client Profile - <?php echo htmlspecialchars($user['fullname'] ?? $user['username']); ?></h2>
<?php if(!empty($user['branch'])): ?>
<p class="branch-info"><i class="fas fa-map-marker-alt"></i> Branch: <?php echo htmlspecialchars($user['branch']); ?></p>
<?php endif; ?>

<?php if($success_msg): ?><div class="success-msg"><?php echo $success_msg; ?></div><?php endif; ?>
<?php if($error_msg): ?><div class="error-msg"><?php echo $error_msg; ?></div><?php endif; ?>

<img src="<?php echo !empty($user['photo']) ? htmlspecialchars($user['photo']) : 'https://via.placeholder.com/120?text=No+Photo'; ?>" alt="Profile Photo" class="profile-photo" id="profilePreview">

<form method="post" enctype="multipart/form-data">
    <div class="info-row">
        <label class="info-label" for="photo"><i class="fas fa-camera"></i> Profile Photo</label>
        <input type="file" name="photo" id="photo" accept="image/*" onchange="previewPhoto(event)">
    </div>

    <div class="info-row">
        <label class="info-label" for="username"><i class="fas fa-user"></i> Username</label>
        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
    </div>

    <div class="info-row">
        <label class="info-label" for="fullname"><i class="fas fa-id-card"></i> Full Name</label>
        <input type="text" name="fullname" id="fullname" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>">
    </div>

    <div class="info-row">
        <label class="info-label" for="email"><i class="fas fa-envelope"></i> Email</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
    </div>

    <div class="info-row">
        <label class="info-label" for="contact"><i class="fas fa-phone"></i> Contact Number</label>
        <input type="text" name="contact" id="contact" value="<?php echo htmlspecialchars($user['contact'] ?? ''); ?>">
    </div>

    <div class="info-row">
        <label class="info-label" for="password"><i class="fas fa-key"></i> New Password (leave blank to keep current)</label>
        <input type="password" name="password" id="password">
    </div>

    <div class="info-row">
        <label class="info-label" for="confirm_password"><i class="fas fa-key"></i> Confirm New Password</label>
        <input type="password" name="confirm_password" id="confirm_password">
    </div>

    <button type="submit" name="update_profile"><i class="fas fa-save"></i> Update Profile</button>
</form>
</div>

</div>

<script>
function previewPhoto(event){
    const reader = new FileReader();
    reader.onload = function(){
        document.getElementById('profilePreview').src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>

</body>
</html>