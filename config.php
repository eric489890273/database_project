<?php
// 資料庫連線配置
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "database_project";

// 建立連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 設定字元編碼
$conn->set_charset("utf8mb4");

// 開啟 Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 輔助函數：檢查是否登入
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// 輔助函數：取得當前使用者資訊
function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) {
        return null;
    }

    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM person WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// 輔助函數：檢查是否為訪客
function isVisitor() {
    global $conn;
    if (!isLoggedIn()) {
        return false;
    }

    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM visitor WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// 輔助函數：檢查是否為策展人
function isCurator() {
    global $conn;
    if (!isLoggedIn()) {
        return false;
    }

    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM curator WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// 輔助函數：生成隨機ID
function generateID($prefix, $table, $column) {
    global $conn;
    do {
        $id = $prefix . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $sql = "SELECT * FROM $table WHERE $column = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);

    return $id;
}
?>
