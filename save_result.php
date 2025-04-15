<?php
session_start();
include 'db.php';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'];
    $scan_path = $_POST['scan_path'];
    $ai_result = $_POST['ai_result'];
    $doctor_confirm = $_POST['confirm'];
    $stmt = $conn->prepare("UPDATE patient_records SET scan_image = ?, ai_result = ?, doctor_confirm = ? WHERE patient_id = ? ORDER BY recorded_at DESC LIMIT 1");
    $stmt->bind_param("sssi", $scan_path, $ai_result, $doctor_confirm, $patient_id);
    $stmt->execute();
    header("Location: track_patient.php?id=$patient_id");
    exit();
}
?>