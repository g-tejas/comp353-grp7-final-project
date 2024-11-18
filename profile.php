<?php include 'includes/header.php'; ?>

<div class="profile-container">
    <div class="profile-header">
        <h2>Profile</h2>
        <button onclick="location.href='edit_profile.php'">Edit Profile</button>
    </div>

    <div class="profile-info card">
        <h3>Personal Information</h3>
        <!-- TODO: Replace with actual user data -->
        <p>Name: John Doe</p>
        <p>Email: john.doe@proton.me</p>
        <p>Member Since: January 1, 2024</p>
        <p>Status: Active</p>
        <p>Privilege Level: Junior Member</p>
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
</div>

<?php include 'includes/footer.php'; ?>
