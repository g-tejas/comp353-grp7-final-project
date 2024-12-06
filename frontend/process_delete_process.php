<?php
session_start();
include 'includes/dbh.inc.php'; 

// user logged in?
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get the Content_ID from the URL
if (isset($_GET['id'])) {
    $content_id = intval($_GET['id']);

    // Prepare delete statement
    $query = "DELETE FROM content WHERE Content_ID = ? AND Member_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $content_id, $_SESSION['user']);

    if ($stmt->execute()) {
        header("Location: index.php?success=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>