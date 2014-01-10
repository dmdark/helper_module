<div class="navbar navbar-static-top navbar-inverse">
   <div class="container">

      <a class="navbar-brand" href="/_seo/admin/">Matik SEO admin</a>

      <ul class="nav navbar-nav">
         <li <?php if($_SERVER['REQUEST_URI'] == '/_seo/admin/' || $_SERVER['REQUEST_URI'] == '/_seo/admin/index.php') echo 'class="active"'; ?>>
            <a href="/_seo/admin/index.php">Редактирование</a>
         </li>
         <li <?php if($_SERVER['REQUEST_URI'] == '/_seo/admin/redirects.php') echo 'class="active"'; ?>><a href="/_seo/admin/redirects.php">Редиректы</a></li>
         <li><a href="/" target="_blank">Перейти на сайт!</a></li>
         <li <?php if($_SERVER['REQUEST_URI'] == '/_seo/admin/test.php') echo 'class="active"'; ?>><a href="/_seo/admin/test.php">Потестировать</a></li>
      </ul>
   </div>
</div>