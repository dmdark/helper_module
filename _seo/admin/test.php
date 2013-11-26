<?php require_once 'header.php'; ?>

<body ng-controller="SeoController">

<?php if(!isset($_SESSION['_seo_auth'])): ?>
   <!-- Авторизация -->
   <div class="auth_container">
      <form class="form-inline" action="" method="post">
         <input type="text" class="form-control" placeholder="Login" name="login"/>
         <input type="password" class="form-control" placeholder="Password" name="password"/>
         <br/>
         <button type="submit" class="btn btn-default">Войти</button>
      </form>
   </div>
<?php else: ?>

   <div class="navbar navbar-static-top navbar-inverse">
      <div class="container">

         <a class="navbar-brand" href="/_seo/admin/">Matik SEO admin</a>

         <ul class="nav navbar-nav">
            <li><a href="/_seo/admin/">Редактирование</a></li>
            <li><a href="/" target="_blank">Перейти на сайт!</a></li>
            <li class="active"><a href="/_seo/admin/test.php">Потестировать</a></li>
         </ul>

      </div>
   </div>


   <div class="container">
      <?php require_once(dirname(__FILE__) . '/tests/tests.php'); ?>
      <label class="label <?php echo bool_to_label_class(test_mb_functions()); ?>">Модуль mbstring</label>
      <label class="label <?php echo bool_to_label_class(test_php2js()); ?>">php2js</label>
      <label class="label <?php echo bool_to_label_class(test_iconv()); ?>">iconv</label>
      <label class="label <?php echo bool_to_label_class(test_preg_match()); ?>">preg_match</label>

   </div>
<?php endif; ?>
</body>
</html>
