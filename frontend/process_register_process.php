<?php
session_start();

include 'includes/dbh.inc.php';

// Get data from register page
$email = $_POST['email'];
$address = $_POST['address'];
$pseudonym = $_POST['pseudonym'];
$is_business = $_POST['is_business'];
$dob = $_POST['dob'];
$password = $_POST['password']; 

// Check email
$query = "SELECT * FROM member WHERE Email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Email already exists
    $_SESSION['error'] = "Email already exists";
    header("Location: register.php");
    exit();
}

// Check pseudonym
$query = "SELECT * FROM member WHERE Pseudonym = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $pseudonym);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Pseudonym already exists
    $_SESSION['error'] = "Pseudonym already exists";
    header("Location: register.php");
    exit();
}

// insert the member
$query = "INSERT INTO member (Email, Address, Pseudonym, Is_Business, Date_of_Birth, Password) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssssss", $email, $address, $pseudonym, $is_business, $dob, $password);

if ($stmt->execute()) {
    // Registration successful
    header("Location: login.php"); 
    exit();
} else {
    // Registration failed
    echo "Error: " . $stmt->error;
}
?>