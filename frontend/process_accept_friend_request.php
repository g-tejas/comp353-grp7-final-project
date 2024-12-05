<?php
session_start();
include '../includes/dbh.inc.php'; // Ensure this path is correct

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$receiver_id = $_SESSION['user'];
$sender_id = $_POST['sender_id']; // This should be passed from the message

// Retrieve the sender's pseudonym
$query = "SELECT Pseudonym FROM member WHERE Member_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $sender_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $sender_pseudonym = $row['Pseudonym'];
} else {
    echo "Sender not found.";
    exit();
}

// Check if the relationship already exists
$query = "SELECT * FROM member_relationship WHERE (Member_1_ID = ? AND Member_2_ID = ?) OR (Member_1_ID = ? AND Member_2_ID = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $receiver_id, $sender_id, $sender_id, $receiver_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Friend already accepted.";
    exit();
}

// Update the member_relationship table
$query = "INSERT INTO member_relationship (Member_1_ID, Member_2_ID, type) VALUES (?, ?, 'Friend'), (?, ?, 'Friend')";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $receiver_id, $sender_id, $sender_id, $receiver_id);

if ($stmt->execute()) {
    // Send a confirmation message
    $title = "You are now friends!";
    $body = "";
    $query = "INSERT INTO private_messages (Sender_ID, Receiver_ID, Title, Body) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $receiver_id, $sender_id, $title, $body);

    if ($stmt->execute()) {
        echo "Friend request accepted successfully.";
        header("Location: ../messages.php");
        exit();
    } else {
        echo "Error sending confirmation message: " . $conn->error;
    }
} else {
    echo "Error updating relationship: " . $conn->error;
}

$stmt->close();
$conn->close();
?>