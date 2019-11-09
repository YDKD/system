<?php
// 目标： 根据categories.php 传过来的id值 删除对应的数据

// 导入文件
require_once '../functions.php';

if(empty($_GET['id'])) {
    exit('');
}
//　强制类型转化的目的是为了把获取的id值转为数字类型，为了在下方where判断的时候能够不出错
$id = $_GET['id'];

xiu_execute('DELETE FROM categories WHERE id in (' . $id . '); ');

header('Location: /admin/categories.php');