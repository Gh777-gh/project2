<?php
session_start();
if($_SESSION['role'] != 'doctor') { header("Location: login.php"); }
include 'db.php';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $password, $_SESSION['user_id']);
    if($stmt->execute()) {
        $_SESSION['username'] = $username; // Update the username in the session
        $success = "Profile updated successfully";
    } else {
        $error = "Error updating profile";
    }
}
$user = $conn->query("SELECT * FROM users WHERE id = " . $_SESSION['user_id'])->fetch_assoc();
include 'header.php';
?>
<div class="container mt-5">
    <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
    <?php if(isset($success)): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
    <?php if(isset($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
        <div class="mb-3"><input type="text" name="username" class="form-control" value="<?php echo $user['username']; ?>" required></div>
        <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="New Password" required></div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
<?php include 'footer.php'; ?>