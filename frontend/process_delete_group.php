<?php
session_start();
include 'includes/dbh.inc.php'; // Ensure this path is correct



$group_id = $_POST['group_id'];

// Delete the group from the database
$query = "DELETE FROM `group` WHERE Group_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $group_id);

if ($stmt->execute()) {
    echo "Group deleted successfully.";
    header("Location: admin_groups.php");
    exit();
} else {
    echo "Error deleting group: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>