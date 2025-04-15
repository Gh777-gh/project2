<?php
include 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password']; // Password without encryption
    $role = $_POST['role'];
    $error = '';

    // Check if the username already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0) {
        $error = "Username '$username' is already taken. Please choose a different username.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role); // Save the password as plain text
        if($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $error = "An error occurred while creating your account. Please try again.";
        }
        $stmt->close();
    }
    $check->close();
}

include 'header.php';
?>
<div class="container mt-5" style="width: 50%;">
    <h2><i class="fas fa-user-plus"></i> Register</h2>
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-control">
                <option value="doctor">Doctor</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Register</button>
    </form>
</div>
<?php include 'footer.php'; ?>