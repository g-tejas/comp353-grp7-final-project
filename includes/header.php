<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
                    <li><a href="messages.php">Messages</a></li>
                    <?php if($_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="admin.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        <?php endif; ?>
    </header>
    <main>

