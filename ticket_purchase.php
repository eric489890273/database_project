<?php
require_once 'config.php';

// 檢查是否登入
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $price = intval($_POST['price']);

    if ($price >= 0) {
        $user_id = $_SESSION['user_id'];
        $ticket_id = generateID('T', 'ticket', 't_id');

        try {
            // 插入票券
            $sql = "INSERT INTO ticket (t_id, price, id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sis", $ticket_id, $price, $user_id);
            $stmt->execute();

            $message = '<div class="alert alert-success">購票成功！票券編號：' . $ticket_id . '，金額：NT$ ' . $price . '</div>';

        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">購票失敗：' . $e->getMessage() . '</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">請選擇票種！</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>購買票券 - 博物館展覽系統</title>
    <link rel="stylesheet" href="css/style.css">
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
                <li><a href="logout.php" class="btn">登出</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card" style="max-width: 700px; margin: 2rem auto;">
            <h2 class="card-title">🎫 購買票券</h2>

            <?php echo $message; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="price">選擇票種 *</label>
                    <select id="price" name="price" class="form-control" required>
                        <option value="">請選擇票種</option>
                        <option value="300">全票 - NT$ 300</option>
                        <option value="150">學生票 - NT$ 150</option>
                        <option value="200">優待票 - NT$ 200</option>
                        <option value="0">免費票 - NT$ 0</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success" style="width: 100%;">確認購買</button>
            </form>

            <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                <h3 style="color: #667eea; margin-bottom: 1rem;">📋 購票說明</h3>
                <ul style="color: #666; line-height: 1.8;">
                    <li>全票：適用於一般成人</li>
                    <li>學生票：需出示有效學生證</li>
                    <li>優待票：適用於65歲以上長者及身心障礙人士</li>
                    <li>免費票：6歲以下兒童免費入場</li>
                    <li>購票後請妥善保管票券編號</li>
                </ul>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 博物館展覽管理系統. All rights reserved.</p>
    </footer>
</body>
</html>
