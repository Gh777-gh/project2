<?php
session_start();
if ($_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit();
}
include 'db.php';

//Patient statistics
$total_patients = $conn->query("SELECT COUNT(*) as total FROM patients WHERE doctor_id = " . $_SESSION['user_id'])->fetch_assoc()['total'];
$affected = $conn->query("SELECT COUNT(*) as total FROM patient_records pr JOIN patients p ON pr.patient_id = p.id WHERE p.doctor_id = " . $_SESSION['user_id'] . " AND pr.ai_result = 'affected' AND pr.doctor_confirm = 'yes'")->fetch_assoc()['total'];
$not_affected = $conn->query("SELECT COUNT(*) as total FROM patient_records pr JOIN patients p ON pr.patient_id = p.id WHERE p.doctor_id = " . $_SESSION['user_id'] . " AND pr.ai_result = 'not_affected' AND pr.doctor_confirm = 'yes'")->fetch_assoc()['total'];
$pending = $conn->query("SELECT COUNT(*) as total FROM patient_records pr JOIN patients p ON pr.patient_id = p.id WHERE p.doctor_id = " . $_SESSION['user_id'] . " AND pr.doctor_confirm = 'pending'")->fetch_assoc()['total'];
$records = $conn->query("SELECT COUNT(*) as total FROM patient_records pr JOIN patients p ON pr.patient_id = p.id WHERE p.doctor_id = " . $_SESSION['user_id'])->fetch_assoc()['total'];

//Fetch curve data (last 5 months)
$months = [];
$month_labels = [];
$affected_data = [];
$not_affected_data = [];
$pending_data = [];

for ($i = 4; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_labels[] = date('M', strtotime("-$i months")); // تسميات الأشهر (مثل Jan, Feb)
    $months[] = $month;
}

//Fetch data for each month
foreach ($months as $month) {
    //Infected cases
    $affected_query = $conn->query("SELECT COUNT(*) as total FROM patient_records pr JOIN patients p ON pr.patient_id = p.id WHERE p.doctor_id = " . $_SESSION['user_id'] . " AND pr.ai_result = 'affected' AND pr.doctor_confirm = 'yes' AND DATE_FORMAT(pr.recorded_at, '%Y-%m') = '$month'");
    $affected_data[] = $affected_query->fetch_assoc()['total'];

    // Uninfected cases
    $not_affected_query = $conn->query("SELECT COUNT(*) as total FROM patient_records pr JOIN patients p ON pr.patient_id = p.id WHERE p.doctor_id = " . $_SESSION['user_id'] . " AND pr.ai_result = 'not_affected' AND pr.doctor_confirm = 'yes' AND DATE_FORMAT(pr.recorded_at, '%Y-%m') = '$month'");
    $not_affected_data[] = $not_affected_query->fetch_assoc()['total'];

    //Pending cases
    $pending_query = $conn->query("SELECT COUNT(*) as total FROM patient_records pr JOIN patients p ON pr.patient_id = p.id WHERE p.doctor_id = " . $_SESSION['user_id'] . " AND pr.doctor_confirm = 'pending' AND DATE_FORMAT(pr.recorded_at, '%Y-%m') = '$month'");
    $pending_data[] = $pending_query->fetch_assoc()['total'];
}

//Fetch complaints
$complaints = $conn->query("SELECT * FROM complaints WHERE doctor_id = " . $_SESSION['user_id'] . " ORDER BY submitted_at DESC");

include 'header.php';
?>
<div class="container mt-5">
    <h2><i class="fas fa-tachometer-alt"></i> Doctor Dashboard</h2>
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card p-3">
                <h5>Total Patients</h5>
                <p><?php echo $total_patients; ?></p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3">
                <h5>Affected Cases</h5>
                <p><?php echo $affected; ?></p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <h5>Not Affected Cases</h5>
                <p><?php echo $not_affected; ?></p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3">
                <h5>Pending Cases</h5>
                <p><?php echo $pending; ?></p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3">
                <h5>Total Records</h5>
                <p><?php echo $records; ?></p>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><a href="add_patient.php" class="btn btn-success w-100"><i class="fas fa-user-plus"></i> Add Patient</a></div>
        <div class="col-md-3"><a href="analyze_scan.php" class="btn btn-primary w-100"><i class="fas fa-upload"></i> Analyze Scan</a></div>
        <div class="col-md-3"><a href="library.php" class="btn btn-secondary w-100"><i class="fas fa-book"></i> Library</a></div>
        <div class="col-md-3"><a href="shared_cases.php" class="btn btn-warning w-100"><i class="fas fa-share-alt"></i> Shared Cases</a></div>
    </div>
    <div class="row">
        <div class="col-md-5">
            <h4 class="mt-4">Patient Status Trends</h4>
            <canvas id="statusChart" style="width: 80%;"></canvas>
        </div>
        <div class="col-md-7">
            <h4 class="mt-4">My Complaints</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Complaint</th>
                        <th>Status</th>
                        <th>Response</th>
                        <th>Submitted At</th>
                        <th>Responded At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $complaints->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['complaint_text']; ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td><?php echo $row['response'] ?? 'N/A'; ?></td>
                            <td><?php echo $row['submitted_at']; ?></td>
                            <td><?php echo $row['responded_at'] ?? 'N/A'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($month_labels); ?>,
            datasets: [{
                label: 'Affected Cases',
                data: <?php echo json_encode($affected_data); ?>,
                borderColor: '#e74c3c',
                fill: false
            }, {
                label: 'Not Affected Cases',
                data: <?php echo json_encode($not_affected_data); ?>,
                borderColor: '#2ecc71',
                fill: false
            }, {
                label: 'Pending Cases',
                data: <?php echo json_encode($pending_data); ?>,
                borderColor: '#f1c40f',
                fill: false
            }]
        }
    });
</script>
<?php include 'footer.php'; ?>