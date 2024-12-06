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

// Fetch friends data from the database
$query = "
    SELECT member_relationship.Member_2_ID, member.Pseudonym, member_relationship.type AS Relationship_Type
    FROM member_relationship
    JOIN member ON member_relationship.Member_2_ID = member.Member_ID
    WHERE member_relationship.Member_1_ID = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

$friends = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $friends[] = $row;
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
    <title>Friends</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="friends-container">
        <form action="process_send_friend_request.php" method="POST" class="friend-request-form">
            <input type="text" name="username" placeholder="ENTER USERNAME" required>
            <button type="submit" class="button">Send friend request</button>
        </form>
        <h2>My Friends</h2>
        <?php if (!empty($friends)): ?>
            <ul class="friends-list">
                <?php foreach ($friends as $friend): ?>
                    <li class="friend-item">
                        <span class="friend-username"><?php echo htmlspecialchars($friend['Pseudonym']); ?></span>
                        <span class="friend-relationship"><?php echo htmlspecialchars($friend['Relationship_Type']); ?></span>
                        <a href="friend_info.php?friend_id=<?php echo $friend['Member_2_ID']; ?>" class="button">View Info</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You have no friends added yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>