<?php
session_start();
include 'includes/dbh.inc.php';
include 'includes/header.php';

// Fetch posts for the logged-in user
$query = "SELECT * FROM content WHERE Member_ID = ? ORDER BY Timestamp DESC";
$user_id = $_SESSION['user'];
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
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

        <!-- Displaying Posts -->
        <div class="card display-posts">
            <h3>Your Posts</h3>
            <div id="posts-list">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="post-item">
                            <h4><?php echo htmlspecialchars($row['Title']); ?></h4>
                            <p><?php echo htmlspecialchars($row['Body']); ?></p>
                            <?php if ($row['Media_Path']): ?>
                                <?php if (strpos($row['Media_Path'], '.mp4') !== false || strpos($row['Media_Path'], '.webm') !== false): ?>
                                    <video controls style="max-width: 50%; height: auto;">
                                        <source src="<?php echo htmlspecialchars(str_replace('..uploads/', '', $row['Media_Path'])); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars(str_replace('..uploads/', '', $row['Media_Path'])); ?>" alt="Post Media" style="max-width: 100%; height: auto;">
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No posts found.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
$stmt->close();
$conn->close();
include 'includes/footer.php'; 
?>

