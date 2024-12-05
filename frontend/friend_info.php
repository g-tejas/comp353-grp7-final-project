<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php'; 

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get the friend_id from the query parameter
if (!isset($_GET['friend_id'])) {
    echo "No friend specified.";
    exit();
}

$friend_id = $_GET['friend_id'];

// Fetch friend data from the database
$query = "SELECT * FROM member WHERE Member_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $friend_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pseudonym = $row['Pseudonym'];
    $email = $row['Email'];
    $date_joined = isset($row['Date_Joined']) ? $row['Date_Joined'] : 'N/A';
    $status = $row['Status'];
    $privilege_level = $row['Privilege_Level'];
    $address = $row['Address'];
    $is_business = $row['Is_Business'];
    $dob = isset($row['Date_of_Birth']) ? $row['Date_of_Birth'] : 'N/A';

    // Determine the privilege level label
    switch ($privilege_level) {
        case 3:
            $privilege_label = "Admin";
            break;
        case 1:
            $privilege_label = "Junior";
            break;
        case 2:
            $privilege_label = "Senior";
            break;
        default:
            $privilege_label = "Unknown";
            break;
    }
} else {
    echo "Friend not found.";
    exit();
}

//Fetch the friends status (Colleague, Friend, Family)
$member_id = $_SESSION['user'];
$query = "SELECT Type FROM member_relationship WHERE Member_1_ID = ? AND Member_2_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii",$member_id, $friend_id);
$stmt->execute();
$result = $stmt->get_result();
$relationship_type= $result->fetch_assoc()['Type'];

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friend Info</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="profile-info card">
        <h3>Friend Information</h3>
        <p>Pseudonym: <?php echo htmlspecialchars($pseudonym); ?></p>
        <p>Email: <?php echo htmlspecialchars($email); ?></p>
        <p>Address: <?php echo htmlspecialchars($address); ?></p>
        <p>Type of Relationship: <?php echo htmlspecialchars($relationship_type); ?></p>
        <p>Date of Birth: <?php echo htmlspecialchars($dob); ?></p>
        <p>Status: <?php echo htmlspecialchars($status); ?></p>
        <p>Privilege Level: <?php echo htmlspecialchars($privilege_label); ?></p>
        <p>Is Business: <?php echo htmlspecialchars($is_business); ?></p>
    </div>

    <div class="profile-groups card">
        <h3>Groups</h3>
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