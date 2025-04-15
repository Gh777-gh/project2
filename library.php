<?php
session_start();
if($_SESSION['role'] != 'doctor') { header("Location: login.php"); exit(); }
include 'db.php';

$title_search = $_POST['title_search'] ?? '';
$date_search = $_POST['date_search'] ?? '';
$category_search = $_POST['category_search'] ?? '';
$tag_filter = $_GET['tag'] ?? '';

$query = "SELECT a.*, c.name AS category_name FROM articles a JOIN categories c ON a.category_id = c.id";
$conditions = [];
if($title_search) $conditions[] = "a.title LIKE '%$title_search%'";
if($date_search) $conditions[] = "DATE(a.published_at) = '$date_search'";
if($category_search) $conditions[] = "a.category_id = '$category_search'";
if($tag_filter) $conditions[] = "a.id IN (SELECT article_id FROM article_tags JOIN tags t ON tag_id = t.id WHERE t.name = '$tag_filter')";
if($conditions) $query .= " WHERE " . implode(" AND ", $conditions);
$result = $conn->query($query);
$tags = $conn->query("SELECT DISTINCT t.name FROM tags t JOIN article_tags at ON t.id = at.tag_id");

include 'header.php';
?>
<div class="container mt-5">
    <h2><i class="fas fa-book"></i> Library</h2>
    <form method="POST" class="mb-3">
        <div class="row">
            <div class="col-md-4 mb-3">
                <input type="text" name="title_search" class="form-control" placeholder="Search by Title" value="<?php echo $title_search; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <input type="date" name="date_search" class="form-control" value="<?php echo $date_search; ?>">
            </div>
            <div class="col-md-4 mb-3">
                <select name="category_search" class="form-control">
                    <option value="">Search by Category</option>
                    <?php $categories = $conn->query("SELECT * FROM categories"); while($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_search == $cat['id'] ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
    </form>
    <div class="mb-3">
        <h5>Filter by Tag:</h5>
        <?php while($tag = $tags->fetch_assoc()): ?>
            <a href="?tag=<?php echo $tag['name']; ?>" class="badge bg-secondary me-2"><?php echo $tag['name']; ?></a>
        <?php endwhile; ?>
    </div>

    <?php if($result->num_rows == 0): ?>
        <p class="text-muted">No articles available yet.</p>
    <?php else: ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="row mb-4 p-3 border rounded align-items-center">
                <!-- Article image on the left -->
                <div class="col-md-2">
                    <?php if($row['image_path']): ?>
                        <img src="<?php echo $row['image_path']; ?>" class="img-fluid rounded" alt="<?php echo $row['title']; ?>" style="max-height: 100px;">
                    <?php else: ?>
                        <img src="assets/images/default_article.jpg" class="img-fluid rounded" alt="Default Image" style="max-height: 100px;">
                    <?php endif; ?>
                </div>
                <!-- Article description next to the image -->
                <div class="col-md-7">
                    <h5><?php echo $row['title']; ?></h5>
                    <p><?php echo substr($row['description'], 0, 150) . (strlen($row['description']) > 150 ? '...' : ''); ?></p>
                    <small class="text-muted">Published: <?php echo $row['published_at']; ?> | Category: <?php echo $row['category_name']; ?></small>
                    <div class="mt-2">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#pdfModal" data-pdf="<?php echo $row['file_path']; ?>">Read PDF</button>
                        <a href="<?php echo $row['file_path']; ?>" class="btn btn-secondary btn-sm" download="<?php echo basename($row['file_path']); ?>">Download</a>
                    </div>
                </div>
                <!-- Tags next to the description -->
                <div class="col-md-3">
                    <strong>Tags:</strong>
                    <?php 
                    $tag_result = $conn->query("SELECT t.name FROM tags t JOIN article_tags at ON t.id = at.tag_id WHERE at.article_id = " . $row['id']);
                    while($tag = $tag_result->fetch_assoc()): ?>
                        <span class="badge bg-info me-1"><?php echo $tag['name']; ?></span>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfModalLabel">View PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="pdfFrame" src="" style="width:100%; height:500px;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var pdfModal = document.getElementById('pdfModal');
    pdfModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var pdfUrl = button.getAttribute('data-pdf');
        var iframe = pdfModal.querySelector('#pdfFrame');
        iframe.src = pdfUrl;
    });
});
</script>
<?php include 'footer.php'; ?>