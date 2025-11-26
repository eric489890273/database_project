<?php
require_once '../config.php';

if (!isLoggedIn() || !isCurator()) {
    header("Location: ../login.php");
    exit();
}

$message = '';

// 處理新增展覽
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_exhibition'])) {
    $e_name = trim($_POST['e_name']);
    $e_date = $_POST['e_date'];
    $curator_id = $_SESSION['user_id'];

    if (!empty($e_name) && !empty($e_date)) {
        // 檢查展覽是否已存在
        $sql = "SELECT * FROM exhibition WHERE e_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $e_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = '<div class="alert alert-danger">展覽名稱已存在!</div>';
        } else {
            $sql = "INSERT INTO exhibition (e_name, e_Date, id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $e_name, $e_date, $curator_id);

            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">展覽新增成功!</div>';
            } else {
                $message = '<div class="alert alert-danger">新增失敗: ' . $conn->error . '</div>';
            }
        }
    } else {
        $message = '<div class="alert alert-danger">請填寫所有必填欄位!</div>';
    }
}

// 處理刪除展覽
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_exhibition'])) {
    $e_name = $_POST['e_name'];

    $sql = "DELETE FROM exhibition WHERE e_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $e_name);

    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">展覽已刪除!</div>';
    } else {
        $message = '<div class="alert alert-danger">刪除失敗: ' . $conn->error . '</div>';
    }
}

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
$sql = "SELECT e.e_name, e.e_Date, e.id, p.name as curator_name,
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

// 查詢所有策展人(curator)
$curators = $conn->query("SELECT c.id, p.name FROM curator c LEFT JOIN person p ON c.id = p.id ORDER BY p.name");
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>展覽管理 - 博物館展覽系統</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .btn-small {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
        .search-form {
            background: #f5f0e8;
            padding: 1.5rem;
            border-radius: 3px;
            margin-bottom: 1.5rem;
            border: 1px solid #d4c4a8;
        }
        .edit-form {
            display: none;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #fffef9;
            border-radius: 3px;
            border: 2px solid #8b7355;
        }
        .edit-form.active {
            display: block;
        }
    </style>
    <script>
        function confirmDelete(name) {
            return confirm('確定要刪除展覽「' + name + '」嗎?這將刪除所有相關資料!');
        }
        function toggleAddForm() {
            var form = document.getElementById('add-form');
            if (form.classList.contains('active')) {
                form.classList.remove('active');
            } else {
                form.classList.add('active');
            }
        }
    </script>
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
        <div class="card">
            <h2 class="card-title">🎨 展覽管理</h2>

            <?php echo $message; ?>

            <!-- 搜尋表單 -->
            <div class="search-form">
                <h3 style="color: #5c4a32; margin-bottom: 1rem;">🔍 查詢展覽</h3>
                <form method="GET" action="">
                    <div style="display: grid; grid-template-columns: 200px 1fr auto auto; gap: 1rem; align-items: end;">
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
                            <a href="exhibition_manage.php" class="btn">清除</a>
                        </div>
                        <button type="button" onclick="toggleAddForm()" class="btn btn-success">➕ 新增展覽</button>
                    </div>
                </form>
            </div>

            <!-- 新增展覽表單 -->
            <div id="add-form" class="edit-form">
                <h3 style="color: #5c4a32; margin-bottom: 1rem;">新增展覽</h3>
                <form method="POST">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label>展覽名稱 *</label>
                            <input type="text" name="e_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>展覽日期 *</label>
                            <input type="date" name="e_date" class="form-control" required>
                        </div>
                    </div>
                    <div style="margin-top: 1rem;">
                        <button type="submit" name="add_exhibition" class="btn btn-success">確認新增</button>
                        <button type="button" onclick="toggleAddForm()" class="btn">取消</button>
                    </div>
                </form>
            </div>

            <!-- 展覽列表 -->
            <?php if ($exhibitions && $exhibitions->num_rows > 0): ?>
                <div style="margin-bottom: 1rem; padding: 1rem; background: linear-gradient(135deg, #5c4a32 0%, #8b7355 100%); color: #f5f0e8; border-radius: 3px;">
                    <h3 style="margin: 0;">
                        <?php if (!empty($search_value)): ?>
                            查詢結果: <?php echo $exhibitions->num_rows; ?> 個展覽 (總展覽數: <?php echo $total_exhibitions; ?> 個)
                        <?php else: ?>
                            總展覽數: <?php echo $exhibitions->num_rows; ?> 個
                        <?php endif; ?>
                    </h3>
                </div>

                <div style="overflow-x: auto;">
                    <div style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                        <!-- 表頭 -->
                        <div style="background: linear-gradient(135deg, #5c4a32 0%, #8b7355 100%); color: #f5f0e8; padding: 0.75rem;">
                            <div style="display: grid; grid-template-columns: 200px 100px 120px 90px 90px 140px; gap: 0.5rem; font-weight: bold; font-size: 0.9rem;">
                                <div>展覽名稱</div>
                                <div>展覽日期</div>
                                <div>策展人</div>
                                <div>藝術品數</div>
                                <div>參觀人數</div>
                                <div>操作</div>
                            </div>
                        </div>

                        <!-- 資料列 -->
                        <div>
                            <?php while($ex = $exhibitions->fetch_assoc()): ?>
                                <div style="border-bottom: 1px solid #eee; background: #fff;">
                                    <div style="padding: 0.75rem;">
                                        <div style="display: grid; grid-template-columns: 200px 100px 120px 90px 90px 140px; gap: 0.5rem; align-items: center; font-size: 0.9rem;">
                                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($ex['e_name']); ?>">
                                                <strong><?php echo htmlspecialchars($ex['e_name']); ?></strong>
                                            </div>
                                            <div><?php echo date('Y-m-d', strtotime($ex['e_Date'])); ?></div>
                                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($ex['curator_name']); ?>">
                                                <?php echo htmlspecialchars($ex['curator_name']); ?>
                                            </div>
                                            <div><?php echo $ex['artifact_count']; ?> 件</div>
                                            <div><?php echo $ex['visitor_count']; ?> 人</div>
                                            <div class="action-buttons">
                                                <a href="exhibition_detail.php?name=<?php echo urlencode($ex['e_name']); ?>" class="btn btn-primary btn-small">查看/修改</a>
                                                <form method="POST" style="display: inline; margin: 0;" onsubmit="return confirmDelete('<?php echo htmlspecialchars($ex['e_name']); ?>');">
                                                    <input type="hidden" name="e_name" value="<?php echo htmlspecialchars($ex['e_name']); ?>">
                                                    <button type="submit" name="delete_exhibition" class="btn btn-danger btn-small">刪除</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
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
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 博物館展覽管理系統. All rights reserved.</p>
    </footer>
</body>
</html>
