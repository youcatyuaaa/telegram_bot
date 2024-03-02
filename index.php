<?php

// Telegram 机器人 API 令牌
define('BOT_TOKEN', 'YOUR_BOT_TOKEN');

// 数据库连接配置
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'username');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'database_name');

// 连接到 MySQL 数据库
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// Telegram API 请求函数
function apiRequest($method, $parameters) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/" . $method;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

// 处理传入的消息
$update = json_decode(file_get_contents('php://input'), true);
if (isset($update["message"])) {
    $message = $update["message"];
    $chat_id = $message["chat"]["id"];
    $text = $message["text"];

    // 处理命令
    switch ($text) {
        case '/start':
            apiRequest("sendMessage", array('chat_id' => $chat_id, 'text' => '欢迎使用社工机器人，请使用命令查询相关信息。'));
            break;

        case '/phone':
            // 处理手机号查询
            apiRequest("sendMessage", array('chat_id' => $chat_id, 'text' => '请输入要查询的手机号：'));
            break;

        case '/qq':
            // 处理 QQ 号查询
            apiRequest("sendMessage", array('chat_id' => $chat_id, 'text' => '请输入要查询的QQ号：'));
            break;

        case '/xingming':
            // 处理姓名查询
            apiRequest("sendMessage", array('chat_id' => $chat_id, 'text' => '请输入要查询的姓名：'));
            break;

        case '/email':
            // 处理邮箱查询
            apiRequest("sendMessage", array('chat_id' => $chat_id, 'text' => '请输入要查询的邮箱：'));
            break;

        default:
            // 查询手机号、QQ号、姓名、邮箱
            $sql = "SELECT * FROM user_info WHERE phone_number = '$text' OR qq = '$text' OR name = '$text' OR email = '$text'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $response_text = "查询结果：\n";
                while ($row = $result->fetch_assoc()) {
                    $response_text .= "姓名：" . $row["name"] . "\n";
                    $response_text .= "手机号：" . $row["phone_number"] . "\n";
                    $response_text .= "QQ：" . $row["qq"] . "\n";
                    $response_text .= "邮箱：" . $row["email"] . "\n";
                    // 可以根据需要添加更多字段
                }
                apiRequest("sendMessage", array('chat_id' => $chat_id, 'text' => $response_text));
            } else {
                apiRequest("sendMessage", array('chat_id' => $chat_id, 'text' => '抱歉，没有找到相关信息。'));
            }
    }
} elseif (isset($update["callback_query"])) {
    // 处理回调查询（如果有）
}

// 关闭数据库连接
$conn->close();
