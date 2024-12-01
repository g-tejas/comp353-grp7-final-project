<?php
session_start();
include 'includes/header.php';
include 'includes/dbh.inc.php'; // Ensure this path is correct

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data from the database if not already set in session
if (!isset($_SESSION['pseudonym']) || !isset($_SESSION['address']) || !isset($_SESSION['dob']) || !isset($_SESSION['email'])) {
    $member_id = $_SESSION['user'];
    $query = "SELECT * FROM member WHERE Member_ID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['pseudonym'] = $row['Pseudonym'];
        $_SESSION['address'] = $row['Address'];
        $_SESSION['dob'] = $row['Date_of_Birth'];
        $_SESSION['email'] = $row['Email'];
    } else {
        echo "User not found.";
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>
        <form action="processes\update_profile.php" method="POST">
            <div class="form-group">
                <label for="pseudonym">Pseudonym</label>
                <textarea class="form-control" id="pseudonym" name="pseudonym" rows="1"><?php echo htmlspecialchars($_SESSION['pseudonym']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($_SESSION['address']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input type="date" class="form-control" id="dob" name="dob" value="<?php echo date('Y-m-d', strtotime($_SESSION['dob'])); ?>" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="profile.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>