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
$classification = isset($_POST['classification']) ? $_POST['classification'] : 'Public';

try {
    // Start transaction
    $conn->begin_transaction();

    // Insert into content table first
    $stmt = $conn->prepare("INSERT INTO content (Group_ID, Member_ID, Body, Title) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $group_id, $sender_id, $content, $title);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create content: " . $stmt->error);
    }
    
    // Get the content_id of the newly inserted content
    $content_id = $stmt->insert_id;

    // Insert into content_classification
    $view = ($classification === 'Public') ? 'Public' : 'Private';
    $allow_comment = ($classification === 'Public') ? 1 : 0;
    $allow_link = ($classification === 'Public') ? 1 : 0;
    
    $stmt_classification = $conn->prepare("INSERT INTO content_classification (Content_ID, View, Allow_Comment, Allow_Link) VALUES (?, ?, ?, ?)");
    $stmt_classification->bind_param('isii', $content_id, $view, $allow_comment, $allow_link);

    if (!$stmt_classification->execute()) {
        throw new Exception("Failed to create content classification: " . $stmt_classification->error);
    }

    // Commit the transaction
    $conn->commit();
    
    echo "Content approved and posted successfully.";
    // Redirect back to group page
    header("Location: group_details.php?id=" . $group_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

$stmt->close();
$conn->close();
?>