<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$receiver_id = $_SESSION['user'];
$sender_id = $_POST['sender_id'];
$group_id = $_POST['group_id'];

// Check if the user is already a member of the group
$stmt = $conn->prepare("SELECT * FROM group_members WHERE Member_ID = ? AND Group_ID = ?");
$stmt->bind_param("ii", $receiver_id, $group_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "You are already a member of this group.";
    exit();
}

// Add the user to the group
$stmt = $conn->prepare("INSERT INTO group_members (Member_ID, Group_ID, Is_Owner) VALUES (?, ?, 0)");
$stmt->bind_param("ii", $receiver_id, $group_id);

if ($stmt->execute()) {
    // Send a confirmation message
    $title = "You have joined the group!";
    $body = "";
    $query = "INSERT INTO private_messages (Sender_ID, Receiver_ID, Title, Body) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $receiver_id, $sender_id, $title, $body);

    if ($stmt->execute()) {
        echo "Group invitation accepted successfully.";
        header("Location: group_details.php?id=$group_id");
        exit();
    } else {
        echo "Error sending confirmation message: " . $conn->error;
    }
} else {
    echo "Error joining the group: " . $conn->error;
}

$stmt->close();
$conn->close();
?>