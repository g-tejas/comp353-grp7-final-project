<?php
session_start();
include 'includes/dbh.inc.php'; 

// user logged in?
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$member_id = $_SESSION['user'];
$friend_id = $_POST['friend_id'];

// Delete the entry in member_relationship
$query = "DELETE FROM member_relationship WHERE (Member_1_ID = ? AND Member_2_ID = ?) OR (Member_1_ID = ? AND Member_2_ID = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $member_id, $friend_id, $friend_id, $member_id);
$stmt->execute();

// Delete the friendship in profile_accissibility table
$query = "DELETE FROM profile_accessibility WHERE (Member_ID = ? AND Target_ID = ?) OR (Member_ID = ? AND Target_ID = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $member_id, $friend_id, $friend_id, $member_id);
$stmt->execute();

header("Location: friends.php");

$stmt->close();
$conn->close();
?>