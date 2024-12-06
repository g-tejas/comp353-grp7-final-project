<?php
session_start();
include 'includes/dbh.inc.php'; // Ensure this path is correct

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$member_id = $_SESSION['user'];
$friend_id = $_POST['friend_id'];

// Delete the friendship from the member_relationship table
$query = "DELETE FROM member_relationship WHERE (Member_1_ID = ? AND Member_2_ID = ?) OR (Member_1_ID = ? AND Member_2_ID = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $member_id, $friend_id, $friend_id, $member_id);

if ($stmt->execute()) {
    echo "Friend removed successfully.";
    header("Location: friends.php");
    exit();
} else {
    echo "Error removing friend: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>