<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php'; // Ensure this path is correct



// Fetch all groups from the database
$query = "SELECT * FROM `group`";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$groups = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $groups[] = $row;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Groups</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>All Groups</h2>
        <?php if (!empty($groups)): ?>
            <ul class="groups-list">
                <?php foreach ($groups as $group): ?>
                    <li class="group-item">
                        <span class="group-name"><strong>Title:</strong> <?php echo htmlspecialchars($group['Name']); ?></span>
                        <span class="group-description"><strong>Description:</strong> <?php echo htmlspecialchars($group['Description']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No groups found.</p>
        <?php endif; ?>
    </div>
</body>
</html>