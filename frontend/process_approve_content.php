<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user'];

// Get the necessary data from the form
$sender_id = $_POST['sender_id'];
$title = $_POST['title'];
$content = $_POST['body'];
$group_id = $_POST['group_id'];

// Debugging: Display values to ensure they're correct
echo "Sender ID: " . htmlspecialchars($sender_id) . "<br>";
echo "Title: " . htmlspecialchars($title) . "<br>";
echo "Content: " . htmlspecialchars($content) . "<br>";
echo "Group ID: " . htmlspecialchars($group_id) . "<br>";

// Now, insert the content into the database
$stmt = $conn->prepare("INSERT INTO content (Group_ID, Member_ID, Body, Title) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $group_id, $sender_id, $content, $title);

$classification = "Public";
//content_classification insertion
$classification = isset($_POST['classification']) ? $_POST['classification'] : 'Public'; // Default to 'Public'
$view = ($classification === 'Public') ? 'Public' : 'Private';
$allow_comment = ($classification === 'Public') ? 1 : 0;
$allow_link = ($classification === 'Public') ? 1 : 0;
$stmt_classification = $conn->prepare("INSERT INTO content_classification (Content_ID, View, Allow_Comment, Allow_Link) VALUES (?, ?, ?, ?)");
$stmt_classification->bind_param('isii', $content_id, $view, $allow_comment, $allow_link);

if (!$stmt_classification->execute()) {
    throw new Exception("Failed to create content classification: " . $stmt_classification->error);
}

// Execute the query and check for errors
if ($stmt->execute()) {
    echo "Content approved and posted successfully.";
} else {
    echo "Error: Failed to post the content. " . $stmt->error;
}

// Commit the transaction
$conn->commit();

$stmt->close();
$conn->close();
?>