<?php
session_start();
if($_SESSION['role'] != 'doctor') { header("Location: login.php"); exit(); }
include 'db.php';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $medical_history = $_POST['medical_history'];
    $exam_date = $_POST['exam_date'];
    $notes = $_POST['notes'];

    $stmt = $conn->prepare("INSERT INTO patients (name, age, medical_history, doctor_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sisi", $name, $age, $medical_history, $_SESSION['user_id']);
    $stmt->execute();
    $patient_id = $conn->insert_id;

    $stmt = $conn->prepare("INSERT INTO patient_records (patient_id, exam_date, notes) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $patient_id, $exam_date, $notes);
    $stmt->execute();

    header("Location: patient_list.php");
    exit();
}
include 'header.php';
?>
<div class="container mt-5">
    <h2><i class="fas fa-user-plus"></i> Add Patient</h2>
    <form method="POST">
        <div class="mb-3"><input type="text" name="name" class="form-control" placeholder="Patient Name" required></div>
        <div class="mb-3"><input type="number" name="age" class="form-control" placeholder="Age" required></div>
        <div class="mb-3"><textarea name="medical_history" class="form-control" placeholder="Medical History"></textarea></div>
        <div class="mb-3"><input type="date" name="exam_date" class="form-control" required></div>
        <div class="mb-3"><textarea name="notes" class="form-control" placeholder="Initial Notes"></textarea></div>
        <button type="submit" class="btn btn-primary">Add</button>
    </form>
</div>
<?php include 'footer.php'; ?>