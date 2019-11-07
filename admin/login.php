<?php

//载入配置文件
require_once '../config.php';
// 给客户找一个箱子 （如何之前有就用之前的，如果没有就重新创建一个）
session_start();

function login()
{
  //1.接收并校验数据
  //2.持久化
  //3.响应
  global $message;
  if (empty($_POST['email'])) {
    $message = '请填写邮箱';
    return;
  }
  if (empty($_POST['password'])) {
    $message = '请填写密码';
    return;
  }
  // 邮箱和密码都存在，然后定义一个变量来接收，方便后续操作
  $email = $_POST['email'];
  $password = $_POST['password'];


  //当客户端提交过来完整的表单信息就应该进行数据校验

  // 数据库连接校验
  $conn = mysqli_connect(XIU_DB_HOST, XIU_DB_USER, XIU_DB_PASS, XIU_DB_NAME);

  if (!$conn) {
    exit('<h1>连接数据库失败</h1>');
  }

  $query = mysqli_query($conn, "SELECT * FROM `users` WHERE email = '{$email}' limit 1;");

  if (!$query) {
    $message = '登录失败，请重试';
    return;
  }

  // 获取登录用户关联数组
  $user = mysqli_fetch_assoc($query);

  if (!$user) {
    // 用户名不存在
    $message = '邮箱和密码不匹配';
    return;
  }

  if ($user['password'] != $password) {
    // 密码错误
    $message = '邮箱和密码不匹配';
    return;
  }
  // 存一个登录标识
  // $_SESSION['is_logged_in'] = true;
  $_SESSION['current_login_user'] = $user;

  //一切都ok了 进行跳转
  header('Location: /admin/');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  login();
}

?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="utf-8">
  <title>Sign in &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/animate/animate.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
</head>

<body>
  <div class="login">
    <!-- 可以在form表单添加 novalidate 属性去取消浏览器自动校验的功能 -->
    <!-- autocomplete=off 是关闭客户端的自动完成功能 -->
    <form class="login-wrap<?php echo isset($message) ? ' shake animated' : '' ?>" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" novalidate autocomplete="off">
      <img class="avatar" src="/static/assets/img/default.png">
      <!-- 有错误信息时展示 -->
      <?php if (isset($message)) : ?>
        <div class="alert alert-danger">
          <strong> 错误！ <?php echo $message; ?>
        </div>
      <?php endif ?>
      <div class="form-group">
        <label for="email" class="sr-only">邮箱</label>
        <input id="email" name="email" type="email" class="form-control" placeholder="邮箱" autofocus value="<?php echo empty($_POST['email']) ? '' : $_POST['email'] ?>">
      </div>
      <div class="form-group">
        <label for="password" class="sr-only">密码</label>
        <input id="password" name="password" type="password" class="form-control" placeholder="密码">
      </div>
      <button class="btn btn-primary btn-block" href="index.html">登 录</button>
    </form>
  </div>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script>
    $(function($) {
      // 1. 单独作用域
      // 2. 确保页面加载之后执行

      // 目标：在用户输入完自己的邮箱之后，页面上方展示这个邮箱对应的头像
      // 实现：
      // - 时机：邮箱文本框失去焦点,并且能够拿到文本框中填写的邮箱地址
      // - 事情：获取到这个文本框填写的邮箱对应的头像地址，展示到 img 元素上

      var emailFormat = /[0-9a-zA-Z]+@[0-9a-zA-Z]+\.[0-9a-zA-Z]+$/;

      $('#email').on('blur', function() {
        $value = $(this).val();
        // 忽略文本框为空或者不是一个邮箱地址
        if (!$value || !emailFormat.test($value)) return;

        // 用户输入了一个正确的邮箱地址
        // 获取这个邮箱地址对应的头像地址，并且把它加到Img元素上
        // 因为客户端的 JS 无法直接操作数据库，应该通过 JS 发送AJAX请求 告诉服务端的某个接口，通过这个接口来帮助客户端获取头像地址

        $.get('/admin/api/avatar.php', {
          email: $value
        }, function(res) {

          //希望 res 对应的是这个邮箱对应的头像地址
          if (!res) return;
          // 展示到 img 元素上
          $('.avatar').fadeOut(function() {
            $(this).on('load', function() {
              //图片完全加载成功之后 注意“加载是指图片在后台已经拿到了，处理好了，不是马上就显示出来”
              $(this).fadeIn()
            }).attr('src', res)
          })
        })
      })
    })
  </script>
</body>

</html>