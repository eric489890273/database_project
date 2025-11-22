<?php
require_once 'config.php';

// 檢查是否登入
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$user = getCurrentUser();
$message = '';

// 處理刪除票券
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_ticket'])) {
    $ticket_id = $_POST['ticket_id'];
    $sql = "DELETE FROM ticket WHERE t_id = ? AND id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $ticket_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">票券已刪除！</div>';
    } else {
        $message = '<div class="alert alert-danger">刪除失敗！</div>';
    }
}

// 處理修改票券
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_ticket'])) {
    $ticket_id = $_POST['ticket_id'];
    $new_price = intval($_POST['new_price']);
    $sql = "UPDATE ticket SET price = ? WHERE t_id = ? AND id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $new_price, $ticket_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">票券已更新！</div>';
    } else {
        $message = '<div class="alert alert-danger">更新失敗！</div>';
    }
}

// 處理刪除回饋
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_feedback'])) {
    $fb_id = $_POST['fb_id'];
    $sql = "DELETE FROM feedback WHERE fb_id = ? AND id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $fb_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">回饋已刪除！</div>';
    } else {
        $message = '<div class="alert alert-danger">刪除失敗！</div>';
    }
}

// 處理修改回饋
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_feedback'])) {
    $fb_id = $_POST['fb_id'];
    $new_content = trim($_POST['new_content']);
    if (!empty($new_content)) {
        $sql = "UPDATE feedback SET content = ? WHERE fb_id = ? AND id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $new_content, $fb_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">回饋已更新！</div>';
        } else {
            $message = '<div class="alert alert-danger">更新失敗！</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">回饋內容不能為空！</div>';
    }
}

// 處理資料更新
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $mail = trim($_POST['mail']);

    if (empty($name) || empty($phone) || empty($mail)) {
        $message = '<div class="alert alert-danger">所有欄位都必須填寫！</div>';
    } elseif (!preg_match('/^09\d{8}$/', $phone)) {
        $message = '<div class="alert alert-danger">電話號碼格式不正確！</div>';
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="alert alert-danger">電子郵件格式不正確！</div>';
    } else {
        $user_id = $_SESSION['user_id'];

        $sql = "UPDATE person SET name = ?, phone = ?, mail = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $phone, $mail, $user_id);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">資料更新成功！</div>';
            $_SESSION['user_name'] = $name;
            $_SESSION['user_mail'] = $mail;
            $user = getCurrentUser(); // 重新載入資料
        } else {
            $message = '<div class="alert alert-danger">更新失敗，請稍後再試。</div>';
        }
    }
}

// 查詢購票記錄
$sql = "SELECT t.t_id, t.price
        FROM ticket t
        WHERE t.id = ?
        ORDER BY t.t_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$tickets = $stmt->get_result();

// 查詢參觀記錄
$sql = "SELECT v.e_name, e.e_Date, p.name as curator_name
        FROM visit v
        LEFT JOIN exhibition e ON v.e_name = e.e_name
        LEFT JOIN curator c ON e.id = c.id
        LEFT JOIN person p ON c.id = p.id
        WHERE v.id = ?
        ORDER BY e.e_Date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$visits = $stmt->get_result();

// 查詢我的回饋
$sql = "SELECT f.fb_id, f.content
        FROM feedback f
        WHERE f.id = ? AND f.fb_id NOT LIKE 'PWD_%'
        ORDER BY f.fb_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$feedbacks = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員資料 - 博物館展覽系統</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .btn-small {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
        .edit-form {
            display: none;
            margin-top: 0.5rem;
            padding: 1rem;
            background: #fff;
            border-radius: 5px;
            border: 2px solid #667eea;
        }
        .edit-form.active {
            display: block;
        }
    </style>
    <script>
        function toggleEdit(id) {
            var form = document.getElementById('edit-' + id);
            if (form.classList.contains('active')) {
                form.classList.remove('active');
            } else {
                form.classList.add('active');
            }
        }
        function confirmDelete(type, id) {
            return confirm('確定要刪除這筆' + type + '記錄嗎？');
        }
    </script>
</head>
<body>
    <!-- 導航列 -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">
                <span>🏛️</span> 博物館展覽系統
            </a>
            <ul class="navbar-menu">
                <li><a href="index.php">首頁</a></li>
                <li><a href="ticket_purchase.php">購買票券</a></li>
                <li><a href="feedback.php">網站回饋</a></li>
                <li><a href="member_profile.php">會員資料</a></li>
                <?php if (isCurator()): ?>
                    <li><a href="admin/index.php">後台管理</a></li>
                <?php endif; ?>
                <li><a href="logout.php" class="btn">登出</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- 個人資料 -->
        <div class="card">
            <h2 class="card-title">👤 個人資料</h2>

            <?php echo $message; ?>

            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <div class="form-group">
                        <label for="name">姓名</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>性別</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['gender']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="phone">電話</label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="mail">電子郵件</label>
                        <input type="email" id="mail" name="mail" class="form-control" value="<?php echo htmlspecialchars($user['mail']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>出生日期</label>
                        <input type="text" class="form-control" value="<?php echo date('Y年m月d日', strtotime($user['birth_date'])); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>會員編號</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['id']); ?>" readonly>
                    </div>
                </div>

                <button type="submit" name="update_profile" class="btn btn-primary">更新資料</button>
            </form>
        </div>

        <!-- 購票記錄 -->
        <div class="card">
            <h2 class="card-title">🎫 購票記錄</h2>

            <?php if ($tickets && $tickets->num_rows > 0): ?>
                <div style="display: grid; gap: 1rem;">
                    <?php while($ticket = $tickets->fetch_assoc()): ?>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; border-left: 4px solid #667eea;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div>
                                    <div style="font-weight: bold; color: #667eea;">票券編號: <?php echo htmlspecialchars($ticket['t_id']); ?></div>
                                    <div style="font-size: 1.2rem; margin-top: 0.5rem;"><strong>NT$ <?php echo $ticket['price']; ?></strong></div>
                                </div>
                                <div class="action-buttons">
                                    <button onclick="toggleEdit('ticket-<?php echo $ticket['t_id']; ?>')" class="btn btn-primary btn-small">修改</button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirmDelete('票券', '<?php echo $ticket['t_id']; ?>');">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['t_id']; ?>">
                                        <button type="submit" name="delete_ticket" class="btn btn-danger btn-small">刪除</button>
                                    </form>
                                </div>
                            </div>

                            <div id="edit-ticket-<?php echo $ticket['t_id']; ?>" class="edit-form">
                                <form method="POST">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['t_id']; ?>">
                                    <div class="form-group">
                                        <label>修改票價</label>
                                        <select name="new_price" class="form-control" required>
                                            <option value="300" <?php echo $ticket['price'] == 300 ? 'selected' : ''; ?>>全票 - NT$ 300</option>
                                            <option value="150" <?php echo $ticket['price'] == 150 ? 'selected' : ''; ?>>學生票 - NT$ 150</option>
                                            <option value="200" <?php echo $ticket['price'] == 200 ? 'selected' : ''; ?>>優待票 - NT$ 200</option>
                                            <option value="0" <?php echo $ticket['price'] == 0 ? 'selected' : ''; ?>>免費票 - NT$ 0</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="update_ticket" class="btn btn-success">確認修改</button>
                                    <button type="button" onclick="toggleEdit('ticket-<?php echo $ticket['t_id']; ?>')" class="btn">取消</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>尚無購票記錄</p>
            <?php endif; ?>
        </div>

        <!-- 參觀記錄 -->
        <div class="card">
            <h2 class="card-title">📅 參觀記錄</h2>

            <?php if ($visits && $visits->num_rows > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
                    <?php while($visit = $visits->fetch_assoc()): ?>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; border-left: 4px solid #667eea;">
                            <h3 style="color: #667eea; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($visit['e_name']); ?></h3>
                            <p style="color: #666; margin: 0;">📅 <?php echo date('Y年m月d日', strtotime($visit['e_Date'])); ?></p>
                            <p style="color: #666; margin: 0;">👤 策展人: <?php echo htmlspecialchars($visit['curator_name']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>尚無參觀記錄</p>
            <?php endif; ?>
        </div>

        <!-- 我的回饋 -->
        <div class="card">
            <h2 class="card-title">💬 我的回饋</h2>

            <?php if ($feedbacks && $feedbacks->num_rows > 0): ?>
                <?php while($feedback = $feedbacks->fetch_assoc()): ?>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid #667eea;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <div style="color: #999; font-size: 0.9rem; margin-bottom: 0.5rem;">編號: <?php echo htmlspecialchars($feedback['fb_id']); ?></div>
                                <p style="color: #333; margin: 0;"><?php echo nl2br(htmlspecialchars($feedback['content'])); ?></p>
                            </div>
                            <div class="action-buttons">
                                <button onclick="toggleEdit('feedback-<?php echo $feedback['fb_id']; ?>')" class="btn btn-primary btn-small">修改</button>
                                <form method="POST" style="display: inline;" onsubmit="return confirmDelete('回饋', '<?php echo $feedback['fb_id']; ?>');">
                                    <input type="hidden" name="fb_id" value="<?php echo $feedback['fb_id']; ?>">
                                    <button type="submit" name="delete_feedback" class="btn btn-danger btn-small">刪除</button>
                                </form>
                            </div>
                        </div>

                        <div id="edit-feedback-<?php echo $feedback['fb_id']; ?>" class="edit-form">
                            <form method="POST">
                                <input type="hidden" name="fb_id" value="<?php echo $feedback['fb_id']; ?>">
                                <div class="form-group">
                                    <label>修改回饋內容</label>
                                    <textarea name="new_content" class="form-control" rows="4" required><?php echo htmlspecialchars($feedback['content']); ?></textarea>
                                </div>
                                <button type="submit" name="update_feedback" class="btn btn-success">確認修改</button>
                                <button type="button" onclick="toggleEdit('feedback-<?php echo $feedback['fb_id']; ?>')" class="btn">取消</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>尚無回饋記錄</p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 博物館展覽管理系統. All rights reserved.</p>
    </footer>
</body>
</html>
