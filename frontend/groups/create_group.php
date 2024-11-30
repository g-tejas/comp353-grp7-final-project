<?php
session_start();
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    // Insert the new group into the database
    $stmt = $conn->prepare("INSERT INTO `group` (Name, Description) VALUES (:name, :description)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->execute();

    // Add the current user as the owner of the new group
    $group_id = $conn->lastInsertId();
    $stmt = $conn->prepare("INSERT INTO group_members (Member_ID, Group_ID, Is_Owner) VALUES (:member_id, :group_id, 1)");
    $stmt->bindParam(':member_id', $_SESSION['user_id']);
    $stmt->bindParam(':group_id', $group_id);
    $stmt->execute();

    // Redirect to the group details page
    header("Location: group_details.php?id=$group_id");
    exit;
}
?>

<div class="container my-5">
    <h1>Create a New Group</h1>
    <form method="POST" action="create_group.php">
        <div class="form-group">
            <label for="name">Group Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="description">Group Description</label>
            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create Group</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>