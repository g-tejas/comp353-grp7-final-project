<?php
session_start();

include 'includes/dbh.inc.php'; 

// user logged in?
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];

// Get content visibility from POST
$content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
$visibility = filter_input(INPUT_POST, 'visibility', FILTER_SANITIZE_NUMBER_INT);

// file upload
$media_path = null;
if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/'; 
    $file_name = uniqid() . '_' . basename($_FILES['media']['name']);
    $media_path = $upload_dir . $file_name;

    // move file to directory
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
$title = "New Post";
$is_event = 0; // regular post, not event
$group_id = null; 

if ($stmt->execute()) {
    header("Location: index.php?success=1");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>