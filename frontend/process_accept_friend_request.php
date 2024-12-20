<?php
session_start();

include 'includes/dbh.inc.php'; 

// user logged in?
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$receiver_id = $_SESSION['user'];
$sender_id = $_POST['sender_id']; 


// fetch the sender's pseudonym
$query = "SELECT Pseudonym FROM member WHERE Member_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $receiver_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $sender_pseudonym = $row['Pseudonym'];
} else {
    echo "Sender not found.";
    exit();
}

// Check relationship 
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
    // Send confirmation message
    $title = "You are now friends with " . htmlspecialchars($sender_pseudonym);
    $query = "INSERT INTO private_messages (Sender_ID, Receiver_ID, Title, Body) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $receiver_id, $sender_id, $title, $body);

    if ($stmt->execute()) {
        // Set profile accessibility to private
        $query = "INSERT INTO profile_accessibility (Member_ID, Target_ID, Accessibility) VALUES (?, ?, 'Private'), (?, ?, 'Private')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $receiver_id, $sender_id, $sender_id, $receiver_id);

        if ($stmt->execute()) {
            header("Location: messages.php");
            exit();
        } else {
            echo "Error setting the friends privacy: " . $stmt->error;
        }
    } else {
        echo "Error sending confirmation message: " . $stmt->error;
    }
} else {
    echo "Error updating relationship: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>