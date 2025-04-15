<?php
session_start();
include 'db.php';
if(isset($_FILES['scanImage'])) {
    $file = $_FILES['scanImage'];
    $patient_id = $_POST['patient_id'] ?? null;
    $uploadDir = 'assets/uploads/';
    $fileName = time() . "_" . basename($file['name']);
    move_uploaded_file($file['tmp_name'], $uploadDir . $fileName);
    $ai_result = rand(0, 1) ? 'affected' : 'not_affected';

    if($patient_id) {
        $stmt = $conn->prepare("UPDATE patient_records SET scan_image = ?, ai_result = ? WHERE patient_id = ? ORDER BY recorded_at DESC LIMIT 1");
        $stmt->bind_param("ssi", $fileName, $ai_result, $patient_id);
        $stmt->execute();
    }

    echo "<div class='alert alert-info'>AI Result: " . ($ai_result == 'affected' ? 'Affected' : 'Not Affected') . "</div>";
    echo "<form method='POST' action='save_result.php'><input type='hidden' name='patient_id' value='$patient_id'><input type='hidden' name='scan_path' value='$fileName'><input type='hidden' name='ai_result' value='$ai_result'><button type='submit' name='confirm' value='yes' class='btn btn-success'>Confirm</button> <button type='submit' name='confirm' value='no' class='btn btn-danger'>Reject</button></form>";
}
?>