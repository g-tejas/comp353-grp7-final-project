<?php
session_start();
include '../includes/dbh.inc.php'; // Update the path to the database connection file

$username = $_POST['username'];
$password = $_POST['password'];

// Prepare and execute the SQL statement to check the credentials
$query = "SELECT * FROM member WHERE Pseudonym = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Verify the password
    if (password_verify($password, $user['Password'])) {
        // Password is correct, set session variables and redirect to profile page
        $_SESSION['user'] = $user['Member_ID'];
        $_SESSION['username'] = $user['Pseudonym'];
        header("Location: ../profile.php"); // Update the path to the profile page
        exit();
    } else {
        // Password is incorrect, redirect back to login page with error message
        $_SESSION['error'] = "Invalid username or password";
        header("Location: ../login.php");
        exit();
    }
} else {
    // Username does not exist, redirect back to login page with error message
    $_SESSION['error'] = "Invalid username or password";
    header("Location: ../login.php");
    exit();
}
?>