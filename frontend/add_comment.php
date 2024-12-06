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

// Validate form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content_id = filter_input(INPUT_POST, 'content_id', FILTER_VALIDATE_INT);
    $body = trim(filter_input(INPUT_POST, 'body', FILTER_SANITIZE_STRING));

    if ($content_id && !empty($body)) {
        try {
            // Disable autocommit
            $conn->autocommit(FALSE);

            // Insert the comment into the comments table
            $stmt = $conn->prepare("INSERT INTO comment (Content_ID, Member_ID, Body) VALUES (?, ?, ?)");
            $stmt->bind_param('iis', $content_id, $member_id, $body);

            if ($stmt->execute()) {
                // Commit the transaction
                $conn->commit();
                $group_id = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
                header("Location: group_details.php?id=$group_id");
                exit();
            } else {
                throw new Exception("Failed to add comment: " . $stmt->error);
            }

            $stmt->close();
        } catch (Exception $e) {
            // Rollback the transaction
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        } finally {
            // Reset autocommit
            $conn->autocommit(TRUE);
        }
    } else {
        echo "Invalid input.";
    }
}
?>