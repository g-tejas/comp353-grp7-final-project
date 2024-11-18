<?php
session_start();
include 'includes/header.php';
?>

<div class="dashboard">
    <!-- Welcome Section -->
    <div class="card welcome-card">
        <?php if(isset($_SESSION['user'])): ?>
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        <?php else: ?>
            <h2>Welcome to COSN</h2>
            <div class="auth-links">
                <a href="login.php" class="button">Login</a>
                <a href="register.php" class="button">Register</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if(isset($_SESSION['user'])): ?>
        <!-- Quick Actions -->
        <div class="card quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-grid">
                <a href="profile.php" class="action-item">
                    <span class="action-title">My Profile</span>
                    <span class="action-desc">View and edit your profile</span>
                </a>
                
                <a href="messages.php" class="action-item">
                    <span class="action-title">Messages</span>
                    <span class="action-desc">Check your private messages</span>
                </a>
                
                <a href="groups.php" class="action-item">
                    <span class="action-title">Groups</span>
                    <span class="action-desc">Manage your groups</span>
                </a>
                
                <a href="events.php" class="action-item">
                    <span class="action-title">Events</span>
                    <span class="action-desc">View upcoming events</span>
                </a>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card recent-activity">
            <h3>Recent Activity</h3>
            <!-- TODO: Replace with actual activity data -->
            <div class="activity-list">
                <div class="activity-item">
                    <p>New post in "Local Community Group"</p>
                    <small>2 hours ago</small>
                </div>
                <div class="activity-item">
                    <p>Event reminder: Monthly Meeting</p>
                    <small>5 hours ago</small>
                </div>
                <div class="activity-item">
                    <p>New friend request from Jane Doe</p>
                    <small>Yesterday</small>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

