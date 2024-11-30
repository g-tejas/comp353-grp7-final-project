<!-- login.php -->
<form action="login_process.php" method="POST">
    <label for="username">Username:</label>
    <input type="text" name="username" id="username" required>
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required>
    <!-- Dev only: Quick admin toggle -->
    <div class="dev-options">
        <label>
            <input type="checkbox" name="is_admin" value="1">
            Login as Admin (Dev Only)
        </label>
    </div>
    <button type="submit">Login</button>
</form>

<!--Register button -->
<form action="register.php" method="GET">
    <button type="submit">Register</button>
</form>

