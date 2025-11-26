<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $gender = $_POST['gender'];
    $phone = trim($_POST['phone']);
    $mail = trim($_POST['mail']);
    $birth_date = $_POST['birth_date'];

    // 驗證
    if (empty($name) || empty($gender) || empty($phone) || empty($mail) || empty($birth_date)) {
        $error = '所有欄位都必須填寫！';
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $error = '電子郵件格式不正確！';
    } elseif (!preg_match('/^09\d{8}$/', $phone)) {
        $error = '電話號碼格式不正確！';
    } else {
        // 檢查郵件是否已存在
        $sql = "SELECT * FROM person WHERE mail = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $mail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = '此電子郵件已被註冊！';
        } else {
            // 生成ID
            $person_id = generateID('P', 'person', 'id');
            $visitor_id = generateID('V', 'visitor', 'v_id');

            // 開始交易
            $conn->begin_transaction();

            try {
                // 插入 person 表
                $sql = "INSERT INTO person (id, gender, name, phone, mail, birth_date) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssss", $person_id, $gender, $name, $phone, $mail, $birth_date);
                $stmt->execute();

                // 插入 visitor 表
                $sql = "INSERT INTO visitor (id, v_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $person_id, $visitor_id);
                $stmt->execute();

                $conn->commit();
                $success = '註冊成功！請登入。';

                // 2秒後跳轉到登入頁面
                header("refresh:2;url=login.php");

            } catch (Exception $e) {
                $conn->rollback();
                $error = '註冊失敗：' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員註冊 - 博物館展覽系統</title>
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
                <li><a href="login.php">登入</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 2rem auto;">
            <h2 class="card-title">👤 會員註冊</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">姓名 *</label>
                    <input type="text" id="name" name="name" class="form-control" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="gender">性別 *</label>
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="">請選擇</option>
                        <option value="男" <?php echo (isset($_POST['gender']) && $_POST['gender'] == '男') ? 'selected' : ''; ?>>男</option>
                        <option value="女" <?php echo (isset($_POST['gender']) && $_POST['gender'] == '女') ? 'selected' : ''; ?>>女</option>
                        <option value="其他" <?php echo (isset($_POST['gender']) && $_POST['gender'] == '其他') ? 'selected' : ''; ?>>其他</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="phone">電話號碼 *</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="0912345678" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    <small class="form-text">請輸入10位手機號碼</small>
                </div>

                <div class="form-group">
                    <label for="mail">電子郵件 *</label>
                    <input type="email" id="mail" name="mail" class="form-control" required value="<?php echo isset($_POST['mail']) ? htmlspecialchars($_POST['mail']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="birth_date">出生日期 *</label>
                    <input type="date" id="birth_date" name="birth_date" class="form-control" required value="<?php echo isset($_POST['birth_date']) ? $_POST['birth_date'] : ''; ?>">
                </div>

                <div class="alert alert-info" style="margin-top: 1rem;">
                    <strong>ℹ️ 登入說明：</strong>註冊後,您的電話號碼將作為登入密碼使用。
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">註冊</button>

                <p style="text-align: center; margin-top: 1rem;">
                    已經有帳號？ <a href="login.php" style="color: #5c4a32;">立即登入</a>
                </p>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 博物館展覽管理系統. All rights reserved.</p>
    </footer>
</body>
</html>
