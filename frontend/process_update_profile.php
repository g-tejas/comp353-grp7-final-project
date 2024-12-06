<?php
session_start();
include 'includes/dbh.inc.php'; // Ensure this path is correct

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user'];

// Get the form data
$pseudonym = $_POST['pseudonym'];
$email = $_POST['email'];
$address = $_POST['address'];
$dob = $_POST['dob'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Check if passwords match
if (!empty($password) && $password === $confirm_password) {
    // Update the member information in the database with the new password
    $query = "UPDATE member SET Pseudonym = ?, Email = ?, Address = ?, Date_of_Birth = ?, Password = ? WHERE Member_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssi", $pseudonym, $email, $address, $dob, $password, $member_id);
} else {
    // Update the member information without changing the password
    $query = "UPDATE member SET Pseudonym = ?, Email = ?, Address = ?, Date_of_Birth = ? WHERE Member_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $pseudonym, $email, $address, $dob, $member_id);
}

if ($stmt->execute()) {
    // Update session variables
    $_SESSION['pseudonym'] = $pseudonym;
    $_SESSION['email'] = $email;
    $_SESSION['address'] = $address;
    $_SESSION['dob'] = $dob;

    // Redirect to the profile page
    header("Location: profile.php");
    exit();
} else {
    echo "Error updating record: " . $conn->error;
}

$stmt->close();
$conn->close();
?>