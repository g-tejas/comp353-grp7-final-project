<?php
session_start();

include 'includes/dbh.inc.php'; 

$username = $_POST['username'];
$password = $_POST['password'];

//check credentials
$query = "SELECT * FROM member WHERE Pseudonym = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['Status'] == 'Suspended') {
        $_SESSION['error'] = "Your account is suspended. Please contact the administrator.";
        header("Location: login.php");
        exit();
    }
    //compare password
    if ($password === $row['Password']) {
        $_SESSION['user'] = $row['Member_ID'];
        $_SESSION['username'] = $row['Pseudonym'];
        $_SESSION['email'] = $row['Email'];
        header("Location: profile.php");
        exit();
    } else {
        // Invalid password
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: login.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid username or password.";
    header("Location: login.php");
    exit();
}

$stmt->close();
$conn->close();
?>