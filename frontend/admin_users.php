<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php'; // Ensure this path is correct



// Fetch all members from the database
$query = "SELECT * FROM member";
$stmt = $conn->prepare($query);
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
</head>
<body>
    <div class="container">
        <h2>All Members</h2>
        <?php if (!empty($members)): ?>
            <ul class="members-list">
                <?php foreach ($members as $member): ?>
                    <li class="member-item">
                        <span class="member-username"><strong>Username:</strong> <?php echo htmlspecialchars($member['Pseudonym']); ?></span>
                        <span class="member-email"><strong>Email:</strong> <?php echo htmlspecialchars($member['Email']); ?></span>
                        <span class="member-privilege"><strong>Privilege Level:</strong> <?php echo htmlspecialchars($member['Privilege_Level']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No members found.</p>
        <?php endif; ?>
    </div>
</body>
</html>