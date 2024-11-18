<?php include 'includes/header.php'; ?>

<div class="form-container">
    <h2>Register New Account</h2>
    <form action="register_process.php" method="POST">
        <div class="form-group">
            <label for="email">Email (Proton Mail only):</label>
            <input type="email" name="email" required pattern=".*@proton\..*">
        </div>
        
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="dob">Date of Birth:</label>
            <input type="date" name="dob" required>
        </div>
        
        <div class="form-group">
            <label for="referrer">Referrer's Member ID:</label>
            <input type="text" name="referrer" required>
        </div>
        
        <button type="submit">Register</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?> 