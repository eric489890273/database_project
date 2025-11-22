<?php
require_once '../config.php';

if (!isLoggedIn() || !isCurator()) {
    header("Location: ../login.php");
    exit();
}

$message = '';

// 處理刪除藝術品
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_artifact'])) {
    $art_id = $_POST['art_id'];
    $sql = "DELETE FROM artifact WHERE art_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $art_id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">藝術品已刪除!</div>';
    } else {
        $message = '<div class="alert alert-danger">刪除失敗: ' . $conn->error . '</div>';
    }
}

// 處理修改藝術品
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_artifact'])) {
    $art_id = $_POST['art_id'];
    $art_name = trim($_POST['art_name']);
    $e_name = $_POST['e_name'];
    $creator_type = $_POST['creator_type'];

    if (!empty($art_name) && !empty($e_name)) {
        $conn->begin_transaction();

        try {
            $sql = "UPDATE artifact SET art_name = ?, e_name = ? WHERE art_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $art_name, $e_name, $art_id);
            $stmt->execute();

            // 更新 exhibit 表
            $sql = "UPDATE exhibit SET e_name = ? WHERE art_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $e_name, $art_id);
            $stmt->execute();

            // 處理創作者更新
            // 先刪除現有關聯
            $sql = "DELETE FROM `create` WHERE art_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $art_id);
            $stmt->execute();

            // 根據類型新增創作者關聯
            if ($creator_type == 'existing' && !empty($_POST['creator_ids'])) {
                $sql = "INSERT INTO `create` (id, art_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                foreach ($_POST['creator_ids'] as $creator_id) {
                    $stmt->bind_param("ss", $creator_id, $art_id);
                    $stmt->execute();
                }
            } elseif ($creator_type == 'new') {
                $new_name = trim($_POST['new_creator_name']);
                $new_gender = $_POST['new_creator_gender'];
                $new_phone = trim($_POST['new_creator_phone']);
                $new_mail = trim($_POST['new_creator_mail']);
                $new_birth = $_POST['new_creator_birth'];

                if (!empty($new_name) && !empty($new_gender) && !empty($new_phone) && !empty($new_mail) && !empty($new_birth)) {
                    $person_id = generateID('P', 'person', 'id');
                    $creator_id = generateID('CR', 'creator', 'cr_id');

                    // 插入 person
                    $sql = "INSERT INTO person (id, name, gender, phone, mail, birth_date) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssss", $person_id, $new_name, $new_gender, $new_phone, $new_mail, $new_birth);
                    $stmt->execute();

                    // 插入 creator
                    $sql = "INSERT INTO creator (id, cr_id) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $person_id, $creator_id);
                    $stmt->execute();

                    // 關聯創作者與藝術品
                    $sql = "INSERT INTO `create` (id, art_id) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $person_id, $art_id);
                    $stmt->execute();
                }
            }

            $conn->commit();
            $message = '<div class="alert alert-success">藝術品資料已更新!</div>';
        } catch (Exception $e) {
            $conn->rollback();
            $message = '<div class="alert alert-danger">更新失敗: ' . $e->getMessage() . '</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">所有欄位都必須填寫!</div>';
    }
}

// 處理新增藝術品
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_artifact'])) {
    $art_name = trim($_POST['art_name']);
    $e_name = $_POST['e_name'];
    $creator_type = $_POST['creator_type'];

    if (!empty($art_name) && !empty($e_name)) {
        $art_id = generateID('ART', 'artifact', 'art_id');

        $conn->begin_transaction();

        try {
            // 插入藝術品
            $sql = "INSERT INTO artifact (art_id, art_name, e_name) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $art_id, $art_name, $e_name);
            $stmt->execute();

            // 插入展出關聯
            $sql = "INSERT INTO exhibit (art_id, e_name) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $art_id, $e_name);
            $stmt->execute();

            // 處理創作者
            if ($creator_type == 'existing' && !empty($_POST['creator_ids'])) {
                $sql = "INSERT INTO `create` (id, art_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                foreach ($_POST['creator_ids'] as $creator_id) {
                    $stmt->bind_param("ss", $creator_id, $art_id);
                    $stmt->execute();
                }
            } elseif ($creator_type == 'new') {
                // 新增創作者
                $new_name = trim($_POST['new_creator_name']);
                $new_gender = $_POST['new_creator_gender'];
                $new_phone = trim($_POST['new_creator_phone']);
                $new_mail = trim($_POST['new_creator_mail']);
                $new_birth = $_POST['new_creator_birth'];

                if (!empty($new_name) && !empty($new_gender) && !empty($new_phone) && !empty($new_mail) && !empty($new_birth)) {
                    $person_id = generateID('P', 'person', 'id');
                    $creator_id = generateID('CR', 'creator', 'cr_id');

                    // 插入 person
                    $sql = "INSERT INTO person (id, name, gender, phone, mail, birth_date) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssss", $person_id, $new_name, $new_gender, $new_phone, $new_mail, $new_birth);
                    $stmt->execute();

                    // 插入 creator
                    $sql = "INSERT INTO creator (id, cr_id) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $person_id, $creator_id);
                    $stmt->execute();

                    // 關聯創作者與藝術品
                    $sql = "INSERT INTO `create` (id, art_id) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ss", $person_id, $art_id);
                    $stmt->execute();
                }
            }

            $conn->commit();
            $message = '<div class="alert alert-success">藝術品新增成功! 編號: ' . $art_id . '</div>';

        } catch (Exception $e) {
            $conn->rollback();
            $message = '<div class="alert alert-danger">新增失敗: ' . $e->getMessage() . '</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">請填寫所有必填欄位!</div>';
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
            $where_clause .= " AND a.art_name LIKE '%" . $conn->real_escape_string($search_value) . "%'";
            break;
        case 'exhibition':
            $where_clause .= " AND a.e_name LIKE '%" . $conn->real_escape_string($search_value) . "%'";
            break;
        case 'creator':
            $where_clause .= " AND p.name LIKE '%" . $conn->real_escape_string($search_value) . "%'";
            break;
    }
}

// 查詢藝術品
$sql = "SELECT a.art_id, a.art_name, a.e_name, e.e_Date,
        GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as creators
        FROM artifact a
        LEFT JOIN exhibition e ON a.e_name = e.e_name
        LEFT JOIN `create` c ON a.art_id = c.art_id
        LEFT JOIN creator cr ON c.id = cr.id
        LEFT JOIN person p ON cr.id = p.id
        $where_clause
        GROUP BY a.art_id, a.art_name, a.e_name, e.e_Date
        ORDER BY a.art_id DESC";
$artifacts = $conn->query($sql);

// 總藝術品數
$total_sql = "SELECT COUNT(*) as total FROM artifact";
$total_artifacts = $conn->query($total_sql)->fetch_assoc()['total'];

// 查詢所有展覽
$exhibitions = $conn->query("SELECT e_name FROM exhibition ORDER BY e_Date DESC");

// 查詢所有創作者
$creators = $conn->query("SELECT p.id, p.name FROM creator c LEFT JOIN person p ON c.id = p.id ORDER BY p.name");
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>藝術品管理 - 博物館展覽系統</title>
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
            background: #fff;
            border-radius: 5px;
            border: 2px solid #667eea;
        }
        .edit-form.active {
            display: block;
        }
        .search-form {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        .creator-option {
            display: none;
        }
        .creator-option.active {
            display: block;
        }
    </style>
    <script>
        function toggleEdit(id) {
            var form = document.getElementById('edit-' + id);
            if (form.classList.contains('active')) {
                form.classList.remove('active');
            } else {
                var allForms = document.querySelectorAll('.edit-form');
                allForms.forEach(function(f) {
                    f.classList.remove('active');
                });
                form.classList.add('active');
            }
        }
        function confirmDelete(name) {
            return confirm('確定要刪除藝術品「' + name + '」嗎?');
        }
        function toggleCreatorType(type) {
            document.querySelectorAll('#add-form .creator-option').forEach(function(el) {
                el.classList.remove('active');
            });
            document.getElementById('creator-' + type).classList.add('active');
        }
        function toggleCreatorTypeEdit(artId, type) {
            var editForm = document.getElementById('edit-' + artId);
            editForm.querySelectorAll('.creator-option').forEach(function(el) {
                el.classList.remove('active');
            });
            document.getElementById('creator-' + type + '-' + artId).classList.add('active');
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
            <h2 class="card-title">🖼️ 藝術品管理</h2>

            <?php echo $message; ?>

            <!-- 搜尋表單 -->
            <div class="search-form">
                <h3 style="color: #667eea; margin-bottom: 1rem;">🔍 查詢藝術品</h3>
                <form method="GET" action="">
                    <div style="display: grid; grid-template-columns: 200px 1fr auto auto; gap: 1rem; align-items: end;">
                        <div class="form-group" style="margin: 0;">
                            <label for="search_type">查詢方式</label>
                            <select id="search_type" name="search_type" class="form-control" required>
                                <option value="name" <?php echo $search_type == 'name' ? 'selected' : ''; ?>>依名稱查詢</option>
                                <option value="exhibition" <?php echo $search_type == 'exhibition' ? 'selected' : ''; ?>>依展覽查詢</option>
                                <option value="creator" <?php echo $search_type == 'creator' ? 'selected' : ''; ?>>依創作者查詢</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label for="search_value">查詢內容</label>
                            <input type="text" id="search_value" name="search_value" class="form-control" placeholder="輸入查詢內容..." value="<?php echo htmlspecialchars($search_value); ?>">
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="submit" class="btn btn-primary">查詢</button>
                            <a href="artifact_manage.php" class="btn">清除</a>
                        </div>
                        <button type="button" onclick="toggleAddForm()" class="btn btn-success">➕ 新增藝術品</button>
                    </div>
                </form>
            </div>

            <!-- 新增藝術品表單 -->
            <div id="add-form" class="edit-form" style="margin-bottom: 1.5rem;">
                <h3 style="color: #667eea; margin-bottom: 1rem;">新增藝術品</h3>
                <form method="POST">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label>藝術品名稱 *</label>
                            <input type="text" name="art_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>所屬展覽 *</label>
                            <select name="e_name" class="form-control" required>
                                <option value="">請選擇展覽</option>
                                <?php while($ex = $exhibitions->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($ex['e_name']); ?>">
                                        <?php echo htmlspecialchars($ex['e_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 1rem;">
                        <label>創作者選擇 *</label>
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                            <label style="cursor: pointer;">
                                <input type="radio" name="creator_type" value="existing" onclick="toggleCreatorType('existing')" checked> 選擇既有創作者
                            </label>
                            <label style="cursor: pointer;">
                                <input type="radio" name="creator_type" value="new" onclick="toggleCreatorType('new')"> 新增創作者
                            </label>
                        </div>

                        <!-- 既有創作者 -->
                        <div id="creator-existing" class="creator-option active">
                            <div style="max-height: 150px; overflow-y: auto; border: 2px solid #e0e0e0; border-radius: 8px; padding: 1rem;">
                                <?php
                                $creators->data_seek(0);
                                while($creator = $creators->fetch_assoc()):
                                ?>
                                    <label style="display: block; margin-bottom: 0.5rem; cursor: pointer;">
                                        <input type="checkbox" name="creator_ids[]" value="<?php echo htmlspecialchars($creator['id']); ?>">
                                        <?php echo htmlspecialchars($creator['name']); ?>
                                    </label>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <!-- 新增創作者 -->
                        <div id="creator-new" class="creator-option">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 1rem; border: 2px solid #667eea; border-radius: 8px;">
                                <div class="form-group" style="margin: 0;">
                                    <label>姓名 *</label>
                                    <input type="text" name="new_creator_name" class="form-control">
                                </div>
                                <div class="form-group" style="margin: 0;">
                                    <label>性別 *</label>
                                    <select name="new_creator_gender" class="form-control">
                                        <option value="男">男</option>
                                        <option value="女">女</option>
                                        <option value="其他">其他</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin: 0;">
                                    <label>電話 *</label>
                                    <input type="tel" name="new_creator_phone" class="form-control" pattern="09\d{8}" placeholder="0912345678">
                                </div>
                                <div class="form-group" style="margin: 0;">
                                    <label>電子郵件 *</label>
                                    <input type="email" name="new_creator_mail" class="form-control">
                                </div>
                                <div class="form-group" style="margin: 0;">
                                    <label>出生日期 *</label>
                                    <input type="date" name="new_creator_birth" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 1rem;">
                        <button type="submit" name="add_artifact" class="btn btn-success">確認新增</button>
                        <button type="button" onclick="toggleAddForm()" class="btn">取消</button>
                    </div>
                </form>
            </div>

            <!-- 藝術品列表 -->
            <?php if ($artifacts && $artifacts->num_rows > 0): ?>
                <div style="margin-bottom: 1rem; padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px;">
                    <h3 style="margin: 0;">
                        <?php if (!empty($search_value)): ?>
                            查詢結果: <?php echo $artifacts->num_rows; ?> 件 (總藝術品數: <?php echo $total_artifacts; ?> 件)
                        <?php else: ?>
                            總藝術品數: <?php echo $artifacts->num_rows; ?> 件
                        <?php endif; ?>
                    </h3>
                </div>

                <div style="overflow-x: auto;">
                    <div style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
                        <!-- 表頭 -->
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.75rem;">
                            <div style="display: grid; grid-template-columns: 80px 200px 180px 100px 180px 140px; gap: 0.5rem; font-weight: bold; font-size: 0.9rem;">
                                <div>編號</div>
                                <div>藝術品名稱</div>
                                <div>所屬展覽</div>
                                <div>展覽日期</div>
                                <div>創作者</div>
                                <div>操作</div>
                            </div>
                        </div>

                        <!-- 資料列 -->
                        <div>
                            <?php while($art = $artifacts->fetch_assoc()): ?>
                                <div style="border-bottom: 1px solid #eee; background: #fff;">
                                    <div style="padding: 0.75rem;">
                                        <div style="display: grid; grid-template-columns: 80px 200px 180px 100px 180px 140px; gap: 0.5rem; align-items: center; font-size: 0.9rem;">
                                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo htmlspecialchars($art['art_id']); ?></div>
                                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><strong><?php echo htmlspecialchars($art['art_name']); ?></strong></div>
                                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($art['e_name']); ?>"><?php echo htmlspecialchars($art['e_name']); ?></div>
                                            <div><?php echo date('Y-m-d', strtotime($art['e_Date'])); ?></div>
                                            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($art['creators'] ?? '未知'); ?>"><?php echo htmlspecialchars($art['creators'] ?? '未知'); ?></div>
                                            <div class="action-buttons">
                                                <button onclick="toggleEdit('<?php echo $art['art_id']; ?>')" class="btn btn-primary btn-small">修改</button>
                                                <form method="POST" style="display: inline; margin: 0;" onsubmit="return confirmDelete('<?php echo htmlspecialchars($art['art_name']); ?>');">
                                                    <input type="hidden" name="art_id" value="<?php echo $art['art_id']; ?>">
                                                    <button type="submit" name="delete_artifact" class="btn btn-danger btn-small">刪除</button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- 編輯表單 -->
                                        <div id="edit-<?php echo $art['art_id']; ?>" class="edit-form">
                                            <form method="POST">
                                                <input type="hidden" name="art_id" value="<?php echo $art['art_id']; ?>">
                                                <h4 style="color: #667eea; margin-bottom: 1rem;">修改藝術品資料</h4>
                                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                                                    <div class="form-group">
                                                        <label>藝術品編號</label>
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($art['art_id']); ?>" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>藝術品名稱 *</label>
                                                        <input type="text" name="art_name" class="form-control" value="<?php echo htmlspecialchars($art['art_name']); ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>所屬展覽 *</label>
                                                        <select name="e_name" class="form-control" required>
                                                            <?php
                                                            $exhibitions->data_seek(0);
                                                            while($ex = $exhibitions->fetch_assoc()):
                                                            ?>
                                                                <option value="<?php echo htmlspecialchars($ex['e_name']); ?>" <?php echo $art['e_name'] == $ex['e_name'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($ex['e_name']); ?>
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group" style="margin-top: 1rem;">
                                                    <label>創作者選擇 *</label>
                                                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                                                        <label style="cursor: pointer;">
                                                            <input type="radio" name="creator_type" value="existing" onclick="toggleCreatorTypeEdit('<?php echo $art['art_id']; ?>', 'existing')" checked> 選擇既有創作者
                                                        </label>
                                                        <label style="cursor: pointer;">
                                                            <input type="radio" name="creator_type" value="new" onclick="toggleCreatorTypeEdit('<?php echo $art['art_id']; ?>', 'new')"> 新增創作者
                                                        </label>
                                                    </div>

                                                    <!-- 既有創作者 -->
                                                    <div id="creator-existing-<?php echo $art['art_id']; ?>" class="creator-option active">
                                                        <div style="max-height: 150px; overflow-y: auto; border: 2px solid #e0e0e0; border-radius: 8px; padding: 1rem;">
                                                            <?php
                                                            $creators->data_seek(0);
                                                            while($creator = $creators->fetch_assoc()):
                                                            ?>
                                                                <label style="display: block; margin-bottom: 0.5rem; cursor: pointer;">
                                                                    <input type="checkbox" name="creator_ids[]" value="<?php echo htmlspecialchars($creator['id']); ?>">
                                                                    <?php echo htmlspecialchars($creator['name']); ?>
                                                                </label>
                                                            <?php endwhile; ?>
                                                        </div>
                                                    </div>

                                                    <!-- 新增創作者 -->
                                                    <div id="creator-new-<?php echo $art['art_id']; ?>" class="creator-option">
                                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 1rem; border: 2px solid #667eea; border-radius: 8px;">
                                                            <div class="form-group" style="margin: 0;">
                                                                <label>姓名 *</label>
                                                                <input type="text" name="new_creator_name" class="form-control">
                                                            </div>
                                                            <div class="form-group" style="margin: 0;">
                                                                <label>性別 *</label>
                                                                <select name="new_creator_gender" class="form-control">
                                                                    <option value="男">男</option>
                                                                    <option value="女">女</option>
                                                                    <option value="其他">其他</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group" style="margin: 0;">
                                                                <label>電話 *</label>
                                                                <input type="tel" name="new_creator_phone" class="form-control" pattern="09\d{8}" placeholder="0912345678">
                                                            </div>
                                                            <div class="form-group" style="margin: 0;">
                                                                <label>電子郵件 *</label>
                                                                <input type="email" name="new_creator_mail" class="form-control">
                                                            </div>
                                                            <div class="form-group" style="margin: 0;">
                                                                <label>出生日期 *</label>
                                                                <input type="date" name="new_creator_birth" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div style="margin-top: 1rem;">
                                                    <button type="submit" name="update_artifact" class="btn btn-success">確認修改</button>
                                                    <button type="button" onclick="toggleEdit('<?php echo $art['art_id']; ?>')" class="btn">取消</button>
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
                            查無符合條件的藝術品資料
                        <?php else: ?>
                            目前沒有藝術品資料
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
