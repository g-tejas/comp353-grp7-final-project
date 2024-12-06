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

// Check user privilege level
$stmt = $conn->prepare("SELECT Privilege_Level FROM member WHERE Member_ID = ?");
$stmt->bind_param('i', $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $privilege_level = $row['Privilege_Level'];

    if ($privilege_level < 2) {
        // redirect users with junior privileges
        header("Location: groups.php");
        exit();
    }
} else {
    // the user is not found in the database
    echo "Error: User not found.";
    exit();
}

$stmt->close();

// Validate form submission
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));

    // Validate name
    if (empty($name)) {
        $errors[] = "Group name is required.";
    } elseif (strlen($name) > 100) {
        $errors[] = "Group name cannot exceed 100 characters.";
    }

    // Validate description
    if (empty($description)) {
        $errors[] = "Group description is required.";
    } elseif (strlen($description) > 65535) {
        $errors[] = "Group description is too long.";
    }

    // If no errors, proceed with group creation
    if (empty($errors)) {
        try {
            // Disable autocommit
            $conn->autocommit(FALSE);

            // Prepare and execute group insertion
            $stmt_group = $conn->prepare("INSERT INTO `group` (Name, Description) VALUES (?, ?)");
            $stmt_group->bind_param('ss', $name, $description);
            
            if (!$stmt_group->execute()) {
                throw new Exception("Failed to create group: " . $stmt_group->error);
            }

            // Get the ID of the newly created group
            $group_id = $conn->insert_id;

            // Prepare and execute group members insertion
            $stmt_members = $conn->prepare("INSERT INTO group_members (Member_ID, Group_ID, Is_Owner) VALUES (?, ?, 1)");
            $stmt_members->bind_param('ii', $member_id, $group_id);
            
            if (!$stmt_members->execute()) {
                throw new Exception("Failed to add group member: " . $stmt_members->error);
            }

            // Commit the transaction
            $conn->commit();

            // Close statements
            $stmt_group->close();
            $stmt_members->close();

            // Redirect to the group details page
            header("Location: group_details.php?id=$group_id");
            exit();

        } catch (Exception $e) {
            // Roll back the transaction
            $conn->rollback();

            // Log the error
            error_log("Group creation error: " . $e->getMessage());

            // Add error to errors array
            $errors[] = "An unexpected error occurred. Please try again.";
        } finally {
            // Reset autocommit
            $conn->autocommit(TRUE);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Group</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container my-5">
        <h2>Create Group</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="create_group.php" method="POST">
            <div class="form-group">
                <label for="name">Group Name</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" 
                       required maxlength="100">
            </div>
            <div class="form-group">
                <label for="description">Group Description</label>
                <textarea class="form-control" id="description" name="description" 
                          rows="5" required maxlength="65535"><?php 
                              echo isset($description) ? htmlspecialchars($description) : ''; 
                          ?></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Group</button>
                <a href="groups.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>