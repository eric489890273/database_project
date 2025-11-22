<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mail = trim($_POST['mail']);
    $phone = trim($_POST['phone']);

    if (empty($mail) || empty($phone)) {
        $error = '請輸入電子郵件和電話！';
    } else {
        // 查詢使用者
        $sql = "SELECT p.*, v.v_id
                FROM person p
                LEFT JOIN visitor v ON p.id = v.id
                WHERE p.mail = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $mail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // 直接比對電話號碼
            if ($user['phone'] === $phone) {
                // 登入成功
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_mail'] = $user['mail'];

                // 跳轉到首頁
                header("Location: index.php");
                exit();
            } else {
                $error = '電話號碼錯誤！';
            }
        } else {
            $error = '此電子郵件尚未註冊！';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員登入 - 博物館展覽系統</title>
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
                <li><a href="feedback.php">網站回饋</a></li>
                <li><a href="register.php">註冊</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card" style="max-width: 500px; margin: 2rem auto;">
            <h2 class="card-title">🔐 會員登入</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="mail">電子郵件</label>
                    <input type="email" id="mail" name="mail" class="form-control" required value="<?php echo isset($_POST['mail']) ? htmlspecialchars($_POST['mail']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="phone">電話號碼(密碼)</label>
                    <input type="tel" id="phone" name="phone" class="form-control" required pattern="09\d{8}" placeholder="0912345678">
                    <small class="form-text">請輸入您註冊時使用的電話號碼</small>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">登入</button>

                <p style="text-align: center; margin-top: 1rem;">
                    還沒有帳號？ <a href="register.php" style="color: #667eea;">立即註冊</a>
                </p>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 博物館展覽管理系統. All rights reserved.</p>
    </footer>
</body>
</html>
