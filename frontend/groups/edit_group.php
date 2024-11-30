<?php
session_start();
include 'includes/header.php';

// Fetch the group details from the database
$group_id = $_GET['id'];
$stmt = $conn->prepare("SELECT Name, Description FROM `group` WHERE Group_ID = :group_id");
$stmt->bindParam(':group_id', $group_id);
$stmt->execute();
$group = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the user is the group owner
$stmt = $conn->prepare("SELECT Is_Owner FROM group_members WHERE Member_ID = :member_id AND Group_ID = :group_id");
$stmt->bindParam(':member_id', $_SESSION['user_id']);
$stmt->bindParam(':group_id', $group_id);
$stmt->execute();
$is_owner = $stmt->fetchColumn();

if (!$is_owner) {
    // Redirect non-owners to the group details page
    header("Location: group_details.php?id=$group_id");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    // Update the group details in the database
    $stmt = $conn->prepare("UPDATE `group` SET Name = :name, Description = :description WHERE Group_ID = :group_id");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':group_id', $group_id);
    $stmt->execute();

    // Redirect to the group details page
    header("Location: group_details.php?id=$group_id");
    exit;
}
?>

<div class="container my-5">
    <h1>Edit Group</h1>
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
</div>

<?php include 'includes/footer.php'; ?>