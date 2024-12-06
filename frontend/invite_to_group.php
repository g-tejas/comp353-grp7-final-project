<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$sender_id = $_SESSION['user'];
$pseudonym = $_POST['pseudonym'];
$group_id = $_POST['group_id'];

// Fetch the member's Member_ID based on the pseudonym
$query = "SELECT Member_ID FROM member WHERE Pseudonym = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $pseudonym);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $receiver_id = $row['Member_ID'];

    echo "Sender ID: " . $sender_id . "<br>";
echo "Pseudonym: " . $pseudonym . "<br>";
echo "Group ID: " . $group_id . "<br>";
echo "Receiver  ID: " . $receiver_id . "<br>";

    // Insert the group invitation message into the private_messages table
    $title = "$username has invited you to a group.";
    $body = "Click the button below to join group. Group ID: $group_id";
    $query = "INSERT INTO private_messages (Sender_ID, Receiver_ID, Title, Body) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $title, $body);

    if ($stmt->execute()) {
        echo "Group invitation sent successfully.";
    } else {
        echo "Error sending group invitation: " . $stmt->error;
    }

    $conn->commit();
    $stmt->close();
    $conn->close();
} else {
    echo "User not found.";
}
?>