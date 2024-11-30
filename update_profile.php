<?php
session_start();

// Connect to the database
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "comp353_cosn";

try {
    //fill with database data
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the user's current data
    $stmt = $conn->prepare("SELECT * FROM member WHERE Member_ID = :member_id");
    $stmt->bindParam(':member_id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validate and sanitize the input data
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $pseudonym = filter_input(INPUT_POST, 'pseudonym', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    $dob = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_STRING);

    // Handle profile picture upload
    $profile_picture = $user['Profile_Picture'];
    if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
            $profile_picture = $file_path;
        } else {
            // Handle file upload error
            $error_message = "Error uploading profile picture.";
        }
    }

    // Update the user's data in the database
    $stmt = $conn->prepare("UPDATE member SET Name = :name, Pseudonym = :pseudonym, Email = :email, Password = :password, Address = :address, Date_of_Birth = :dob, Profile_Picture = :profile_picture WHERE Member_ID = :member_id");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':pseudonym', $pseudonym);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', password_hash($password, PASSWORD_DEFAULT));
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':dob', $dob);
    $stmt->bindParam(':profile_picture', $profile_picture);
    $stmt->bindParam(':member_id', $_SESSION['user_id']);

    if ($stmt->execute()) {
        // Update the session data with the new profile information
        $_SESSION['username'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['profile_picture'] = $profile_picture;

        // Redirect to the profile page with a success message
        header("Location: profile.php?success=1");
        exit;
    } else {
        // Handle database update error
        $error_message = "Error updating profile. Please try again.";
    }
} catch(PDOException $e) {
    // Handle database connection or query error
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - COSN</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container my-5">
        <h1>Update Profile</h1>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
    </div>
</body>
</html>