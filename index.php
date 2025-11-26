<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>博物館展覽管理系統 - 首頁</title>
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

    <!-- 主要內容 -->
    <div class="container">
        <!-- 英雄區塊 -->
        <div class="hero">
            <h1>歡迎來到博物館展覽系統</h1>
            <p>探索藝術、體驗文化、豐富生活</p>
        </div>

        <!-- 所有展覽 -->
        <div class="card">
            <h2 class="card-title">🎨 所有展覽</h2>
            <div class="exhibition-grid">
                <?php
                $sql = "SELECT e.e_name, e.e_Date, p.name as curator_name,
                        (SELECT COUNT(*) FROM exhibit WHERE e_name = e.e_name) as artifact_count
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
                            <div style="margin-top: 1rem;">
                                <a href="exhibition_detail.php?name=<?php echo urlencode($row['e_name']); ?>" class="btn btn-primary" style="width: 100%;">查看詳情</a>
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
            <div style="text-align: center; margin-top: 2rem;">
                <a href="exhibition_list.php" class="btn btn-primary" style="padding: 1rem 3rem; font-size: 1.1rem;">查看更多展覽 →</a>
            </div>
        </div>

        <!-- 系統特色 -->
        <div class="card">
            <h2 class="card-title">✨ 系統特色</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-top: 1.5rem;">
                <div style="text-align: center; padding: 1.5rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎫</div>
                    <h3 style="color: #667eea; margin-bottom: 0.5rem;">線上購票</h3>
                    <p style="color: #666;">輕鬆便捷的線上購票系統</p>
                </div>
                <div style="text-align: center; padding: 1.5rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎨</div>
                    <h3 style="color: #667eea; margin-bottom: 0.5rem;">展覽管理</h3>
                    <p style="color: #666;">完善的展覽資訊管理功能</p>
                </div>
                <div style="text-align: center; padding: 1.5rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
                    <h3 style="color: #667eea; margin-bottom: 0.5rem;">會員系統</h3>
                    <p style="color: #666;">個人化的會員服務體驗</p>
                </div>
                <div style="text-align: center; padding: 1.5rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">💬</div>
                    <h3 style="color: #667eea; margin-bottom: 0.5rem;">意見回饋</h3>
                    <p style="color: #666;">即時的展覽意見反饋機制</p>
                </div>
            </div>
        </div>

        <!-- 統計資訊 -->
        <div class="card">
            <h2 class="card-title">📊 系統統計</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-top: 1.5rem;">
                <?php
                // 統計展覽數量
                $sql = "SELECT COUNT(*) as count FROM exhibition";
                $result = $conn->query($sql);
                $exhibition_count = $result->fetch_assoc()['count'];

                // 統計藝術品數量
                $sql = "SELECT COUNT(*) as count FROM artifact";
                $result = $conn->query($sql);
                $artifact_count = $result->fetch_assoc()['count'];

                // 統計會員數量
                $sql = "SELECT COUNT(*) as count FROM visitor";
                $result = $conn->query($sql);
                $visitor_count = $result->fetch_assoc()['count'];

                // 統計今日展覽
                $sql = "SELECT COUNT(*) as count FROM exhibition WHERE e_Date = CURDATE()";
                $result = $conn->query($sql);
                $today_exhibition = $result->fetch_assoc()['count'];
                ?>

                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold;"><?php echo $exhibition_count; ?></div>
                    <div>場展覽</div>
                </div>
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold;"><?php echo $artifact_count; ?></div>
                    <div>件藝術品</div>
                </div>
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold;"><?php echo $visitor_count; ?></div>
                    <div>位會員</div>
                </div>
                <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold;"><?php echo $today_exhibition; ?></div>
                    <div>今日展覽</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 頁尾 -->
    <footer class="footer">
        <p>&copy; 2025 博物館展覽管理系統. All rights reserved.</p>
    </footer>
</body>
</html>