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

// Fetch user's pseudonym to personalize the page
if (!isset($_SESSION['pseudonym'])) {
    try {
        $stmt = $conn->prepare("SELECT Pseudonym FROM member WHERE Member_ID = ?");
        $stmt->bind_param('i', $member_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['pseudonym'] = $user['Pseudonym'];
        }

        $stmt->close();
    } catch (Exception $e) {
        error_log("Error fetching user pseudonym: " . $e->getMessage());
        // Continue without stopping the page
    }
}

try {
    // Updated query to match the database structure
    $stmt = $conn->prepare("SELECT g.Group_ID, g.Name, g.Description, 
                                   CASE 
                                       WHEN gm.Is_Owner = 1 THEN 1 
                                       ELSE 0 
                                   END AS Is_Owner
                            FROM `group` g
                            JOIN group_members gm ON g.Group_ID = gm.Group_ID
                            WHERE gm.Member_ID = ?");
    $stmt->bind_param('i', $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $groups = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching groups: " . $e->getMessage());
    // Display a user-friendly error message
    $groups = [];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Groups</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container my-5">
        <h1>My Groups 
            <?php if (isset($_SESSION['pseudonym'])): ?>
                for <?php echo htmlspecialchars($_SESSION['pseudonym']); ?>
            <?php endif; ?>
        </h1>

        <?php if (empty($groups)): ?>
            <div class="alert alert-info">
                <p>You are not a member of any groups yet.</p>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($groups as $group): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($group['Name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($group['Description']); ?></p>
                                <div class="d-flex justify-content-between">
                                    <a href="group_details.php?id=<?php echo $group['Group_ID']; ?>" class="btn btn-primary btn-sm">View Group</a>
                                    <?php if ($group['Is_Owner'] == 1): ?>
                                        <a href="edit_group.php?id=<?php echo $group['Group_ID']; ?>" class="btn btn-secondary btn-sm">Manage Group</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-4 text-center">
            <a href="create_group.php" class="btn btn-primary">Create a New Group</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>