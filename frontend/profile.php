<?php
session_start();
include 'includes\dbh.inc.php'; // Ensure this path is correct!!! it can vary based on your files

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user'];

// Fetch user data from the database
$query = "SELECT * FROM member WHERE Member_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pseudonym = $row['Pseudonym'];
    $email = $row['Email'];
    $status = $row['Status'];
    $privilege_level = $row['Privilege_Level'];
    $address = $row['Address'];
    $is_business = $row['Is_Business'];
    $dob = $row['Date_of_Birth'];
} else {
    echo "User not found.";
    exit();
}

$stmt->close();
$conn->close();
?>

<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="profile-container">
        <h2>Profile</h2>
        <button onclick="location.href='edit_profile.php'">Edit Profile</button>
    </div>

    <div class="profile-info card">
        <h3>Personal Information</h3>
        <p>Pseudonym: <?php echo htmlspecialchars($pseudonym); ?></p>
        <p>Email: <?php echo htmlspecialchars($email); ?></p>
        <p>Address: <?php echo htmlspecialchars($address); ?></p>
        <p>Date of Birth: <?php echo htmlspecialchars($dob); ?></p>
        <p>Status: <?php echo htmlspecialchars($status); ?></p>
        <p>Privilege Level: <?php echo htmlspecialchars($privilege_level); ?></p>
        <p>Is Business: <?php echo htmlspecialchars($is_business); ?></p>
    </div>

    <div class="profile-groups card">
        <h3>My Groups</h3>
        <!-- TODO: Replace with actual group data -->
        <ul>
            <li>Local Community Group</li>
            <li>Environmental Awareness</li>
            <li>Tech Enthusiasts</li>
        </ul>
    </div>

    <div class="profile-posts card">
        <h3>Recent Posts</h3>
        <!-- TODO: Replace with actual post data -->
        <div class="post">
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            <small>Posted: 2024-03-20</small>
        </div>
    </div>
</body>
</html>