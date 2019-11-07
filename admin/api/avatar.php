<?php

/**
 * 根据用户的邮箱来输出用户的头像
 * email => 头像地址
 */

// 导入配置文件
require_once  '../../config.php';
// 1.接收传过来的邮箱地址
if (empty($_GET['email'])) {
    exit('缺少必要的参数');
}
$email = $_GET['email'];
// 2.查询对应的头像地址
$conn = mysqli_connect(XIU_DB_HOST, XIU_DB_USER, XIU_DB_PASS, XIU_DB_NAME);
if (!$conn) {
    exit('连接数据库失败');
}
$res = mysqli_query($conn, "SELECT avatar FROM users WHERE email = '{$email}' limit 1;");
if (!$res) {
    exit('查询失败');
}
// 关联数组
$row = mysqli_fetch_assoc($res);
// 3.把这个头像地址进行输出
echo $row['avatar'];