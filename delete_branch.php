<?php
include __DIR__ . '/../includes/db.php';

if(isset($_GET['id'])){
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM branches WHERE id=$id");
}
?>