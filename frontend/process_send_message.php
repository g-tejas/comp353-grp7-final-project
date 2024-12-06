<?php
session_start();

include 'includes/dbh.inc.php'; // Ensure this path is correct

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$sender_id = $_SESSION['user'];
$receiver_id = $_POST['friend'];
$title = $_POST['title'];
$body = $_POST['body'];

// Insert the message into the private_messages table
$query = "INSERT INTO private_messages (Sender_ID, Receiver_ID, Title, Body) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiss", $sender_id, $receiver_id, $title, $body);

if ($stmt->execute()) {
    echo "Message sent successfully.";
    header("Location: messages.php");
    exit();
} else {
    echo "Error sending message: " . $conn->error;
}

$stmt->close();
$conn->close();
?>