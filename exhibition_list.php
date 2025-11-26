<?php
require_once 'config.php';

// 查詢條件
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : '';
$search_value = isset($_GET['search_value']) ? trim($_GET['search_value']) : '';

// 建立查詢
$where_clause = "WHERE 1=1";
if (!empty($search_value)) {
    switch ($search_type) {
        case 'name':
            $where_clause .= " AND e.e_name LIKE '%" . $conn->real_escape_string($search_value) . "%'";
            break;
        case 'date':
            $where_clause .= " AND e.e_Date LIKE '%" . $conn->real_escape_string($search_value) . "%'";
            break;
        case 'curator':
            $where_clause .= " AND p.name LIKE '%" . $conn->real_escape_string($search_value) . "%'";
            break;
    }
}

// 查詢展覽
$sql = "SELECT e.e_name, e.e_Date, p.name as curator_name,
        (SELECT COUNT(*) FROM exhibit WHERE e_name = e.e_name) as artifact_count,
        (SELECT COUNT(*) FROM visit WHERE e_name = e.e_name) as visitor_count
        FROM exhibition e
        LEFT JOIN curator c ON e.id = c.id
        LEFT JOIN person p ON c.id = p.id
        $where_clause
        ORDER BY e.e_Date DESC";
$exhibitions = $conn->query($sql);

// 總展覽數
$total_sql = "SELECT COUNT(*) as total FROM exhibition";
$total_exhibitions = $conn->query($total_sql)->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>展覽列表 - 博物館展覽系統</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .search-form {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
    </style>
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
        <div class="card">
            <h2 class="card-title">🎨 展覽列表</h2>

            <!-- 搜尋表單 -->
            <div class="search-form">
                <h3 style="color: #667eea; margin-bottom: 1rem;">🔍 查詢展覽</h3>
                <form method="GET" action="">
                    <div style="display: grid; grid-template-columns: 200px 1fr auto; gap: 1rem; align-items: end;">
                        <div class="form-group" style="margin: 0;">
                            <label for="search_type">查詢方式</label>
                            <select id="search_type" name="search_type" class="form-control" required>
                                <option value="name" <?php echo $search_type == 'name' ? 'selected' : ''; ?>>依展覽名稱查詢</option>
                                <option value="date" <?php echo $search_type == 'date' ? 'selected' : ''; ?>>依日期查詢</option>
                                <option value="curator" <?php echo $search_type == 'curator' ? 'selected' : ''; ?>>依策展人查詢</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label for="search_value">查詢內容</label>
                            <input type="text" id="search_value" name="search_value" class="form-control" placeholder="輸入查詢內容..." value="<?php echo htmlspecialchars($search_value); ?>">
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="submit" class="btn btn-primary">查詢</button>
                            <a href="exhibition_list.php" class="btn">清除</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- 展覽統計 -->
            <?php if ($exhibitions && $exhibitions->num_rows > 0): ?>
                <div style="margin-bottom: 1.5rem; padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px;">
                    <h3 style="margin: 0;">
                        <?php if (!empty($search_value)): ?>
                            查詢結果: <?php echo $exhibitions->num_rows; ?> 個展覽 (總展覽數: <?php echo $total_exhibitions; ?> 個)
                        <?php else: ?>
                            總展覽數: <?php echo $exhibitions->num_rows; ?> 個
                        <?php endif; ?>
                    </h3>
                </div>

                <!-- 展覽列表 -->
                <div class="exhibition-grid">
                    <?php while($ex = $exhibitions->fetch_assoc()): ?>
                        <div class="exhibition-card">
                            <div class="exhibition-image">
                                🖼️
                            </div>
                            <div class="exhibition-content">
                                <h3 class="exhibition-title"><?php echo htmlspecialchars($ex['e_name']); ?></h3>
                                <p class="exhibition-date">📅 <?php echo date('Y年m月d日', strtotime($ex['e_Date'])); ?></p>
                                <p class="exhibition-curator">👤 策展人: <?php echo htmlspecialchars($ex['curator_name']); ?></p>
                                <p style="color: #999; font-size: 0.9rem;">🎨 藝術品: <?php echo $ex['artifact_count']; ?> 件</p>
                                <p style="color: #999; font-size: 0.9rem;">👥 參觀人數: <?php echo $ex['visitor_count']; ?> 人</p>
                                <div style="margin-top: 1rem;">
                                    <a href="exhibition_detail.php?name=<?php echo urlencode($ex['e_name']); ?>" class="btn btn-primary" style="width: 100%;">查看詳情</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; color: #999;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                    <p style="font-size: 1.2rem;">
                        <?php if (!empty($search_value)): ?>
                            查無符合條件的展覽資料
                        <?php else: ?>
                            目前沒有展覽資料
                        <?php endif; ?>
                    </p>
                    <a href="exhibition_list.php" class="btn btn-primary" style="margin-top: 1rem;">返回所有展覽</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 博物館展覽管理系統. All rights reserved.</p>
    </footer>
</body>
</html>
