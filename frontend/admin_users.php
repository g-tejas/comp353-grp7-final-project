<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php'; // Ensure this path is correct


$admin_id = $_SESSION['user'];

// Fetch all members excluding the admin user
$query = "SELECT * FROM member WHERE Member_ID != ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
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
    <title>Admin - Members</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .members-list {
            list-style-type: none;
            padding: 0;
        }
        .member-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
        .member-info {
            display: flex;
            align-items: center;
        }
        .member-info span {
            margin-right: 10px;
        }
        .member-actions {
            display: flex;
            align-items: center;
        }
        .member-actions form {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>All Members</h2>
        <?php if (!empty($members)): ?>
            <ul class="members-list">
                <?php foreach ($members as $member): ?>
                    <li class="member-item">
                        <div class="member-info">
                            <span class="member-username"><strong>Username:</strong> <?php echo htmlspecialchars($member['Pseudonym']); ?></span>
                            <span class="member-email"><strong>Email:</strong> <?php echo htmlspecialchars($member['Email']); ?></span>
                            <span class="member-privilege"><strong>Privilege Level:</strong> <?php echo htmlspecialchars($member['Privilege_Level']); ?></span>
                        </div>
                        <div class="member-actions">
                            <form action="process_change_status.php" method="POST">
                                <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($member['Member_ID']); ?>">
                                <select name="status" required>
                                    <option value="Active" <?php echo $member['Status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo $member['Status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="Suspended" <?php echo $member['Status'] == 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                                </select>
                                <button type="submit" class="button">Change Status</button>
                            </form>
                            <form action="process_change_privilege.php" method="POST">
                                <input type="hidden" name="member_id" value="<?php echo htmlspecialchars($member['Member_ID']); ?>">
                                <select name="privilege" required>
                                    <option value="1" <?php echo $member['Privilege_Level'] == '1' ? 'selected' : ''; ?>>Junior</option>
                                    <option value="2" <?php echo $member['Privilege_Level'] == '2' ? 'selected' : ''; ?>>Senior</option>
                                    <option value="3" <?php echo $member['Privilege_Level'] == '3' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <button type="submit" class="button">Change Privilege</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No members found.</p>
        <?php endif; ?>
    </div>
</body>
</html>