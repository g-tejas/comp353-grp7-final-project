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
$type = $_POST['relationship_type'];

//Update relationship type in database
$query = "UPDATE member_relationship SET Type = ? WHERE (Member_1_ID = ? AND Member_2_ID = ?) OR (Member_1_ID = ? AND Member_2_ID = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("siiii", $type, $member_id, $friend_id, $friend_id, $member_id);

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