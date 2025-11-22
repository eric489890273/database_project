<?php
require_once '../config.php';

// 檢查是否為策展人
if (!isLoggedIn() || !isCurator()) {
    header("Location: ../login.php");
    exit();
}

$user = getCurrentUser();

// 統計資訊
$sql = "SELECT COUNT(*) as count FROM exhibition";
$exhibition_count = $conn->query($sql)->fetch_assoc()['count'];

$sql = "SELECT COUNT(*) as count FROM artifact";
$artifact_count = $conn->query($sql)->fetch_assoc()['count'];

$sql = "SELECT COUNT(*) as count FROM visitor";
$visitor_count = $conn->query($sql)->fetch_assoc()['count'];

$sql = "SELECT COUNT(*) as count FROM ticket";
$ticket_count = $conn->query($sql)->fetch_assoc()['count'];

// 最新展覽
$sql = "SELECT e.e_name, e.e_Date, p.name as curator_name,
        (SELECT COUNT(*) FROM artifact WHERE e_name = e.e_name) as artifact_count
        FROM exhibition e
        LEFT JOIN curator c ON e.id = c.id
        LEFT JOIN person p ON c.id = p.id
        ORDER BY e.e_Date DESC
        LIMIT 5";
$recent_exhibitions = $conn->query($sql);

// 最新購票
$sql = "SELECT t.t_id, t.price, p.name
        FROM ticket t
        LEFT JOIN person p ON t.id = p.id
        ORDER BY t.t_id DESC
        LIMIT 5";
$recent_tickets = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>後台管理 - 博物館展覽系統</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- 導航列 -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="../index.php" class="navbar-brand">
                <span>🏛️</span> 博物館展覽系統 - 後台管理
            </a>
            <ul class="navbar-menu">
                <li><a href="index.php">管理首頁</a></li>
                <li><a href="exhibition_manage.php">展覽管理</a></li>
                <li><a href="artifact_manage.php">藝術品管理</a></li>
                <li><a href="member_manage.php">會員管理</a></li>
                <li><a href="../index.php">返回前台</a></li>
                <li><a href="../logout.php" class="btn">登出</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="hero" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h1 style="color: white; background: none; -webkit-text-fill-color: white;">歡迎，<?php echo htmlspecialchars($user['name']); ?></h1>
            <p style="color: rgba(255,255,255,0.9);">策展人管理後台</p>
        </div>

        <!-- 統計卡片 -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div style="font-size: 3rem; text-align: center;">🎨</div>
                <h3 style="text-align: center; margin: 1rem 0; font-size: 2.5rem;"><?php echo $exhibition_count; ?></h3>
                <p style="text-align: center; font-size: 1.2rem;">展覽總數</p>
            </div>

            <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                <div style="font-size: 3rem; text-align: center;">🖼️</div>
                <h3 style="text-align: center; margin: 1rem 0; font-size: 2.5rem;"><?php echo $artifact_count; ?></h3>
                <p style="text-align: center; font-size: 1.2rem;">藝術品總數</p>
            </div>

            <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                <div style="font-size: 3rem; text-align: center;">👥</div>
                <h3 style="text-align: center; margin: 1rem 0; font-size: 2.5rem;"><?php echo $visitor_count; ?></h3>
                <p style="text-align: center; font-size: 1.2rem;">會員總數</p>
            </div>

            <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                <div style="font-size: 3rem; text-align: center;">🎫</div>
                <h3 style="text-align: center; margin: 1rem 0; font-size: 2.5rem;"><?php echo $ticket_count; ?></h3>
                <p style="text-align: center; font-size: 1.2rem;">售出票券</p>
            </div>
        </div>

        <!-- 快速功能 -->
        <div class="card">
            <h2 class="card-title">⚡ 快速功能</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                <a href="exhibition_manage.php?action=add" class="btn btn-primary" style="padding: 1.5rem; font-size: 1.1rem;">
                    ➕ 新增展覽
                </a>
                <a href="artifact_manage.php?action=add" class="btn btn-success" style="padding: 1.5rem; font-size: 1.1rem;">
                    🖼️ 新增藝術品
                </a>
                <a href="exhibition_manage.php" class="btn btn-secondary" style="padding: 1.5rem; font-size: 1.1rem;">
                    📋 查看展覽
                </a>
                <a href="member_manage.php" class="btn btn-secondary" style="padding: 1.5rem; font-size: 1.1rem;">
                    👥 查看會員
                </a>
            </div>
        </div>

        <!-- 最新展覽 -->
        <div class="card">
            <h2 class="card-title">🎨 最新展覽</h2>
            <?php if ($recent_exhibitions && $recent_exhibitions->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>展覽名稱</th>
                            <th>展覽日期</th>
                            <th>策展人</th>
                            <th>藝術品數</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($ex = $recent_exhibitions->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ex['e_name']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($ex['e_Date'])); ?></td>
                                <td><?php echo htmlspecialchars($ex['curator_name']); ?></td>
                                <td><?php echo $ex['artifact_count']; ?> 件</td>
                                <td>
                                    <a href="../exhibition_detail.php?name=<?php echo urlencode($ex['e_name']); ?>" class="btn btn-primary" style="padding: 0.3rem 1rem; font-size: 0.9rem;">查看</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>暫無展覽</p>
            <?php endif; ?>
        </div>

        <!-- 最新購票記錄 -->
        <div class="card">
            <h2 class="card-title">🎫 最新購票記錄</h2>
            <?php if ($recent_tickets && $recent_tickets->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>票券編號</th>
                            <th>購買人</th>
                            <th>票價</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($ticket = $recent_tickets->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticket['t_id']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['name']); ?></td>
                                <td><strong>NT$ <?php echo $ticket['price']; ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>暫無購票記錄</p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 博物館展覽管理系統. All rights reserved.</p>
    </footer>
</body>
</html>
