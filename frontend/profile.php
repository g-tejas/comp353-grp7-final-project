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

// Fetch user data
$query = "SELECT * FROM member WHERE Member_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pseudonym = $row['Pseudonym'];
    $email = $row['Email'];
    $address = $row['Address'];
    $dob = $row['Date_of_Birth'];
    $status = $row['Status'];
    $privilege_level = $row['Privilege_Level'];
    $is_business = $row['Is_Business'];

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
    echo "User not found.";
    exit();
}

// posts from groups the member is a part of AND from friends with PUBLIC profile accessibility
$query = "
    SELECT DISTINCT c.Title, c.Body AS Content, c.Timestamp, m.Pseudonym AS Author, g.Name AS Group_Name
    FROM content c
    JOIN member m ON c.Member_ID = m.Member_ID
    LEFT JOIN group_members gm ON gm.Group_ID = c.Group_ID
    LEFT JOIN `group` g ON g.Group_ID = c.Group_ID
    LEFT JOIN profile_accessibility pa ON pa.Member_ID = c.Member_ID AND pa.Target_ID = ?
    WHERE (gm.Member_ID = ? AND c.Group_ID IS NOT NULL) OR (pa.Accessibility = 'Public')
    ORDER BY c.Timestamp DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $member_id, $member_id);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
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
    <title>Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="profile-info card">
        <h3>Personal Information</h3>
        <p>Pseudonym: <?php echo htmlspecialchars($pseudonym); ?></p>
        <p>Email: <?php echo htmlspecialchars($email); ?></p>
        <p>Address: <?php echo htmlspecialchars($address); ?></p>
        <p>Date of Birth: <?php echo htmlspecialchars($dob); ?></p>
        <p>Status: <?php echo htmlspecialchars($status); ?></p>
        <p>Privilege Level: <?php echo htmlspecialchars($privilege_label); ?></p>
        <p>Is Business: <?php echo htmlspecialchars($is_business); ?></p>
    </div>

    <div class="profile-posts card">
        <h3>Recent Posts</h3>
        <?php if (!empty($posts)): ?>
            <ul class="posts-list">
                <?php foreach ($posts as $post): ?>
                    <li class="post-item">
                        <h4><?php echo htmlspecialchars($post['Title']); ?></h4>
                        <p><?php echo htmlspecialchars($post['Content']); ?></p>
                        <small>Posted by <?php echo htmlspecialchars($post['Author']); ?> in <?php echo htmlspecialchars($post['Group_Name']); ?> on <?php echo htmlspecialchars($post['Timestamp']); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No posts found.</p>
        <?php endif; ?>
    </div>
</body>
</html>