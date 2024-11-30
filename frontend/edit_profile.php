<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - COSN</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container my-5">
        <h1>Edit Profile</h1>
        <form id="edit-profile-form" action="update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" maxlength="100" value="<?php echo htmlspecialchars($user['Name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="pseudonym">Pseudonym</label>
                <input type="text" class="form-control" id="pseudonym" name="pseudonym" maxlength="50" value="<?php echo htmlspecialchars($user['Pseudonym']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password">
                <small class="form-text text-muted">Leave blank to keep the current password.</small>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['Address']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input type="date" class="form-control" id="dob" name="dob" value="<?php echo date('Y-m-d', strtotime($user['Date_of_Birth'])); ?>" required>
            </div>
            <div class="form-group">
                <label for="profile-picture">Profile Picture</label>
                <input type="file" class="form-control-file" id="profile-picture" name="profile_picture">
                <small class="form-text text-muted">Supported formats: JPG, PNG, GIF. Maximum size: 2MB.</small>
            </div>
            <div class="form-group">
                <img id="profile-preview" src="<?php echo $user['Profile_Picture'] ? $user['Profile_Picture'] : 'img/default-avatar.png'; ?>" alt="Profile Picture" class="img-thumbnail" style="max-width: 200px;">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="profile.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#profile-picture').on('change', function() {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#profile-preview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>
</html>