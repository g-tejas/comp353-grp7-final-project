<?php
session_start();
include 'includes/dbh.inc.php'; // Ensure this path is correct



$member_id = $_POST['member_id'];
$status = $_POST['status'];

// Update the member's status in the database
$query = "UPDATE member SET Status = ? WHERE Member_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $status, $member_id);

if ($stmt->execute()) {
    echo "Status updated successfully.";
    header("Location: admin_users.php");
    exit();
} else {
    echo "Error updating status: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>