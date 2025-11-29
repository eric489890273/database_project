<?php
require_once 'config.php';

$exhibition_name = isset($_GET['name']) ? $_GET['name'] : '';

if (empty($exhibition_name)) {
    header("Location: exhibition_list.php");
    exit();
}

// 查詢展覽資訊
$sql = "SELECT e.e_name, e.e_start, e.e_end, e.theme, p.name as curator_name, p.id as curator_id, p.phone, p.mail
        FROM exhibition e
        LEFT JOIN curator c ON e.id = c.id
        LEFT JOIN person p ON c.id = p.id
        WHERE e.e_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $exhibition_name);
$stmt->execute();
$exhibition = $stmt->get_result()->fetch_assoc();

if (!$exhibition) {
    header("Location: exhibition_list.php");
    exit();
}

// 查詢展覽的藝術品
$sql = "SELECT a.art_id, a.art_name, GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as creators
        FROM artifact a
        INNER JOIN exhibit ex ON a.art_id = ex.art_id
        LEFT JOIN `create` cr ON a.art_id = cr.art_id
        LEFT JOIN creator c ON cr.id = c.id
        LEFT JOIN person p ON c.id = p.id
        WHERE ex.e_name = ?
        GROUP BY a.art_id, a.art_name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $exhibition_name);
$stmt->execute();
$artifacts = $stmt->get_result();

// 查詢導覽員
$sql = "SELECT p.name, p.phone
        FROM guide g
        INNER JOIN guided gd ON g.id = gd.id
        LEFT JOIN person p ON g.id = p.id
        WHERE gd.e_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $exhibition_name);
$stmt->execute();
$guides = $stmt->get_result();


?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($exhibition['e_name']); ?> - 博物館展覽系統</title>
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
        <!-- 展覽資訊 -->
        <div class="card">
            <h2 class="card-title">🎨 <?php echo htmlspecialchars($exhibition['e_name']); ?></h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 1.5rem;">
                <div>
                    <h3 style="color: #5c4a32; margin-bottom: 1rem;">展覽資訊</h3>
                    <p><strong>展覽日期：</strong> <?php echo date('Y/m/d', strtotime($exhibition['e_start'])); ?> ~ <?php echo date('Y/m/d', strtotime($exhibition['e_end'])); ?></p>
                    <p><strong>展覽主題：</strong> <?php echo htmlspecialchars($exhibition['theme']); ?></p>
                    <p><strong>策展人：</strong> <?php echo htmlspecialchars($exhibition['curator_name']); ?></p>
                    <p><strong>聯絡電話：</strong> <?php echo htmlspecialchars($exhibition['phone']); ?></p>
                    <p><strong>電子郵件：</strong> <?php echo htmlspecialchars($exhibition['mail']); ?></p>

                    <?php if (isLoggedIn() && isVisitor()): ?>
                        <a href="ticket_purchase.php?exhibition=<?php echo urlencode($exhibition['e_name']); ?>" class="btn btn-success" style="margin-top: 1rem;">購買票券</a>
                    <?php endif; ?>
                </div>

                <div>
                    <h3 style="color: #5c4a32; margin-bottom: 1rem;">導覽員</h3>
                    <?php if ($guides && $guides->num_rows > 0): ?>
                        <ul style="list-style: none; padding: 0;">
                            <?php while($guide = $guides->fetch_assoc()): ?>
                                <li style="padding: 0.5rem; background: #f5f0e8; margin-bottom: 0.5rem; border-radius: 3px;">
                                    <strong><?php echo htmlspecialchars($guide['name']); ?></strong><br>
                                    📞 <?php echo htmlspecialchars($guide['phone']); ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p>暫無導覽員資訊</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 藝術品列表 -->
        <div class="card">
            <h2 class="card-title">🖼️ 展覽藝術品</h2>

            <?php if ($artifacts && $artifacts->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>藝術品編號</th>
                            <th>藝術品名稱</th>
                            <th>創作者</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($artifact = $artifacts->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($artifact['art_id']); ?></td>
                                <td><?php echo htmlspecialchars($artifact['art_name']); ?></td>
                                <td><?php echo htmlspecialchars($artifact['creators'] ?? '未知'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>此展覽暫無藝術品</p>
            <?php endif; ?>
        </div>


    </div>

    <footer class="footer">
        <p>&copy; 2025 博物館展覽管理系統. All rights reserved.</p>
    </footer>
</body>
</html>
