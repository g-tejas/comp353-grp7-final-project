<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php';

// user is logged in?
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user'];

// Get the group ID
$group_id = filter_input(INPUT_GET, 'group_id', FILTER_SANITIZE_NUMBER_INT);

// Validate form submission
$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
    $body = trim(filter_input(INPUT_POST, 'body', FILTER_SANITIZE_STRING));

    // title
    if (empty($title)) {
        $errors[] = "Title is required.";
    } elseif (strlen($title) > 50) {
        $errors[] = "Title cannot exceed 50 characters.";
    }

    // body
    if (empty($body)) {
        $errors[] = "Body is required.";
    }

    // Handle file upload
    $media = $_FILES['media'] ?? null;
    $mediaPath = '';

    if ($media && $media['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/'; // Ensure this directory exists and is writable
        $mediaPath = $uploadDir . basename($media['name']);
        
        // Move the uploaded file to the desired directory
        if (!move_uploaded_file($media['tmp_name'], $mediaPath)) {
            $errors[] = "Failed to upload media.";
        }
    }

    // no errors - create content
    if (empty($errors)) {
        try {
            // Disable autocommit
            $conn->autocommit(FALSE);

            // Prepare and execute content insertion
            $stmt = $conn->prepare("INSERT INTO content (Group_ID, Member_ID, Body, Title, Media_Path) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('iisss', $group_id, $member_id, $body, $title, $mediaPath);


        // Check if the user is a member of the group
$stmt = $conn->prepare("SELECT Is_Owner FROM group_members WHERE Member_ID = ? AND Group_ID = ?");
$stmt->bind_param("ii", $member_id, $group_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $is_owner = $row['Is_Owner'];

    // Retrieve the group owner's ID
    $stmt = $conn->prepare("SELECT Member_ID FROM group_members WHERE Group_ID = ? AND Is_Owner = 1");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $owner_id = $row['Member_ID'];

        // Retrieve the content submitted by the user
        // $content = $_POST['content'];

        // If the user is not the owner, send a message to the group owner for approval
        if (!$is_owner) {
            $message_title = "$title (Approval: $group_id)";
            $message_body = "$content";

            $stmt = $conn->prepare("INSERT INTO private_messages (Sender_ID, Receiver_ID, Title, Body) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $member_id, $owner_id, $message_title, $message_body);

            if ($stmt->execute()) {
                echo "Your content has been submitted for approval.";
            } else {
                echo "Error: Failed to send the message.";
                echo $member_id;
                echo $owner_id . $message_title . $message_body;
            }
        } else {
            try {
                // Disable autocommit
                $conn->autocommit(FALSE);
    
                // Prepare and execute content insertion
                $stmt = $conn->prepare("INSERT INTO content (Group_ID, Member_ID, Body, Title) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('iiss', $group_id, $member_id, $body, $title);
    
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create content: " . $stmt->error);
                }
    
                // Get last inserted Content_ID
                $content_id = $stmt->insert_id;
    
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
    
                // Commit the transaction
                $conn->commit();
    
                // Close statement
                $stmt->close();
    
                // Redirect to the group details page
                header("Location: group_details.php?id=$group_id");
                exit();
    
            } catch (Exception $e) {
                // Roll back the transaction
                $conn->rollback();
    
                // Log the error
                error_log("Content creation error: " . $e->getMessage());
    
                // Add error to errors array
                $errors[] = "An unexpected error occurred. Please try again.";
            } finally {
                // Reset autocommit
                $conn->autocommit(TRUE);
            }
        }
    } else {
        echo "Error: Group owner not found.";
    }
} else {
    echo "Error: You are not a member of this group.";
}


        
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Content</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container my-5">
        <h2>Create Content</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="create_content.php?group_id=<?php echo $group_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required maxlength="50">
            </div>
            <div class="form-group">
                <label for="body">Body</label>
                <textarea class="form-control" id="body" name="body" rows="5" required><?php echo isset($body) ? htmlspecialchars($body) : ''; ?></textarea>
            </div>
            <div class="post-contents">
                <label for="media">Upload Media (Image/Video):</label>
                <input type="file" id="media" name="media" accept="image/*,video/*">
            </div>
            <div class="form-group">
        <label>Classification</label>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="classification" id="public" value="Public" checked>
            <label class="form-check-label" for="public">
                Public (Comments On)
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="classification" id="private" value="Private">
            <label class="form-check-label" for="private">
                Private (Comments Off)
            </label>
        </div>
    </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Post Content</button>
                <a href="group_details.php?id=<?php echo $group_id; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>