<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php'; // Ensure this path is correct

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user'];

try {
    // Fetch the group ID from the GET request
    if (!isset($_GET['id'])) {
        throw new Exception("Group ID is not provided.");
    }
    $group_id = intval($_GET['id']);

    // Fetch the group details from the database
    $stmt = $conn->prepare("SELECT Name, Description FROM `group` WHERE Group_ID = ?");
    $stmt->bind_param('i', $group_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Group not found.");
    }

    $group = $result->fetch_assoc();

    // Check if the user is the group owner
    $stmt = $conn->prepare("SELECT Is_Owner FROM group_members WHERE Member_ID = ? AND Group_ID = ?");
    $stmt->bind_param('ii', $member_id, $group_id);
    $stmt->execute();
    $stmt->bind_result($is_owner);
    $stmt->fetch();

    if (!$is_owner) {
        // Redirect non-owners to the group details page
        header("Location: group_details.php?id=$group_id");
        exit();
    }
    $stmt->close();

    // Handle form submission for editing the group
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

        if (empty($name) || empty($description)) {
            throw new Exception("Name and Description cannot be empty.");
        }

        // Update the group details in the database
        $stmt = $conn->prepare("UPDATE `group` SET Name = ?, Description = ? WHERE Group_ID = ?");
        $stmt->bind_param('ssi', $name, $description, $group_id);
        $stmt->execute();

        // Redirect to the group details page
        header("Location: group_details.php?id=$group_id");
        exit();
    }
} catch (Exception $e) {
    error_log("Error in edit_group.php: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
} finally {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Group</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container my-5">
        <h1>Edit Group</h1>
        <?php if (isset($group)): ?>
            <form method="POST" action="edit_group.php?id=<?php echo $group_id; ?>">
                <div class="form-group">
                    <label for="name">Group Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($group['Name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Group Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($group['Description']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="group_details.php?id=<?php echo $group_id; ?>" class="btn btn-secondary">Cancel</a>
            </form>
        <?php else: ?>
            <p class="alert alert-danger">Group details not found.</p>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
