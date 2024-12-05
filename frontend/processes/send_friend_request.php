<?php
session_start();
include '../includes/dbh.inc.php'; // Ensure this path is correct

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$sender_id = $_SESSION['user'];
$username = $_POST['username'];

// Fetch the sender's pseudonym
$query = "SELECT Pseudonym FROM member WHERE Member_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $sender_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the friend's Member_ID based on the username
$query = "SELECT Member_ID FROM member WHERE Pseudonym = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();



if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $receiver_id = $row['Member_ID'];

    // Insert the friend request message into the private_messages table
    $title = "$sender_id has sent you a friend request.";
    $body = "To accept this request, please click the button below.";
    $query = "INSERT INTO private_messages (Sender_ID, Receiver_ID, Title, Body) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $title, $body);

    if ($stmt->execute()) {
        echo "Friend request sent successfully.";
        header("Location: ../friends.php");
        exit();
    } else {
        echo "Error sending friend request: " . $conn->error;
    }
} else {
    echo "User not found.";
}

$stmt->close();
$conn->close();
?>