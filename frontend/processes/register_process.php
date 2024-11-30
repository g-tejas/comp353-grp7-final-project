<?php
session_start();
include '../includes/dbh.inc.php'; // Database connection file

// Get data from register page
$email = $_POST['email'];
$address = $_POST['address'];
$pseudonym = $_POST['pseudonym'];
$is_business = $_POST['is_business'];
$dob = $_POST['dob'];
$password = $_POST['password']; 

// Check if the member already exists based on the email
$query = "SELECT * FROM member WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Email already exists, redirect back to register page with error message
    $_SESSION['error'] = "Email already exists";
    header("Location: ../register.php");
    exit();
}

// Check if the member already exists based on the pseudonym
$query = "SELECT * FROM member WHERE Pseudonym = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $pseudonym);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Pseudonym already exists, redirect back to register page with error message
    $_SESSION['error'] = "Pseudonym already exists";
    header("Location: ../register.php");
    exit();
}

// Execute SQL query to insert the new member
$query = "INSERT INTO member (Email, Address, Pseudonym, Is_Business, Date_of_Birth, Password) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssss", $email, $address, $pseudonym, $is_business, $dob, $password);

if ($stmt->execute()) {
    // Registration successful, redirect to login page
    header("Location: ../login.php"); // Update the path to the login page
    exit();
} else {
    // Registration failed, display an error message
    echo "Error: " . $stmt->error;
}
?>