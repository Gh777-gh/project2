<?php
session_start();
if($_SESSION['role'] != 'doctor') { header("Location: login.php"); exit(); }
include 'db.php';
$patient_id = $_GET['id'];
$patient = $conn->query("SELECT * FROM patients WHERE id = $patient_id AND doctor_id = " . $_SESSION['user_id'])->fetch_assoc();

// Edit patient data
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_patient'])) {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $medical_history = $_POST['medical_history'];
    $stmt = $conn->prepare("UPDATE patients SET name = ?, age = ?, medical_history = ? WHERE id = ? AND doctor_id = ?");
    $stmt->bind_param("sissi", $name, $age, $medical_history, $patient_id, $_SESSION['user_id']);
    $stmt->execute();
    header("Location: track_patient.php?id=$patient_id");
    exit();
}

// Delete a patient along with their records
if(isset($_GET['delete_patient'])) {
    $conn->query("DELETE cc FROM case_comments cc JOIN shared_cases sc ON cc.shared_case_id = sc.id WHERE sc.patient_id = $patient_id");
    $conn->query("DELETE FROM shared_cases WHERE patient_id = $patient_id");
    $conn->query("DELETE FROM patient_records WHERE patient_id = $patient_id");
    $conn->query("DELETE FROM patients WHERE id = $patient_id AND doctor_id = " . $_SESSION['user_id']);
    header("Location: patient_list.php");
    exit();
}

// Delete a specific record
if(isset($_GET['delete_record'])) {
    $record_id = $_GET['delete_record'];
    $conn->query("DELETE cc FROM case_comments cc JOIN shared_cases sc ON cc.shared_case_id = sc.id WHERE sc.record_id = $record_id");
    $conn->query("DELETE FROM shared_cases WHERE record_id = $record_id");
    $conn->query("DELETE FROM patient_records WHERE id = $record_id AND patient_id = $patient_id");
    header("Location: track_patient.php?id=$patient_id");
    exit();
}

// Add a new record
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_record'])) {
    $exam_date = $_POST['exam_date'];
    $notes = $_POST['notes'];
    $ai_result = $_POST['ai_result'];
    $doctor_confirm = $_POST['doctor_confirm'];

    if(isset($_FILES['scan_image']) && $_FILES['scan_image']['name']) {
        $file = $_FILES['scan_image'];
        $scan_image = "assets/uploads/" . time() . "_" . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $scan_image);
    } else {
        $last_record = $conn->query("SELECT scan_image FROM patient_records WHERE patient_id = $patient_id ORDER BY recorded_at DESC LIMIT 1")->fetch_assoc();
        $scan_image = $last_record['scan_image'] ?? null;
    }

    $stmt = $conn->prepare("INSERT INTO patient_records (patient_id, exam_date, scan_image, ai_result, doctor_confirm, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $patient_id, $exam_date, $scan_image, $ai_result, $doctor_confirm, $notes);
    $stmt->execute();
    header("Location: track_patient.php?id=$patient_id");
    exit();
}

// Share a record
if(isset($_GET['share_record'])) {
    $record_id = $_GET['share_record'];
    $stmt = $conn->prepare("INSERT INTO shared_cases (patient_id, record_id, shared_by) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $patient_id, $record_id, $_SESSION['user_id']);
    $stmt->execute();
    header("Location: track_patient.php?id=$patient_id");
    exit();
}

$records = $conn->query("SELECT * FROM patient_records WHERE patient_id = $patient_id ORDER BY recorded_at DESC");
include 'header.php';
?>
<div class="container mt-5">
    <h2><i class="fas fa-notes-medical"></i> Track Patient: <?php echo $patient['name']; ?></h2>
    <h4>Patient Info</h4>
    <form method="POST">
        <input type="hidden" name="update_patient" value="1">
        <div class="mb-3"><input type="text" name="name" class="form-control" value="<?php echo $patient['name']; ?>" required></div>
        <div class="mb-3"><input type="number" name="age" class="form-control" value="<?php echo $patient['age']; ?>" required></div>
        <div class="mb-3"><textarea name="medical_history" class="form-control" placeholder="Medical History"><?php echo $patient['medical_history']; ?></textarea></div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Update Patient</button>
        <a href="?id=<?php echo $patient_id; ?>&delete_patient=true" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this patient and all their records?')"><i class="fas fa-trash"></i> Delete Patient</a>
    </form>

    <h4 class="mt-4">Add New Record</h4>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_record" value="1">
        <div class="mb-3"><input type="date" name="exam_date" class="form-control" required></div>
        <div class="mb-3"><input type="file" name="scan_image" class="form-control" accept="image/*"></div>
        <div class="mb-3">
            <select name="ai_result" class="form-control">
                <option value="">AI Result (Optional)</option>
                <option value="affected">Affected</option>
                <option value="not_affected">Not Affected</option>
            </select>
        </div>
        <div class="mb-3">
            <select name="doctor_confirm" class="form-control">
                <option value="pending">Doctor Confirmation</option>
                <option value="yes">Yes</option>
                <option value="no">No</option>
            </select>
        </div>
        <div class="mb-3"><textarea name="notes" class="form-control" placeholder="Notes"></textarea></div>
        <button type="submit" class="btn btn-primary">Save Record</button>
    </form>

    <h4 class="mt-4">Patient Records</h4>
    <table class="table table-striped">
        <thead><tr><th>Exam Date</th><th>Scan Image</th><th>AI Result</th><th>Doctor Confirm</th><th>Notes</th><th>Recorded At</th><th>Action</th></tr></thead>
        <tbody>
            <?php while($row = $records->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['exam_date']; ?></td>
                    <td><?php echo $row['scan_image'] ? "<button class='btn btn-link' data-bs-toggle='modal' data-bs-target='#imageModal' data-img='{$row['scan_image']}'><img src='{$row['scan_image']}' width='50'></button>" : 'N/A'; ?></td>
                    <td><?php echo $row['ai_result'] ?? 'N/A'; ?></td>
                    <td><?php echo $row['doctor_confirm']; ?></td>
                    <td><?php echo $row['notes'] ?? 'N/A'; ?></td>
                    <td><?php echo $row['recorded_at']; ?></td>
                    <td>
                        <a href="?id=<?php echo $patient_id; ?>&delete_record=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this record?')"><i class="fas fa-trash"></i> Delete</a>
                        <a href="?id=<?php echo $patient_id; ?>&share_record=<?php echo $row['id']; ?>" class="btn btn-success btn-sm"><i class="fas fa-share"></i> Share</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Scan Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img id="modalImage" src="" style="width:100%;">
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var imageModal = document.getElementById('imageModal');
    imageModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var imgUrl = button.getAttribute('data-img');
        var modalImg = imageModal.querySelector('#modalImage');
        modalImg.src = imgUrl;
    });
});
</script>
<?php include 'footer.php'; ?>