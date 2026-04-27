<?php
include __DIR__ . '/../includes/db.php';

if(isset($_POST['branch_name'])){
    $branch_name = $conn->real_escape_string($_POST['branch_name']);
    $conn->query("INSERT INTO branches (branch_name) VALUES ('$branch_name')");
}
?>