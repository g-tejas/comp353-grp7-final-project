<?php
session_start();
include 'includes/dbh.inc.php';

// user logged in?
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$sender_id = $_SESSION['user'];
$receiver_id = $_POST['friend'];
$title = $_POST['title'];
$body = $_POST['body'];

// sender blocked by the receiver?
$query = "SELECT Accessibility FROM profile_accessibility WHERE Member_ID = ? AND Target_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $receiver_id, $sender_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['Accessibility'] == 'Blocked') {
        echo "You are blocked by this user. Message cannot be sent.";
        header("Location: messages.php");
        exit();
    }
}

// Insert message in private_messages table
$query = "INSERT INTO private_messages (Sender_ID, Receiver_ID, Title, Body) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiss", $sender_id, $receiver_id, $title, $body);

if ($stmt->execute()) {
    echo "Message sent successfully.";
    header("Location: messages.php");
    exit();
} else {
    echo "Error sending message: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>