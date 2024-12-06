<?php
session_start();
include 'includes/dbh.inc.php'; // Ensure this path is correct

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['user'];

// Get the content and visibility from the POST request
$content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
$visibility = filter_input(INPUT_POST, 'visibility', FILTER_SANITIZE_NUMBER_INT);

// Handle file upload
$media_path = null;
if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/'; // Ensure this directory exists and is writable
    $file_name = uniqid() . '_' . basename($_FILES['media']['name']);
    $media_path = $upload_dir . $file_name;

    // Move the uploaded file to the designated directory
    if (!move_uploaded_file($_FILES['media']['tmp_name'], $media_path)) {
        echo "Error uploading file.";
        exit();
    }
}

// Insert the post into the content table
$query = "INSERT INTO content (Member_ID, Body, Title, Media_Path, Is_Event, Timestamp, Group_ID) VALUES (?, ?, ?, ?, ?, NOW(), ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("isssis", $user_id, $content, $title, $media_path, $is_event, $group_id);

// Set default values for Title, Is_Event, and Group_ID
$title = "New Post"; // You can modify this as needed
$is_event = 0; // Assuming this is a regular post, not an event
$group_id = null; // Assuming this is not associated with a group

if ($stmt->execute()) {
    // Redirect back to the index page or wherever you want
    header("Location: index.php?success=1");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>