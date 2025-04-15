<?php
session_start();
if($_SESSION['role'] != 'doctor') { header("Location: login.php"); exit(); }
include 'db.php';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $complaint_text = $_POST['complaint_text'];
    $stmt = $conn->prepare("INSERT INTO complaints (doctor_id, complaint_text) VALUES (?, ?)");
    $stmt->bind_param("is", $_SESSION['user_id'], $complaint_text);
    $stmt->execute();
    header("Location: dashboard_doctor.php");
    exit();
}
include 'header.php';
?>
<div class="container mt-5">
    <h2><i class="fas fa-exclamation-triangle"></i> Submit Complaint</h2>
    <form method="POST">
        <div class="mb-3"><textarea name="complaint_text" class="form-control" placeholder="Enter your complaint" required></textarea></div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
<?php include 'footer.php'; ?>