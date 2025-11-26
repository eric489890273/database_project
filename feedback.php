<?php
require_once 'config.php';

$message = '';

// 處理網站回饋提交
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback']) && isLoggedIn() && isVisitor()) {
    $content = trim($_POST['content']);

    if (!empty($content)) {
        $user_id = $_SESSION['user_id'];
        $fb_id = generateID('FB', 'feedback', 'fb_id');

        $sql = "INSERT INTO feedback (id, fb_id, content) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $user_id, $fb_id, $content);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">感謝您的回饋！我們會持續改進服務品質。</div>';
        } else {
            $message = '<div class="alert alert-danger">提交失敗，請稍後再試。</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">請輸入回饋內容。</div>';
    }
}

// 查詢所有網站回饋
$sql = "SELECT f.content, p.name, f.fb_id, p.id
        FROM feedback f
        LEFT JOIN person p ON f.id = p.id
        WHERE f.fb_id NOT LIKE 'PWD_%'
        ORDER BY f.fb_id DESC";
$feedbacks = $conn->query($sql);

// 統計資訊
$sql = "SELECT COUNT(DISTINCT f.id) as feedback_users
        FROM feedback f
        WHERE f.fb_id NOT LIKE 'PWD_%'";
$stats = $conn->query($sql)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>網站回饋 - 博物館展覽系統</title>
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
                <?php if (isLoggedIn()): ?>
                    <li><a href="member_profile.php">會員資料</a></li>
                    <?php if (isCurator()): ?>
                        <li><a href="admin/index.php">後台管理</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="btn">登出</a></li>
                <?php else: ?>
                    <li><a href="login.php">登入</a></li>
                    <li><a href="register.php" class="btn">註冊</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- 標題區塊 -->
        <div class="hero">
            <h1>💬 網站回饋</h1>
            <p>您的意見是我們進步的動力</p>
        </div>

        <!-- 提交回饋區 -->
        <div class="card">
            <h2 class="card-title">📝 分享您的使用體驗</h2>

            <?php echo $message; ?>

            <?php if (isLoggedIn() && isVisitor()): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="content">您的寶貴意見 *</label>
                        <textarea id="content" name="content" class="form-control" rows="5" required placeholder="請分享您對本網站的使用體驗、建議或任何想法...&#10;例如：展覽資訊、購票流程、網站介面、服務品質等"></textarea>
                        <small class="form-text">請盡量詳細描述，以便我們更好地改進服務</small>
                    </div>
                    <button type="submit" name="submit_feedback" class="btn btn-primary">提交回饋</button>
                </form>
            <?php else: ?>
                <div class="alert alert-info">
                    請先 <a href="login.php" style="color: #5c4a32; font-weight: bold;">登入</a>
                    才能提交回饋。還不是會員？
                    <a href="register.php" style="color: #5c4a32; font-weight: bold;">立即註冊</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- 回饋統計 -->
        <div class="card">
            <h2 class="card-title">📊 回饋統計</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
                <div style="background: linear-gradient(135deg, #5c4a32 0%, #8b7355 100%); color: #f5f0e8; padding: 1.5rem; border-radius: 3px; text-align: center; border: 1px solid #8b7355;">
                    <div style="font-size: 2.5rem; font-weight: bold;"><?php echo $feedbacks->num_rows; ?></div>
                    <div>則回饋</div>
                </div>
                <div style="background: linear-gradient(135deg, #7a5a4a 0%, #a87a6a 100%); color: #f5f0e8; padding: 1.5rem; border-radius: 3px; text-align: center; border: 1px solid #a87a6a;">
                    <div style="font-size: 2.5rem; font-weight: bold;"><?php echo $stats['feedback_users']; ?></div>
                    <div>位用戶參與</div>
                </div>
            </div>
        </div>

        <!-- 所有回饋 -->
        <div class="card">
            <h2 class="card-title">💭 訪客回饋</h2>

            <?php if ($feedbacks && $feedbacks->num_rows > 0): ?>
                <div style="display: grid; gap: 1rem; margin-top: 1rem;">
                    <?php while($feedback = $feedbacks->fetch_assoc()): ?>
                        <div style="background: #f5f0e8; padding: 1.5rem; border-radius: 3px; border-left: 4px solid #8b7355;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #5c4a32 0%, #8b7355 100%); display: flex; align-items: center; justify-content: center; color: #f5f0e8; font-weight: bold;">
                                        <?php echo mb_substr($feedback['name'], 0, 1, 'UTF-8'); ?>
                                    </div>
                                    <div>
                                        <strong style="color: #5c4a32; font-size: 1.1rem;"><?php echo htmlspecialchars($feedback['name']); ?></strong>
                                        <div style="color: #7a6a5a; font-size: 0.85rem;">編號: <?php echo htmlspecialchars($feedback['fb_id']); ?></div>
                                    </div>
                                </div>
                                <?php if (isLoggedIn() && $_SESSION['user_id'] == $feedback['id']): ?>
                                    <span class="badge badge-primary">我的回饋</span>
                                <?php endif; ?>
                            </div>
                            <p style="color: #333; line-height: 1.6; margin: 0; white-space: pre-wrap;"><?php echo htmlspecialchars($feedback['content']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: #999;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                    <p style="font-size: 1.2rem;">尚無回饋，成為第一位留下寶貴意見的訪客！</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- 回饋指南 -->
        <div class="card">
            <h2 class="card-title">💡 回饋指南</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-top: 1.5rem;">
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎨</div>
                    <h3 style="color: #5c4a32; margin-bottom: 0.5rem;">展覽內容</h3>
                    <p style="color: #666;">展覽品質、內容豐富度、藝術品呈現方式</p>
                </div>
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">💻</div>
                    <h3 style="color: #5c4a32; margin-bottom: 0.5rem;">網站功能</h3>
                    <p style="color: #666;">介面設計、操作流暢度、功能完整性</p>
                </div>
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎫</div>
                    <h3 style="color: #5c4a32; margin-bottom: 0.5rem;">購票體驗</h3>
                    <p style="color: #666;">購票流程、價格合理性、票券選擇</p>
                </div>
                <div style="text-align: center; padding: 1rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🤝</div>
                    <h3 style="color: #5c4a32; margin-bottom: 0.5rem;">服務品質</h3>
                    <p style="color: #666;">客戶服務、導覽品質、整體滿意度</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 博物館展覽管理系統. All rights reserved.</p>
    </footer>
</body>
</html>
