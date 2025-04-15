<?php
include 'db.php';
session_start(); // Start the session at the beginning

// Check if the user is already logged in
if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] == 'admin') {
        header("Location: dashboard_admin.php");
    } else {
        header("Location: dashboard_doctor.php");
    }
    exit(); // Stop execution if the user is logged in
}

// Handle login request
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($user = $result->fetch_assoc()) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        if($user['role'] == 'admin') {
            header("Location: dashboard_admin.php");
        } else {
            header("Location: dashboard_doctor.php");
        }
        exit(); // Stop execution after redirection
    } else {
        $error = "Invalid username or password";
    }
}

// If not logged in, display only the login interface
include 'header.php';
?>
<div class="container mt-5" style="width: 50%;">
    <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
    <?php if(isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
        <div class="mb-3"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
        <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
<?php include 'footer.php'; ?>