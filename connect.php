<?php
// --- 資料庫連接的四個重要參數 ---

// 1. 資料庫伺服器的主機名稱或 IP 位址
//    對於 XAMPP 本地環境，通常就是 "localhost"
$servername = "localhost";

// 2. 資料庫的使用者名稱
//    XAMPP 的預設使用者名稱是 "root"
$username = "root";

// 3. 資料庫的密碼
//    XAMPP 的預設密碼是空的
$password = "";

// 4. 要連接的資料庫名稱
//    也就是我們在 phpMyAdmin 建立的那個
$dbname = "database_project";

// --- 建立連接 ---

// 使用以上參數，建立一個新的 mysqli 物件 (也就是連接物件)
$conn = new mysqli($servername, $username, $password, $dbname);

// --- 檢查連接是否成功 ---

// 檢查連接物件中是否有 connect_error 這個屬性
// 如果有，代表連接出錯
if ($conn->connect_error) {
    // 如果連接失敗，停止執行程式，並顯示錯誤訊息
    die("Connection failed: " . $conn->connect_error);
}

// 如果程式能執行到這裡，代表連接成功
echo "Connected successfully!";
