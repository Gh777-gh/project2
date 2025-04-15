<?php
session_start();
if($_SESSION['role'] != 'admin') { header("Location: login.php"); }
include 'db.php';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $tags = $_POST['tags'];
    $file = $_FILES['pdf'];
    $image = $_FILES['image'];
    $file_path = "assets/uploads/" . time() . "_" . basename($file['name']);
    $image_path = $image['name'] ? "assets/uploads/" . time() . "_" . basename($image['name']) : null;
    move_uploaded_file($file['tmp_name'], $file_path);
    if($image_path) move_uploaded_file($image['tmp_name'], $image_path);
    
    $stmt = $conn->prepare("INSERT INTO articles (title, description, file_path, image_path, category_id, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $title, $description, $file_path, $image_path, $category_id, $_SESSION['user_id']);
    $stmt->execute();
    $article_id = $conn->insert_id;

    foreach(explode(',', $tags) as $tag) {
        $tag = trim($tag);
        $stmt = $conn->prepare("INSERT IGNORE INTO tags (name) VALUES (?)");
        $stmt->bind_param("s", $tag);
        $stmt->execute();
        $tag_id = $conn->query("SELECT id FROM tags WHERE name='$tag'")->fetch_assoc()['id'];
        $conn->query("INSERT INTO article_tags (article_id, tag_id) VALUES ($article_id, $tag_id)");
    }
}
include 'header.php';
?>
<div class="container mt-5">
    <h2><i class="fas fa-plus"></i> Add Article</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3"><input type="text" name="title" class="form-control" placeholder="Article Title" required></div>
        <div class="mb-3"><textarea name="description" class="form-control" placeholder="Description" required></textarea></div>
        <div class="mb-3">
            <select name="category_id" class="form-control" required>
                <?php $result = $conn->query("SELECT * FROM categories"); while($row = $result->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3"><input type="text" name="tags" class="form-control" placeholder="Tags (comma-separated)" required></div>
        <div class="mb-3"><input type="file" name="pdf" class="form-control" accept=".pdf" required></div>
        <div class="mb-3"><input type="file" name="image" class="form-control" accept="image/*"></div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>
<?php include 'footer.php'; ?>