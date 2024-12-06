<?php
session_start();
include 'includes/dbh.inc.php'; // Ensure this path is correct

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// Fetch user data from the database
$query = "SELECT * FROM member WHERE Member_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $member_details = [
        'Pseudonym' => $row['Pseudonym'],
        'Email' => $row['Email'],
        'Date_Joined' => $row['Date_Joined'],
        'Status' => $row['Status'],
        'Privilege_Level' => $row['Privilege_Level'],
        'Address' => $row['Address'],
        'Is_Business' => $row['Is_Business'],
        'DOB' => $row['DOB']
    ];
} else {
    echo "User not found.";
    exit();
}

$stmt->close();
$conn->close();
?>