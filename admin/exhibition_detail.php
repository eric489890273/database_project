<?php
require_once '../config.php';

if (!isLoggedIn() || !isCurator()) {
    header("Location: ../login.php");
    exit();
}

$message = '';
$e_name = isset($_GET['name']) ? $_GET['name'] : '';

if (empty($e_name)) {
    header("Location: exhibition_manage.php");
    exit();
}

// 處理修改展覽
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_exhibition'])) {
    $new_e_name = trim($_POST['e_name']);
    $new_e_date = $_POST['e_date'];
    $new_curator_id = $_POST['curator_id'];

    if (!empty($new_e_name) && !empty($new_e_date) && !empty($new_curator_id)) {
        $conn->begin_transaction();

        try {
            // 如果展覽名稱改變,需要更新所有相關表
            if ($new_e_name != $e_name) {
                // 檢查新名稱是否已存在
                $sql = "SELECT * FROM exhibition WHERE e_name = ? AND e_name != ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $new_e_name, $e_name);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    throw new Exception("展覽名稱已存在!");
                }

                // 更新 exhibit 表
                $sql = "UPDATE exhibit SET e_name = ? WHERE e_name = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $new_e_name, $e_name);
                $stmt->execute();

                // 更新 guided 表
                $sql = "UPDATE guided SET e_name = ? WHERE e_name = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $new_e_name, $e_name);
                $stmt->execute();

                // 更新 visit 表
                $sql = "UPDATE visit SET e_name = ? WHERE e_name = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $new_e_name, $e_name);
                $stmt->execute();
            }

            // 更新展覽資訊
            $sql = "UPDATE exhibition SET e_name = ?, e_Date = ?, id = ? WHERE e_name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $new_e_name, $new_e_date, $new_curator_id, $e_name);
            $stmt->execute();

            $conn->commit();
            $message = '<div class="alert alert-success">展覽資料已更新!</div>';
            $e_name = $new_e_name; // 更新當前展覽名稱

        } catch (Exception $e) {
            $conn->rollback();
            $message = '<div class="alert alert-danger">更新失敗: ' . $e->getMessage() . '</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">所有欄位都必須填寫!</div>';
    }
}

// 查詢展覽詳細資訊
$sql = "SELECT e.e_name, e.e_Date, e.id, p.name as curator_name, p.phone, p.mail
        FROM exhibition e
        LEFT JOIN curator c ON e.id = c.id
        LEFT JOIN person p ON c.id = p.id
        WHERE e.e_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $e_name);
$stmt->execute();
$exhibition = $stmt->get_result()->fetch_assoc();

if (!$exhibition) {
    header("Location: exhibition_manage.php");
    exit();
}

// 查詢展覽的藝術品
$sql = "SELECT a.art_id, a.art_name, GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as creators
        FROM artifact a
        INNER JOIN exhibit ex ON a.art_id = ex.art_id
        LEFT JOIN `create` c ON a.art_id = c.art_id
        LEFT JOIN creator cr ON c.id = cr.id
        LEFT JOIN person p ON cr.id = p.id
        WHERE ex.e_name = ?
        GROUP BY a.art_id, a.art_name
        ORDER BY a.art_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $e_name);
$stmt->execute();
$artifacts = $stmt->get_result();

// 查詢展覽的參觀記錄
$sql = "SELECT v.id, p.name
        FROM visit v
        LEFT JOIN visitor vi ON v.id = vi.id
        LEFT JOIN person p ON vi.id = p.id
        WHERE v.e_name = ?
        ORDER BY p.name
        LIMIT 20";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $e_name);
$stmt->execute();
$visits = $stmt->get_result();

// 查詢所有策展人
$curators = $conn->query("SELECT c.id, p.name FROM curator c LEFT JOIN person p ON c.id = p.id ORDER BY p.name");

// 統計資料
$sql = "SELECT COUNT(*) as total FROM exhibit WHERE e_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $e_name);
$stmt->execute();
$artifact_count = $stmt->get_result()->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM visit WHERE e_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $e_name);
$stmt->execute();
$visit_count = $stmt->get_result()->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($exhibition['e_name']); ?> - 展覽詳細資料</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .info-card {
            background: linear-gradient(135deg, #5c4a32 0%, #8b7355 100%);
            color: #f5f0e8;
            padding: 2rem;
            border-radius: 3px;
            margin-bottom: 2rem;
            border: 2px solid #8b7355;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        .info-item h4 {
            margin: 0 0 0.5rem 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .info-item p {
            margin: 0;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .section-card {
            background: #fffef9;
            padding: 1.5rem;
            border-radius: 3px;
            margin-bottom: 1.5rem;
            border: 1px solid #d4c4a8;
        }
        .section-title {
            color: #5c4a32;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #8b7355;
        }
        .edit-section {
            background: #f5f0e8;
            padding: 1.5rem;
            border-radius: 3px;
            margin-bottom: 2rem;
            border: 1px solid #d4c4a8;
        }
    </style>
</head>
<body>
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
        <div style="margin-bottom: 1rem;">
            <a href="exhibition_manage.php" class="btn">← 返回展覽管理</a>
        </div>

        <div class="card">
            <h2 class="card-title">🎨 <?php echo htmlspecialchars($exhibition['e_name']); ?></h2>

            <?php echo $message; ?>

            <!-- 展覽資訊卡片 -->
            <div class="info-card">
                <h3 style="margin: 0 0 1rem 0;">展覽資訊</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <h4>📅 展覽日期</h4>
                        <p><?php echo date('Y年m月d日', strtotime($exhibition['e_Date'])); ?></p>
                    </div>
                    <div class="info-item">
                        <h4>👤 策展人</h4>
                        <p><?php echo htmlspecialchars($exhibition['curator_name']); ?></p>
                    </div>
                    <div class="info-item">
                        <h4>🖼️ 藝術品數量</h4>
                        <p><?php echo $artifact_count; ?> 件</p>
                    </div>
                    <div class="info-item">
                        <h4>👥 參觀人數</h4>
                        <p><?php echo $visit_count; ?> 人</p>
                    </div>
                </div>
            </div>

            <!-- 修改展覽資訊 -->
            <div class="edit-section">
                <h3 class="section-title">✏️ 修改展覽資訊</h3>
                <form method="POST">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label>展覽名稱 *</label>
                            <input type="text" name="e_name" class="form-control" value="<?php echo htmlspecialchars($exhibition['e_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>展覽日期 *</label>
                            <input type="date" name="e_date" class="form-control" value="<?php echo $exhibition['e_Date']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>策展人 *</label>
                            <select name="curator_id" class="form-control" required>
                                <?php while($curator = $curators->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($curator['id']); ?>" <?php echo $curator['id'] == $exhibition['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($curator['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <button type="submit" name="update_exhibition" class="btn btn-success">💾 儲存變更</button>
                    </div>
                </form>
            </div>

            <!-- 展覽藝術品列表 -->
            <div class="section-card">
                <h3 class="section-title">🖼️ 展覽藝術品 (<?php echo $artifact_count; ?> 件)</h3>
                <?php if ($artifacts && $artifacts->num_rows > 0): ?>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">編號</th>
                                    <th>藝術品名稱</th>
                                    <th>創作者</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($art = $artifacts->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($art['art_id']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($art['art_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($art['creators'] ?? '未知'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 2rem;">此展覽目前沒有藝術品</p>
                <?php endif; ?>
            </div>

            <!-- 參觀記錄 -->
            <div class="section-card">
                <h3 class="section-title">👥 參觀記錄 (總計: <?php echo $visit_count; ?> 人次)</h3>
                <?php if ($visits && $visits->num_rows > 0): ?>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 200px;">會員編號</th>
                                    <th>會員姓名</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($visit = $visits->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($visit['id']); ?></td>
                                        <td><?php echo htmlspecialchars($visit['name']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($visit_count > 20): ?>
                        <p style="text-align: center; color: #999; margin-top: 1rem; font-size: 0.9rem;">
                            僅顯示 20 筆記錄
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 2rem;">此展覽目前沒有參觀記錄</p>
                <?php endif; ?>
            </div>

            <!-- 策展人聯絡資訊 -->
            <div class="section-card">
                <h3 class="section-title">📞 策展人聯絡資訊</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <strong>姓名:</strong> <?php echo htmlspecialchars($exhibition['curator_name']); ?>
                    </div>
                    <div>
                        <strong>電話:</strong> <?php echo htmlspecialchars($exhibition['phone']); ?>
                    </div>
                    <div>
                        <strong>電子郵件:</strong> <?php echo htmlspecialchars($exhibition['mail']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 博物館展覽管理系統. All rights reserved.</p>
    </footer>
</body>
</html>
