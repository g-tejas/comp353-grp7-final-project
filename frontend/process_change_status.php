<?php
session_start();
include 'includes/dbh.inc.php'; 



$member_id = $_POST['member_id'];
$status = $_POST['status'];

// Update member status in the database
$query = "UPDATE member SET Status = ? WHERE Member_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $status, $member_id);

if ($stmt->execute()) {
    header("Location: admin_users.php");
    exit();
} else {
    echo "Error updating status: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>