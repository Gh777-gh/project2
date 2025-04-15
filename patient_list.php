<?php
session_start();
if($_SESSION['role'] != 'doctor') { header("Location: login.php"); exit(); }
include 'db.php';

$name_search = $_POST['name_search'] ?? '';
$history_search = $_POST['history_search'] ?? '';
$date_search = $_POST['date_search'] ?? '';
$status_search = $_POST['status_search'] ?? '';

$query = "SELECT p.* FROM patients p LEFT JOIN patient_records pr ON p.id = pr.patient_id WHERE p.doctor_id = " . $_SESSION['user_id'];
$conditions = [];
if($name_search) $conditions[] = "p.name LIKE '%$name_search%'";
if($history_search) $conditions[] = "p.medical_history LIKE '%$history_search%'";
if($date_search) $conditions[] = "pr.exam_date = '$date_search'";
if($status_search) $conditions[] = "pr.ai_result = '$status_search'";
if($conditions) $query .= " AND " . implode(" AND ", $conditions);
$query .= " GROUP BY p.id";
$result = $conn->query($query);

include 'header.php';
?>
<div class="container mt-5">
    <h2><i class="fas fa-list"></i> Patient List</h2>
    <form method="POST" class="mb-3">
        <div class="row">
            <div class="col-md-3 mb-3">
                <input type="text" name="name_search" class="form-control" placeholder="Search by Name" value="<?php echo $name_search; ?>">
            </div>
            <div class="col-md-3 mb-3">
                <input type="text" name="history_search" class="form-control" placeholder="Search by Medical History" value="<?php echo $history_search; ?>">
            </div>
            <div class="col-md-3 mb-3">
                <input type="date" name="date_search" class="form-control" value="<?php echo $date_search; ?>">
            </div>
            <div class="col-md-3 mb-3">
                <select name="status_search" class="form-control">
                    <option value="">Search by Status</option>
                    <option value="affected" <?php echo $status_search == 'affected' ? 'selected' : ''; ?>>Affected</option>
                    <option value="not_affected" <?php echo $status_search == 'not_affected' ? 'selected' : ''; ?>>Not Affected</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
    </form>
    <table class="table table-striped">
        <thead><tr><th>Name</th><th>Age</th><th>Medical History</th><th>Action</th></tr></thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['age']; ?></td>
                    <td><?php echo $row['medical_history'] ?? 'N/A'; ?></td>
                    <td><a href="track_patient.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-notes-medical"></i> Track</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>