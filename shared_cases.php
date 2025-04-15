<?php
session_start();
if($_SESSION['role'] != 'doctor') { header("Location: login.php"); exit(); }
include 'db.php';

// Add a comment
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_comment'])) {
    $shared_case_id = $_POST['shared_case_id'];
    $comment_text = $_POST['comment_text'];
    $stmt = $conn->prepare("INSERT INTO case_comments (shared_case_id, doctor_id, comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $shared_case_id, $_SESSION['user_id'], $comment_text);
    $stmt->execute();
    header("Location: shared_cases.php");
    exit();
}

// Fetch all shared cases
$shared_cases = $conn->query("SELECT sc.*, p.name, pr.exam_date, pr.scan_image, pr.ai_result, pr.doctor_confirm, pr.notes, u.username AS shared_by_name 
                              FROM shared_cases sc 
                              JOIN patients p ON sc.patient_id = p.id 
                              JOIN patient_records pr ON sc.record_id = pr.id 
                              JOIN users u ON sc.shared_by = u.id 
                              ORDER BY sc.shared_at DESC");

include 'header.php';
?>
<div class="container mt-5">
    <h2><i class="fas fa-share-alt"></i> Shared Cases</h2>
    <p>View and comment on cases shared by all doctors.</p>

    <?php if($shared_cases->num_rows == 0): ?>
        <p class="text-muted">No shared cases available yet.</p>
    <?php else: ?>
        <?php while($shared = $shared_cases->fetch_assoc()): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Patient: <?php echo $shared['name']; ?> (Shared by: <?php echo $shared['shared_by_name']; ?> on <?php echo $shared['shared_at']; ?>)</h5>
                    <p>Exam Date: <?php echo $shared['exam_date']; ?></p>
                    <?php if($shared['scan_image']): ?>
                        <p>Scan Image: <button class="btn btn-link" data-bs-toggle="modal" data-bs-target="#imageModal" data-img="<?php echo $shared['scan_image']; ?>"><img src="<?php echo $shared['scan_image']; ?>" width="100"></button></p>
                    <?php else: ?>
                        <p>Scan Image: N/A</p>
                    <?php endif; ?>
                    <p>AI Result: <?php echo $shared['ai_result'] ?? 'N/A'; ?></p>
                    <p>Doctor Confirmation: <?php echo $shared['doctor_confirm']; ?></p>
                    <p>Notes: <?php echo $shared['notes'] ?? 'N/A'; ?></p>

                    <h6>Comments</h6>
                    <?php
                    $comments = $conn->query("SELECT cc.*, u.username FROM case_comments cc JOIN users u ON cc.doctor_id = u.id WHERE cc.shared_case_id = " . $shared['id'] . " ORDER BY cc.commented_at ASC");
                    if($comments->num_rows == 0): ?>
                        <p class="text-muted">No comments yet.</p>
                    <?php else: ?>
                        <?php while($comment = $comments->fetch_assoc()): ?>
                            <div class="border p-2 mb-2">
                                <strong><?php echo $comment['username']; ?>:</strong> <?php echo $comment['comment_text']; ?> <small>(<?php echo $comment['commented_at']; ?>)</small>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>

                    <form method="POST" class="mt-2">
                        <input type="hidden" name="shared_case_id" value="<?php echo $shared['id']; ?>">
                        <textarea name="comment_text" class="form-control mb-2" placeholder="Add a comment..." required></textarea>
                        <button type="submit" name="add_comment" class="btn btn-primary btn-sm"><i class="fas fa-comment"></i> Comment</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
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