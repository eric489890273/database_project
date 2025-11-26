<?php
require_once '../config.php';

if (!isLoggedIn() || !isCurator()) {
    header("Location: ../login.php");
    exit();
}

$message = '';

// 處理刪除會員
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_member'])) {
    $member_id = $_POST['member_id'];
    $sql = "DELETE FROM person WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $member_id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">會員已刪除！</div>';
    } else {
        $message = '<div class="alert alert-danger">刪除失敗：' . $conn->error . '</div>';
    }
}

// 處理修改會員
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_member'])) {
    $member_id = $_POST['member_id'];
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $mail = trim($_POST['mail']);
    $gender = $_POST['gender'];

    if (!empty($name) && !empty($phone) && !empty($mail)) {
        $sql = "UPDATE person SET name = ?, phone = ?, mail = ?, gender = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $phone, $mail, $gender, $member_id);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">會員資料已更新！</div>';
        } else {
            $message = '<div class="alert alert-danger">更新失敗：' . $conn->error . '</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">所有欄位都必須填寫！</div>';
    }
}

// 查詢條件
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : '';
$search_value = isset($_GET['search_value']) ? trim($_GET['search_value']) : '';

// 建立查詢
$where_clause = "WHERE v.id IS NOT NULL";
if (!empty($search_value)) {
    switch ($search_type) {
        case 'name':
            $where_clause .= " AND p.name LIKE '%" . $conn->real_escape_string($search_value) . "%'";
            break;
        case 'phone':
            $where_clause .= " AND p.phone LIKE '%" . $conn->real_escape_string($search_value) . "%'";
            break;
        case 'mail':
            $where_clause .= " AND p.mail LIKE '%" . $conn->real_escape_string($search_value) . "%'";
            break;
    }
}

// 查詢會員
$sql = "SELECT p.id, p.name, p.gender, p.phone, p.mail, p.birth_date,
        v.v_id,
        (SELECT COUNT(*) FROM ticket WHERE id = p.id) as ticket_count,
        (SELECT COUNT(*) FROM visit WHERE id = p.id) as visit_count,
        (SELECT COUNT(*) FROM feedback WHERE id = p.id AND fb_id NOT LIKE 'PWD_%') as feedback_count
        FROM person p
        LEFT JOIN visitor v ON p.id = v.id
        $where_clause
        ORDER BY p.id DESC";
$members = $conn->query($sql);

// 總會員數
$total_sql = "SELECT COUNT(*) as total FROM person p LEFT JOIN visitor v ON p.id = v.id WHERE v.id IS NOT NULL";
$total_members = $conn->query($total_sql)->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員管理 - 博物館展覽系統</title>
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
        .edit-form {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: #fffef9;
            border-radius: 3px;
            border: 2px solid #8b7355;
        }
        .edit-form.active {
            display: block;
        }
        .search-form {
            background: #f5f0e8;
            padding: 1.5rem;
            border-radius: 3px;
            margin-bottom: 1.5rem;
            border: 1px solid #d4c4a8;
        }
    </style>
    <script>
        function toggleEdit(id) {
            var form = document.getElementById('edit-' + id);
            if (form.classList.contains('active')) {
                form.classList.remove('active');
            } else {
                // 關閉其他編輯表單
                var allForms = document.querySelectorAll('.edit-form');
                allForms.forEach(function(f) {
                    f.classList.remove('active');
                });
                form.classList.add('active');
            }
        }
        function confirmDelete(name) {
            return confirm('確定要刪除會員「' + name + '」嗎？此操作將刪除該會員的所有相關資料（票券、參觀記錄、回饋等）。');
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
            <h2 class="card-title">👥 會員管理</h2>

            <?php echo $message; ?>

            <!-- 搜尋表單 -->
            <div class="search-form">
                <h3 style="color: #5c4a32; margin-bottom: 1rem;">🔍 查詢會員</h3>
                <form method="GET" action="">
                    <div style="display: grid; grid-template-columns: 200px 1fr auto; gap: 1rem; align-items: end;">
                        <div class="form-group" style="margin: 0;">
                            <label for="search_type">查詢方式</label>
                            <select id="search_type" name="search_type" class="form-control" required>
                                <option value="name" <?php echo $search_type == 'name' ? 'selected' : ''; ?>>依姓名查詢</option>
                                <option value="phone" <?php echo $search_type == 'phone' ? 'selected' : ''; ?>>依電話查詢</option>
                                <option value="mail" <?php echo $search_type == 'mail' ? 'selected' : ''; ?>>依電子郵件查詢</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label for="search_value">查詢內容</label>
                            <input type="text" id="search_value" name="search_value" class="form-control" placeholder="輸入查詢內容..." value="<?php echo htmlspecialchars($search_value); ?>">
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="submit" class="btn btn-primary">查詢</button>
                            <a href="member_manage.php" class="btn">清除</a>
                        </div>
                    </div>
                </form>
            </div>

            <?php if ($members && $members->num_rows > 0): ?>
                <div style="margin-bottom: 1rem; padding: 1rem; background: linear-gradient(135deg, #5c4a32 0%, #8b7355 100%); color: #f5f0e8; border-radius: 3px;">
                    <h3 style="margin: 0;">
                        <?php if (!empty($search_value)): ?>
                            查詢結果：<?php echo $members->num_rows; ?> 人 (總會員數：<?php echo $total_members; ?> 人)
                        <?php else: ?>
                            總會員數：<?php echo $members->num_rows; ?> 人
                        <?php endif; ?>
                    </h3>
                </div>

                <div style="overflow-x: auto;">
                    <div style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                        <!-- 表頭 -->
                        <div style="background: linear-gradient(135deg, #5c4a32 0%, #8b7355 100%); color: #f5f0e8; padding: 0.75rem;">
                            <div style="display: grid; grid-template-columns: 70px 100px 50px 100px 180px 90px 60px 60px 60px 140px; gap: 0.5rem; font-weight: bold; font-size: 0.9rem;">
                                <div>會員編號</div>
                                <div>姓名</div>
                                <div>性別</div>
                                <div>電話</div>
                                <div>電子郵件</div>
                                <div>出生日期</div>
                                <div>購票數</div>
                                <div>參觀次數</div>
                                <div>回饋數</div>
                                <div>操作</div>
                            </div>
                        </div>

                        <!-- 資料列 -->
                        <div>
                            <?php while($member = $members->fetch_assoc()): ?>
                                <div style="border-bottom: 1px solid #eee; background: #fff;">
                                    <div style="padding: 0.75rem;">
                                        <div style="display: grid; grid-template-columns: 70px 100px 50px 100px 180px 90px 60px 60px 60px 140px; gap: 0.5rem; align-items: center; font-size: 0.9rem;">
                                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo htmlspecialchars($member['id']); ?></div>
                                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><strong><?php echo htmlspecialchars($member['name']); ?></strong></div>
                                            <div><?php echo htmlspecialchars($member['gender']); ?></div>
                                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo htmlspecialchars($member['phone']); ?></div>
                                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($member['mail']); ?>"><?php echo htmlspecialchars($member['mail']); ?></div>
                                            <div><?php echo date('Y-m-d', strtotime($member['birth_date'])); ?></div>
                                            <div><span class="badge badge-primary"><?php echo $member['ticket_count']; ?></span></div>
                                            <div><span class="badge badge-success"><?php echo $member['visit_count']; ?></span></div>
                                            <div><span class="badge badge-warning"><?php echo $member['feedback_count']; ?></span></div>
                                            <div class="action-buttons">
                                                <button onclick="toggleEdit('<?php echo $member['id']; ?>')" class="btn btn-primary btn-small">修改</button>
                                                <form method="POST" style="display: inline; margin: 0;" onsubmit="return confirmDelete('<?php echo htmlspecialchars($member['name']); ?>');">
                                                    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                    <button type="submit" name="delete_member" class="btn btn-danger btn-small">刪除</button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- 編輯表單 -->
                                        <div id="edit-<?php echo $member['id']; ?>" class="edit-form">
                                                <form method="POST">
                                                    <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                    <h4 style="color: #5c4a32; margin-bottom: 1rem;">修改會員資料</h4>
                                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                                                        <div class="form-group">
                                                            <label>會員編號</label>
                                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($member['id']); ?>" readonly>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>姓名 *</label>
                                                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($member['name']); ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>性別 *</label>
                                                            <select name="gender" class="form-control" required>
                                                                <option value="男" <?php echo $member['gender'] == '男' ? 'selected' : ''; ?>>男</option>
                                                                <option value="女" <?php echo $member['gender'] == '女' ? 'selected' : ''; ?>>女</option>
                                                                <option value="其他" <?php echo $member['gender'] == '其他' ? 'selected' : ''; ?>>其他</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>電話 *</label>
                                                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($member['phone']); ?>" pattern="09\d{8}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>電子郵件 *</label>
                                                            <input type="email" name="mail" class="form-control" value="<?php echo htmlspecialchars($member['mail']); ?>" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>出生日期</label>
                                                            <input type="text" class="form-control" value="<?php echo date('Y-m-d', strtotime($member['birth_date'])); ?>" readonly>
                                                        </div>
                                                    </div>
                                            <div style="margin-top: 1rem;">
                                                <button type="submit" name="update_member" class="btn btn-success">確認修改</button>
                                                <button type="button" onclick="toggleEdit('<?php echo $member['id']; ?>')" class="btn">取消</button>
                                            </div>
                                        </form>
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
                            查無符合條件的會員資料
                        <?php else: ?>
                            目前沒有會員資料
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- 會員統計 -->
        <div class="card">
            <h2 class="card-title">📊 會員統計</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <?php
                // 性別統計
                $sql = "SELECT gender, COUNT(*) as count FROM person p
                        LEFT JOIN visitor v ON p.id = v.id
                        WHERE v.id IS NOT NULL
                        GROUP BY gender";
                $gender_stats = $conn->query($sql);
                ?>

                <div style="background: #f5f0e8; padding: 1.5rem; border-radius: 3px; border: 1px solid #d4c4a8;">
                    <h3 style="color: #5c4a32; margin-bottom: 1rem;">性別分布</h3>
                    <?php while($stat = $gender_stats->fetch_assoc()): ?>
                        <div style="margin-bottom: 0.5rem;">
                            <strong><?php echo htmlspecialchars($stat['gender']); ?>：</strong>
                            <?php echo $stat['count']; ?> 人
                        </div>
                    <?php endwhile; ?>
                </div>

                <?php
                // 年齡統計
                $sql = "SELECT
                        SUM(CASE WHEN YEAR(CURDATE()) - YEAR(birth_date) < 18 THEN 1 ELSE 0 END) as under_18,
                        SUM(CASE WHEN YEAR(CURDATE()) - YEAR(birth_date) BETWEEN 18 AND 30 THEN 1 ELSE 0 END) as age_18_30,
                        SUM(CASE WHEN YEAR(CURDATE()) - YEAR(birth_date) BETWEEN 31 AND 50 THEN 1 ELSE 0 END) as age_31_50,
                        SUM(CASE WHEN YEAR(CURDATE()) - YEAR(birth_date) > 50 THEN 1 ELSE 0 END) as over_50
                        FROM person p
                        LEFT JOIN visitor v ON p.id = v.id
                        WHERE v.id IS NOT NULL";
                $age_stats = $conn->query($sql)->fetch_assoc();
                ?>

                <div style="background: #f5f0e8; padding: 1.5rem; border-radius: 3px; border: 1px solid #d4c4a8;">
                    <h3 style="color: #5c4a32; margin-bottom: 1rem;">年齡分布</h3>
                    <div style="margin-bottom: 0.5rem;"><strong>18歲以下：</strong><?php echo $age_stats['under_18']; ?> 人</div>
                    <div style="margin-bottom: 0.5rem;"><strong>18-30歲：</strong><?php echo $age_stats['age_18_30']; ?> 人</div>
                    <div style="margin-bottom: 0.5rem;"><strong>31-50歲：</strong><?php echo $age_stats['age_31_50']; ?> 人</div>
                    <div style="margin-bottom: 0.5rem;"><strong>50歲以上：</strong><?php echo $age_stats['over_50']; ?> 人</div>
                </div>

                <?php
                // 活躍度統計
                $sql = "SELECT
                        SUM(CASE WHEN ticket_count > 0 THEN 1 ELSE 0 END) as purchased,
                        SUM(CASE WHEN visit_count > 0 THEN 1 ELSE 0 END) as visited,
                        SUM(CASE WHEN feedback_count > 0 THEN 1 ELSE 0 END) as feedbacked
                        FROM (
                            SELECT p.id,
                            (SELECT COUNT(*) FROM ticket WHERE id = p.id) as ticket_count,
                            (SELECT COUNT(*) FROM visit WHERE id = p.id) as visit_count,
                            (SELECT COUNT(*) FROM feedback WHERE id = p.id AND fb_id NOT LIKE 'PWD_%') as feedback_count
                            FROM person p
                            LEFT JOIN visitor v ON p.id = v.id
                            WHERE v.id IS NOT NULL
                        ) as stats";
                $activity_stats = $conn->query($sql)->fetch_assoc();
                ?>

                <div style="background: #f5f0e8; padding: 1.5rem; border-radius: 3px; border: 1px solid #d4c4a8;">
                    <h3 style="color: #5c4a32; margin-bottom: 1rem;">活躍度統計</h3>
                    <div style="margin-bottom: 0.5rem;"><strong>已購票會員：</strong><?php echo $activity_stats['purchased']; ?> 人</div>
                    <div style="margin-bottom: 0.5rem;"><strong>有參觀記錄：</strong><?php echo $activity_stats['visited']; ?> 人</div>
                    <div style="margin-bottom: 0.5rem;"><strong>有回饋記錄：</strong><?php echo $activity_stats['feedbacked']; ?> 人</div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 博物館展覽管理系統. All rights reserved.</p>
    </footer>
</body>
</html>
