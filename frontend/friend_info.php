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


//Fetch the friends accessibility (Blocked,Public,Private)
$member_id = $_SESSION['user'];
$query = "SELECT Accessibility FROM profile_accessibility WHERE Member_ID = ? AND Target_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii",$member_id, $friend_id);
$stmt->execute();
$result = $stmt->get_result();
$privacy= $result->fetch_assoc()['Accessibility'];

// Fetch user accessibility to friend's profile (Blocked, Public, Private)
$query = "SELECT Accessibility FROM profile_accessibility WHERE Member_ID = ? AND Target_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $friend_id, $member_id);
$stmt->execute();
$result = $stmt->get_result();
$privacy_on_user = $result->fetch_assoc()['Accessibility'];

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
        <?php if ($privacy_on_user == 'Blocked'): ?>
            <p>You are blocked from viewing this profile.</p>
            <p>Set Privacy: <?php echo htmlspecialchars($privacy); ?></p>
        <?php elseif ($privacy_on_user == 'Private'): ?>
            <p>Pseudonym: <?php echo htmlspecialchars($pseudonym); ?></p>
            <p>Type of Relationship: <?php echo htmlspecialchars($relationship_type); ?></p>
            <p>Set Privacy: <?php echo htmlspecialchars($privacy); ?></p>
            <p>Privilege Level: <?php echo htmlspecialchars($privilege_label); ?></p>
        <?php elseif ($privacy_on_user == 'Public'): ?>
            <p>Pseudonym: <?php echo htmlspecialchars($pseudonym); ?></p>
            <p>Email: <?php echo htmlspecialchars($email); ?></p>
            <p>Address: <?php echo htmlspecialchars($address); ?></p>
            <p>Type of Relationship: <?php echo htmlspecialchars($relationship_type); ?></p>
            <p>Set Privacy: <?php echo htmlspecialchars($privacy); ?></p>
            <p>Date of Birth: <?php echo htmlspecialchars($dob); ?></p>
            <p>Status: <?php echo htmlspecialchars($status); ?></p>
            <p>Privilege Level: <?php echo htmlspecialchars($privilege_label); ?></p>
            <p>Is Business: <?php echo htmlspecialchars($is_business); ?></p>
        <?php else: ?>
            <p>Privacy setting not recognized.</p>
        <?php endif; ?>
    </div>

    <div class="relationship-change card">
        <h3>Change Relationship Type</h3>
        <form action="process_change_relationship_type.php" method="POST">
            <input type="hidden" name="friend_id" value="<?php echo htmlspecialchars($friend_id); ?>">
            <div class="form-group">
                <label for="relationship_type">Select Relationship Type</label>
                <select class="form-control" id="relationship_type" name="relationship_type" required>
                    <option value="Friend" <?php echo $relationship_type == 'Friend' ? 'selected' : ''; ?>>Friend</option>
                    <option value="Colleague" <?php echo $relationship_type == 'Colleague' ? 'selected' : ''; ?>>Colleague</option>
                    <option value="Family" <?php echo $relationship_type == 'Family' ? 'selected' : ''; ?>>Family</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Change Relationship Type</button>
        </form>
    </div>

    <div class="privacy-change card">
        <h3>Set Privacy for this User</h3>
        <form action="process_set_privacy.php" method="POST">
            <input type="hidden" name="friend_id" value="<?php echo htmlspecialchars($friend_id); ?>">
            <div class="form-group">
                <label for="privacy">Select Level of Privacy</label>
                <select class="form-control" id="privacy" name="privacy" required>
                    <option value="Public" <?php echo $relationship_type == 'Public' ? 'selected' : ''; ?>>Public</option>
                    <option value="Private" <?php echo $relationship_type == 'Private' ? 'selected' : ''; ?>>Private</option>
                    <option value="Blocked" <?php echo $relationship_type == 'Blocked' ? 'selected' : ''; ?>>Blocked</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Set Privacy</button>
        </form>
    </div>

    <?php if ($privacy_on_user == 'Public'): ?>
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
    <?php endif; ?>
</body>
</html>