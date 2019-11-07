<?php

require_once 'config.php';

/**
 * 封装大家公用的函数
 */

session_start();
/**
 * 定义函数时，一定要注意：函数名和内置函数冲突的问题
 * JS 判断方式 type fn == 'function'
 * php 判断方式 function_exists('get_current_user') 
 */

/**
 * 获取当前用户登录信息，如果没有获取到则自动跳转到登录页面
 * @return [type] [description]
 */
function xiu_get_current_user()
{
    if (empty($_SESSION['current_login_user'])) {
        // 没有当前用户登录的信息
        header('Location: /admin/login.php');
        exit(); // 没有哦必要再执行之后的代码
    }
    return $_SESSION['current_login_user'];
}


/**
 * 封装一个查询函数
 */
function xiu_query($sql)
{
    $conn = mysqli_connect(XIU_DB_HOST, XIU_DB_USER, XIU_DB_PASS, XIU_DB_NAME);
    if (!$conn) {
        exit('连接数据库失败');
    }
    $query = mysqli_query($conn, $sql);
    if (!$query) {
        // 查询失败
        return false;
    }
    return $query;
}


/**
 * 通过一个数据库查询获取数据，获取多条数据
  => 索引数组套关联数组
 */
function xiu_fetch_all($sql)
{
    $query = xiu_query($sql);

    while ($row = mysqli_fetch_assoc($query)) {
        $result[] = $row;
    }


    mysqli_free_result($query);
    // mysqli_close($conn);
    return $result;
}

/**
 * 获取单条数据
  => 关联数组
 */
function xiu_fetch_one($sql)
{
    $res = xiu_fetch_all($sql);
    return isset($res[0]) ? $res[0] : null;
}
/**
 * 执行一个增删改的语句
 */
function xiu_execute($sql)
{
    $conn = mysqli_connect(XIU_DB_HOST, XIU_DB_USER, XIU_DB_PASS, XIU_DB_NAME);
    if (!$conn) {
        exit('连接数据库失败');
    }

    $query = mysqli_query($conn, $sql);
    if (!$query) {
        // 查询失败
        return false;
    }

    // 取到受影响的行数 此处的受影响的应该是这个连接的桥梁所以传入$conn
    // 对于增删修改类的都是获取受影响的行数
    $affected_rows = mysqli_affected_rows($conn);

    mysqli_free_result($query);
    mysqli_close($conn);
    return $affected_rows;
}
