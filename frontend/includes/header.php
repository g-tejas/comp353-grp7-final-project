<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/dbh.inc.php'; 

// Check if the user is logged in
if (isset($_SESSION['user'])) {
    $member_id = $_SESSION['user'];

    // Fetch the user's privilege level
    $query = "SELECT Privilege_Level FROM member WHERE Member_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $privilege_level = $row['Privilege_Level'];
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COSN - Community Social Network</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>COSN</h1>
        <?php if(isset($_SESSION['user'])): ?>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="groups.php">Groups</a></li>
                    <li><a href="friends.php">Friends</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="logout.php">Logout</a></li>
                    <?php if ($privilege_level == 3): // Check if the user is an admin ?>
                        <li><a href="admin_groups.php">Manage Groups</a></li>
                        <li><a href="admin_users.php">Manage Users</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </header>
    <main>

