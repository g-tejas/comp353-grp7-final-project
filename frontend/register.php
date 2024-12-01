
<div class="form-container">
    <h2>Register New Account</h2>
    <?php
    if (isset($_SESSION['error'])) {
        echo "<div style='color: red;'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    ?>
    <form action="processes/register_process.php" method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="email">Email (Proton Mail only):</label>
            <input type="email" name="email" id="email" required pattern=".*@proton\..*">
        </div>
        
        <div class="form-group">
            <label for="address">Address:</label>
            <input type="text" name="address" id="address" required>
        </div>
        
        <div class="form-group">
            <label for="pseudonym">Pseudonym:</label>
            <input type="text" name="pseudonym" id="pseudonym" required>
        </div>
        
        <div class="form-group">
            <label for="is_business">Is Business:</label>
            <select name="is_business" id="is_business" required>
                <option value="No">No</option>
                <option value="Yes">Yes</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="dob">Date of Birth:</label>
            <input type="date" name="dob" id="dob" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>
        
        <div id="password_error" style="color: red; display: none;">
            Passwords do not match.
        </div>
        
        <button type="submit">Register</button>
    </form>
</div>

<script>
function validateForm() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm_password").value;

    if (password !== confirmPassword) {
        document.getElementById("password_error").style.display = "block";
        return false;
    }

    return true;
}
</script>