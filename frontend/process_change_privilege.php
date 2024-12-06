<?php
session_start();
include 'includes/dbh.inc.php'; // Ensure this path is correct



$member_id = $_POST['member_id'];
$privilege = $_POST['privilege'];

// Update the member's privilege level in the database
$query = "UPDATE member SET Privilege_Level = ? WHERE Member_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $privilege, $member_id);

if ($stmt->execute()) {
    echo "Privilege level updated successfully.";
    header("Location: admin_users.php");
    exit();
} else {
    echo "Error updating privilege level: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>