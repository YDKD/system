<?php
/**
 * 关于编辑功能，
 * 1. 首先在点击编辑按钮的时候，会根据之前查询函数，给定每一个input一个ID值，如果点击编辑会把这个ID的值通过URL传到category.php页面
 * 2. 在确定URL有传递过来的ID值之后，再通过查询函数把当前的数据传过来
 */
require_once '../functions.php';

xiu_get_current_user();

// 判断是否为需要编辑的数据
if (!empty($_GET['id'])) {
  $current_edit_categories = xiu_fetch_one('SELECT FORM categories where id =' . $_GET['id']);
}

function add_category()
{
  if (empty($_POST['slug']) || empty($_POST['name'])) {
    $GLOBALS['success'] = false;
    $GLOBALS['error'] = '请完整填写信息';
    return;
  }

  // 接收表单传来的数据
  $name = $_POST['name'];
  $slug = $_POST['slug'];

  // 将数据传入到数据库中 涉及到数据库的保存操作 insert into categories values()
  $rows = xiu_execute("INSERT INTO categories VALUES (NULL, '{$slug}', '{$name}');");
  $GLOBALS['success'] = $rows > 0;
  $GLOBALS['error'] = $rows <= 0 ? '添加失败' : '添加成功';
}

function edit_category () {
  global $current_edit_category;

  // // 只有当时编辑并点保存
  // if (empty($_POST['name']) || empty($_POST['slug'])) {
  //   $GLOBALS['message'] = '请完整填写表单！';
  //   $GLOBALS['success'] = false;
  //   return;
  // }
  // 接收并保存
  $id = $current_edit_category['id'];
  $name = empty($_POST['name']) ? $current_edit_category['name'] : $_POST['name'];
  // 数据同步，如果不加这行代码，那么点击修改之后，因为现在的POST提交里面不为空，所以跳转之后，还是显示的是上次的内容
  $current_edit_category['name'] = $name;
  $slug = empty($_POST['slug']) ? $current_edit_category['slug'] : $_POST['slug'];
  $current_edit_category['slug'] = $slug;

  // insert into categories values (null, 'slug', 'name');
  $rows = xiu_execute("UPDATE categories SET slug='{$slug}', NAME='{$name}' WHERE id = {$id}");

  $GLOBALS['success'] = $rows > 0;
  $GLOBALS['message'] = $rows <= 0 ? '更新失败！' : '更新成功！';
}

// 如果修改和查询放在一起的话，一定是先做修改 再查询
// 通过判断 URL 是否传入 ID值来判断主线任务
if (empty($_GET['id'])) {
  // 添加
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    add_category();
  }
} else {
  // 编辑
  // 客户端通过 URL 传递了一个 ID
  // => 客户端是要来拿一个修改数据的表单
  // => 需要拿到用户想要修改的数据
  $current_edit_category = xiu_fetch_one('select * from categories where id = ' . $_GET['id']);
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    edit_category();
  }
}

// 查询到categories中的数据
$categories = xiu_fetch_all('SELECT *FROM categories;');
?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <meta charset="utf-8">
  <title>Categories &laquo; Admin</title>
  <link rel="stylesheet" href="/static/assets/vendors/bootstrap/css/bootstrap.css">
  <link rel="stylesheet" href="/static/assets/vendors/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="/static/assets/vendors/nprogress/nprogress.css">
  <link rel="stylesheet" href="/static/assets/css/admin.css">
  <script src="/static/assets/vendors/nprogress/nprogress.js"></script>
</head>

<body>
  <script>
    NProgress.start()
  </script>

  <div class="main">
    <?php include 'inc/navbar.php'; ?>
    <div class="container-fluid">
      <div class="page-title">
        <h1>分类目录</h1>
      </div>
      <!-- 有错误信息时展示 -->
      <?php if (isset($error)) : ?>
        <?php if ($success) : ?>
          <div class="alert alert-success">
            <strong>成功！</strong><?php echo $error; ?>
          </div>
        <?php else : ?>
          <div class="alert alert-danger">
            <strong>错误！</strong><?php echo $error; ?>
          </div>
        <?php endif ?>
      <?php endif ?>

      <div class="row">
        <div class="col-md-4">
          <?php if (isset($current_edit_category)) : ?>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $current_edit_category['id']; ?>" method="post">
              <h2>编辑《<?php echo $current_edit_category['name']; ?>》</h2>
              <div class="form-group">
                <label for="name">名称</label>
                <input id="name" class="form-control" name="name" type="text" placeholder="分类名称" value="<?php echo $current_edit_category['name']; ?>">
              </div>
              <div class="form-group">
                <label for="slug">别名</label>
                <input id="slug" class="form-control" name="slug" type="text" placeholder="slug" value="<?php echo $current_edit_category['slug']; ?>">
                <p class="help-block">https://zce.me/category/<strong>slug</strong></p>
              </div>
              <div class="form-group">
                <button class="btn btn-primary" type="submit">保存</button>
              </div>
            </form>
          <?php else : ?>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
              <h2>添加新分类目录</h2>
              <div class="form-group">
                <label for="name">名称</label>
                <input id="name" class="form-control" name="name" type="text" placeholder="分类名称">
              </div>
              <div class="form-group">
                <label for="slug">别名</label>
                <input id="slug" class="form-control" name="slug" type="text" placeholder="slug">
                <p class="help-block">https://zce.me/category/<strong>slug</strong></p>
              </div>
              <div class="form-group">
                <button class="btn btn-primary" type="submit">添加</button>
              </div>
            </form>
          <?php endif ?>
        </div>
        <div class="col-md-8">
          <div class="page-action">
            <!-- show when multiple checked -->
            <a id="btn_delete" class="btn btn-danger btn-sm" href="/admin/category-delete.php" style="display: none">批量删除</a>
          </div>
          <table class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th class="text-center" width="40"><input type="checkbox"></th>
                <th>名称</th>
                <th>Slug</th>
                <th class="text-center" width="100">操作</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $item) : ?>
                <tr>
                  <td class="text-center"><input type="checkbox" data-id="<?php echo $item['id']; ?>"></td>
                  <td><?php echo $item['name']; ?></td>
                  <td><?php echo $item['slug']; ?></td>
                  <td class="text-center">
                    <a href="/admin/categories.php?id=<?php echo $item['id']; ?>" class="btn btn-info btn-xs">编辑</a>
                    <a href="/admin/category-delete.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-xs">删除</a>
                  </td>
                </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <?php $current_page = 'categories' ?>
  <?php include 'inc/sildebar.php'; ?>

  <script src="/static/assets/vendors/jquery/jquery.js"></script>
  <script src="/static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>
    // 不要使用重复无意义的操作，应该用变量去本地化
    $(function($) {
      // 在表格的任意一个 checkbox 选中状态变化时
      var $tobyCheckbox = $('tbody input');
      var $btn_delete = $('#btn_delete');

      // 把被选中的选项框的id记下来，然后再后面的批量删除中可以用到，
      // tips
      // 1.还要特别注意变量的本地化，一些结果可以通过定义一个变量来接收（变量的重复使用时用到）
      var allCheckeds = [];
      $tobyCheckbox.on('change', function() {
        var id = $(this).data('id');
        if ($(this).prop('checked')) {
          allCheckeds.includes(id) === -1 || allCheckeds.push(id)
        } else {
          allCheckeds.splice(allCheckeds.indexOf(id), 1);
        }

        // 可以通过数组的长度是否为空来判断批量按钮的显示和隐藏
        allCheckeds.length ? $btn_delete.fadeIn() : $btn_delete.fadeOut();
        $btn_delete.prop('search', '?id=' + allCheckeds)
      })

      $('thead input').on('change', function(){
        var cke = $(this).prop('checked')
        $tobyCheckbox.prop('checked', cke).change()
        // console.log(111)
      })

      //version-1
      // $tobybox.on('change', function(){
      //   // 有任意一个 checkbox选中就显示，反之隐藏
      //   // 定义一个标志变量
      //   var flag = false;
      //   $tobybox.each(function(i, item){
      //     // attr 和 prop的区别
      //     // attr 访问到的是元素的属性
      //     // prop 访问到的是元素对应的DOM对象的属性
      //     // console.log($item.prop('checked'));
      //     if ($(item).prop('checked')) {
      //       flag = true;
      //     }
      //   })
      //   //根据标志变量flag 来觉得批量悬着按钮是显示还是隐藏
      //   flag ? $btn_delete.fadeIn() : $btn_delete.fadeOut();
      // })
    })
  </script>
  <script>
    NProgress.done()
  </script>
</body>

</html>