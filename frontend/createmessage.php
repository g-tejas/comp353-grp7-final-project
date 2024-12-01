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

// Fetch friends list from the database
$query = "
    SELECT m.Member_ID, m.Pseudonym
    FROM member_relationship mr
    JOIN member m ON mr.Member_2_ID = m.Member_ID
    WHERE mr.Member_1_ID = ?
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
    <title>Create Message</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Create Message</h2>
        <form action="send_message.php" method="POST">
            <div class="form-group">
                <label for="friend">Select Friend</label>
                <select class="form-control" id="friend" name="friend" required>
                    <?php if (!empty($friends)): ?>
                        <?php foreach ($friends as $friend): ?>
                            <option value="<?php echo htmlspecialchars($friend['Member_ID']); ?>"><?php echo htmlspecialchars($friend['Pseudonym']); ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No friends available</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="body">Body</label>
                <textarea class="form-control" id="body" name="body" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Send Message</button>
                <a href="messages.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>