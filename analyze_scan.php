<?php
session_start();
if($_SESSION['role'] != 'doctor') { header("Location: login.php"); exit(); }
include 'header.php';
$patient_id = $_GET['id'] ?? null;
$ai_website_url = "https://4204-34-145-209-77.ngrok-free.app"; // استبدل هذا برابط موقع الذكاء الاصطناعي الفعلي
?>
<div class="container mt-5">
    <h2><i class="fas fa-upload"></i> Analyze Scan</h2>
    <?php if($patient_id): ?><p>Analyzing scan for Patient ID: <?php echo $patient_id; ?></p><?php endif; ?>
    <iframe src="<?php echo $ai_website_url; ?>" style="width:100%;height:600px;border:none;"></iframe>
</div>
<?php include 'footer.php'; ?>