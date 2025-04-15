<?php
session_start();
if($_SESSION['role'] != 'admin') { header("Location: login.php"); exit(); }
include 'db.php';

// Delete article
if(isset($_GET['delete_article'])) {
    $article_id = $_GET['delete_article'];
    $conn->query("DELETE FROM article_tags WHERE article_id = $article_id");
    $conn->query("DELETE FROM articles WHERE id = $article_id");
    header("Location: dashboard_admin.php");
    exit();
}

// Respond to complaint
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complaint_id'])) {
    $complaint_id = $_POST['complaint_id'];
    $response = $_POST['response'];
    $stmt = $conn->prepare("UPDATE complaints SET response = ?, status = 'resolved', responded_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $response, $complaint_id);
    $stmt->execute();
    header("Location: dashboard_admin.php");
    exit();
}

//General Statistics
$articles = $conn->query("SELECT COUNT(*) as total FROM articles")->fetch_assoc()['total'];
$doctors = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'doctor'")->fetch_assoc()['total'];
$patients = $conn->query("SELECT COUNT(*) as total FROM patients")->fetch_assoc()['total'];

//Fetch curve data (last 5 months)
$months = [];
$month_labels = [];
$records_data = [];
$complaints_data = [];

for ($i = 4; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $month_labels[] = date('M', strtotime("-$i months")); // تسميات الأشهر
    $months[] = $month;
}

//Fetch data for each month
foreach ($months as $month) {
    //Added records
    $records_query = $conn->query("SELECT COUNT(*) as total FROM patient_records WHERE DATE_FORMAT(recorded_at, '%Y-%m') = '$month'");
    $records_data[] = $records_query->fetch_assoc()['total'];

    //Resolved complaints
    $complaints_query = $conn->query("SELECT COUNT(*) as total FROM complaints WHERE status = 'resolved' AND DATE_FORMAT(responded_at, '%Y-%m') = '$month'");
    $complaints_data[] = $complaints_query->fetch_assoc()['total'];
}

$complaints = $conn->query("SELECT c.*, u.username FROM complaints c JOIN users u ON c.doctor_id = u.id");
include 'header.php';
?>
<div class="container mt-5">
    <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
    <div class="row mb-4">
        <div class="col-md-4"><div class="card p-3"><h5>Total Articles</h5><p><?php echo $articles; ?></p></div></div>
        <div class="col-md-4"><div class="card p-3"><h5>Total Doctors</h5><p><?php echo $doctors; ?></p></div></div>
        <div class="col-md-4"><div class="card p-3"><h5>Total Patients</h5><p><?php echo $patients; ?></p></div></div>
    </div>

    <h4>Articles</h4>
    <a href="add_article.php" class="btn btn-success mb-3"><i class="fas fa-plus"></i> Add Article</a>
    <table class="table table-striped">
        <thead><tr><th>Title</th><th>Action</th></tr></thead>
        <tbody>
            <?php $articles = $conn->query("SELECT * FROM articles"); while($row = $articles->fetch_assoc()): ?>
                <tr><td><?php echo $row['title']; ?></td><td><a href="?delete_article=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a></td></tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <div class="row">
        <div class="col-md-6">
            <h4>Performance Charts</h4>
            <canvas id="performanceChart"></canvas>
        </div>
        <div class="col-md-6">
            <h4>Complaints</h4>
            <table class="table table-striped">
                <thead><tr><th>Doctor</th><th>Complaint</th><th>Status</th><th>Response</th><th>Action</th></tr></thead>
                <tbody>
                    <?php while($row = $complaints->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['complaint_text']; ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td><?php echo $row['response'] ?? 'N/A'; ?></td>
                            <td>
                                <?php if($row['status'] == 'pending'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="complaint_id" value="<?php echo $row['id']; ?>">
                                        <textarea name="response" class="form-control d-inline" style="width:200px;" required></textarea>
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-reply"></i> Respond</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('performanceChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($month_labels); ?>,
        datasets: [{
            label: 'Patient Records Added',
            data: <?php echo json_encode($records_data); ?>,
            borderColor: '#2c3e50',
            fill: false
        }, {
            label: 'Complaints Resolved',
            data: <?php echo json_encode($complaints_data); ?>,
            borderColor: '#e74c3c',
            fill: false
        }]
    }
});
</script>
<?php include 'footer.php'; ?>