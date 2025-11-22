<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>展覽列表 - 博物館展覽系統</title>
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
                <li><a href="exhibition_list.php">展覽列表</a></li>
                <li><a href="ticket_purchase.php">購買票券</a></li>
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
        <div class="card">
            <h2 class="card-title">🎨 所有展覽</h2>

            <div class="exhibition-grid">
                <?php
                $sql = "SELECT e.e_name, e.e_Date, p.name as curator_name,
                        (SELECT COUNT(*) FROM artifact WHERE e_name = e.e_name) as artifact_count
                        FROM exhibition e
                        LEFT JOIN curator c ON e.id = c.id
                        LEFT JOIN person p ON c.id = p.id
                        ORDER BY e.e_Date DESC";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0):
                    while($row = $result->fetch_assoc()):
                ?>
                    <div class="exhibition-card">
                        <div class="exhibition-image">
                            🖼️
                        </div>
                        <div class="exhibition-content">
                            <h3 class="exhibition-title"><?php echo htmlspecialchars($row['e_name']); ?></h3>
                            <p class="exhibition-date">📅 <?php echo date('Y年m月d日', strtotime($row['e_Date'])); ?></p>
                            <p class="exhibition-curator">👤 策展人: <?php echo htmlspecialchars($row['curator_name']); ?></p>
                            <p style="color: #999; font-size: 0.9rem;">🎨 藝術品數量: <?php echo $row['artifact_count']; ?> 件</p>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <a href="exhibition_detail.php?name=<?php echo urlencode($row['e_name']); ?>" class="btn btn-primary" style="flex: 1;">查看詳情</a>
                                <?php if (isLoggedIn() && isVisitor()): ?>
                                    <a href="ticket_purchase.php?exhibition=<?php echo urlencode($row['e_name']); ?>" class="btn btn-success">購票</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php
                    endwhile;
                else:
                ?>
                    <p>目前沒有展覽資訊</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 博物館展覽管理系統. All rights reserved.</p>
    </footer>
</body>
</html>
