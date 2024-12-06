<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php'; 

// user logged in?
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user'];

// fetch messages
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
                        <?php
if (strpos($message['Title'], 'has invited you to a group') !== false) {
    // Extract Group ID from the Body
    $body = $message['Body'];
    $group_id = null;

    if (preg_match('/Group ID: (\d+)/', $body, $matches)) {
        $group_id = $matches[1]; // Group ID is captured in $matches[1]
    } else {
        echo "Error: Group ID not found in the message.";
        return; // Stop further execution
    }
?>
    <form action="accept_group_invite.php" method="POST">
        <input type="hidden" name="sender_id" value="<?php echo htmlspecialchars($message['Sender_ID']); ?>">
        <input type="hidden" name="group_id" value="<?php echo htmlspecialchars($group_id); ?>">
        <button type="submit" class="btn btn-primary">Accept Group Invite</button>
    </form>
<?php
}
?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You have no messages.</p>
        <?php endif; ?>
    </div>
</body>
</html>