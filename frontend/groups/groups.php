<?php
session_start();
include 'includes/header.php';

// Fetch the user's groups from the database
$stmt = $conn->prepare("SELECT g.Group_ID, g.Name, g.Description, gm.Is_Owner
                       FROM `group` g
                       JOIN group_members gm ON g.Group_ID = gm.Group_ID
                       WHERE gm.Member_ID = :member_id");
$stmt->bindParam(':member_id', $_SESSION['user_id']);
$stmt->execute();
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container my-5">
    <h1>My Groups</h1>

    <?php if (empty($groups)): ?>
        <p>You are not a member of any groups yet.</p>
        <a href="create_group.php" class="btn btn-primary">Create a New Group</a>
    <?php else: ?>
        <div class="row">
            <?php foreach ($groups as $group): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($group['Name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($group['Description']); ?></p>
                            <a href="group_details.php?id=<?php echo $group['Group_ID']; ?>" class="btn btn-primary">View Group</a>
                            <?php if ($group['Is_Owner']): ?>
                                <a href="edit_group.php?id=<?php echo $group['Group_ID']; ?>" class="btn btn-secondary">Manage Group</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="create_group.php" class="btn btn-primary">Create a New Group</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>