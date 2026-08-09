<?php
include '../includes/db.php';
include '../includes/az_servicebus.php';

$message = "";

// Xử lý thêm ghi chú mới kèm file/hình ảnh
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $filePath = null;

    if ($title === "" || $content === "") {
        $message = "Tiêu đề và nội dung không được để trống!";
    } else {
        // Xử lý upload file/ảnh đính kèm nếu có
        if (isset($_FILES['note_file']) && $_FILES['note_file']['error'] === 0) {
            $fileTmpPath = $_FILES['note_file']['tmp_name'];
            $fileName = $_FILES['note_file']['name'];
            $uploadFileDir = '../uploads/';
            
            // Tạo thư mục uploads nếu chưa có
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            
            $dest_path = $uploadFileDir . time()  . basename($fileName);
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Lưu đường dẫn khớp với cột file_path trong database của bạn
                $filePath = 'uploads/' . basename($dest_path);
            }
        }

        // Câu lệnh INSERT khớp với các cột trong CSDL: title, content, file_path
        $stmt = $conn->prepare("INSERT INTO notes (title, content, file_path) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $content, $filePath);
        
        if ($stmt->execute()) {
            $noteId = $stmt->insert_id;
            sendServiceBusMessage($noteId, $content);
            $message = "Thêm ghi chú thành công!";
        } else {
            $message = "Lỗi khi lưu ghi chú vào cơ sở dữ liệu.";
        }
        $stmt->close();
    }
}

// Lấy danh sách ghi chú từ database
$result = $conn->query("SELECT * FROM notes ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>CloudNotes - Quản lý ghi chú đám mây</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h1 class="mb-4">CloudNotes - Ứng dụng Quản lý Ghi chú</h1>

    <?php if ($message !== ""): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">Tạo ghi chú mới</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <div class="mb-3">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nội dung</label>
                    <textarea name="content" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">File đính kèm</label>
                    <input type="file" name="note_file" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Lưu Ghi Chú</button>
            </form>
        </div>
    </div>

    <h2>Danh sách ghi chú</h2>
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Nội dung</th>
                <th>File đính kèm</th>
                <th>Thời gian (`created_at`)</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['content']) ?></td>
                        <td>
                            <?php if (!empty($row['file_path'])): ?>
                                <a href="../<?= htmlspecialchars($row['file_path']) ?>" target="_blank">Xem file</a>
                            <?php else: ?>
                                <span class="text-muted">NULL</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $row['created_at'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">Chưa có ghi chú nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>