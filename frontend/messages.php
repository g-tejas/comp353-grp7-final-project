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

// Fetch messages for the logged-in user from the database
$query = "
    SELECT pm.Sender_ID, pm.Receiver_ID, pm.Title, pm.Body, pm.Timestamp, s.Pseudonym AS Sender_Pseudonym
    FROM private_messages pm
    JOIN member s ON pm.Sender_ID = s.Member_ID
    WHERE pm.Receiver_ID = ?
    ORDER BY pm.Timestamp DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
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
    <title>Messages</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="profile-container">
        <button onclick="location.href='createmessage.php'">New Message</button>
    </div>
    <div class="messages-container">
        <?php if (!empty($messages)): ?>
            <ul class="messages-list">
                <?php foreach ($messages as $message): ?>
                    <li class="message-item">
                        <span class="message-sender">FROM: <?php echo htmlspecialchars($message['Sender_Pseudonym']); ?></span>
                        <span class="message-timestamp">@<?php echo htmlspecialchars($message['Timestamp']); ?></span>
                        <h3 class="message-title"><?php echo htmlspecialchars($message['Title']); ?></h3>
                        <p class="message-body"><?php echo htmlspecialchars($message['Body']); ?></p>
                        <?php if (strpos($message['Title'], 'has sent you a friend request') !== false): ?>
                            <form action="process_accept_friend_request.php" method="POST">
                                <input type="hidden" name="sender_id" value="<?php echo htmlspecialchars($message['Sender_ID']); ?>">
                                <button type="submit" class="btn btn-primary">Accept Friend Request</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You have no messages.</p>
        <?php endif; ?>
    </div>
</body>
</html>