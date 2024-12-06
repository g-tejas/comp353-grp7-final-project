<?php
session_start();
include 'includes/dbh.inc.php';

// user logged in?
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user'];
$friend_id = $_POST['friend_id'];
$accessibility = $_POST['privacy'];

$query = "UPDATE profile_accessibility SET Accessibility = ? WHERE Member_ID = ? AND Target_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sii", $accessibility, $member_id, $friend_id);


if ($stmt->execute()) {
    echo "Relationship type updated successfully.";
    header("Location: friend_info.php?friend_id=" . $friend_id);
    exit();
} else {
    echo "Error updating relationship type: " . $conn->error;
}

$stmt->close();
$conn->close();
?>