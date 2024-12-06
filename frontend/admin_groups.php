<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php';


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
    <style>
        .groups-list {
            list-style-type: none;
            padding: 0;
        }
        .group-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
        .group-info {
            display: flex;
            align-items: center;
        }
        .group-info span {
            margin-right: 10px;
        }
        .group-actions {
            display: flex;
            align-items: center;
        }
        .group-actions form {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>All Groups</h2>
        <?php if (!empty($groups)): ?>
            <ul class="groups-list">
                <?php foreach ($groups as $group): ?>
                    <li class="group-item">
                        <div class="group-info">
                            <span class="group-name"><strong>Title:</strong> <?php echo htmlspecialchars($group['Name']); ?></span>
                            <span class="group-description"><strong>Description:</strong> <?php echo htmlspecialchars($group['Description']); ?></span>
                        </div>
                        <div class="group-actions">
                            <form action="process_delete_group.php" method="POST">
                                <input type="hidden" name="group_id" value="<?php echo htmlspecialchars($group['Group_ID']); ?>">
                                <button type="submit" class="button">Delete Group</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No groups found.</p>
        <?php endif; ?>
    </div>
</body>
</html>