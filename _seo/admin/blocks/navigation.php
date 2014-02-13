<div class="navbar navbar-static-top navbar-inverse">
   <div class="container">

      <a class="navbar-brand" href="/_seo/admin/">Matik SEO admin</a>

      <ul class="nav navbar-nav">
         <li <?php if($_SERVER['REQUEST_URI'] == '/_seo/admin/' || $_SERVER['REQUEST_URI'] == '/_seo/admin/index.php') echo 'class="active"'; ?>>
            <a href="/_seo/admin/index.php">Редактирование</a>
         </li>
         <li <?php if($_SERVER['REQUEST_URI'] == '/_seo/admin/redirects.php') echo 'class="active"'; ?>><a href="/_seo/admin/redirects.php">Редиректы 301</a>
         </li>
         <li <?php if($_SERVER['REQUEST_URI'] == '/_seo/admin/error404.php') echo 'class="active"'; ?>><a href="/_seo/admin/error404.php">Ошибка 404</a></li>
         <li><a href="/" target="_blank">Перейти на сайт!</a></li>
         <li <?php if($_SERVER['REQUEST_URI'] == '/_seo/admin/test.php') echo 'class="active"'; ?>><a href="/_seo/admin/test.php">Потестировать</a></li>

         <?php if(!empty($config['adminConfig']['information_systems'])): ?>
         <li class="dropdown">
            <a class="dropdown-toggle"
               data-toggle="dropdown"
               href="#">
               Инфо системы
               <b class="caret"></b>
            </a>
            <ul class="dropdown-menu">
               <?php foreach($config['adminConfig']['information_systems'] as $information_system): ?>
                  <li>
                     <a href="/_seo/admin/information_systems.php?id=<?php echo $information_system['id']; ?>">
                        <?php echo $information_system['title']; ?>
                     </a>
                  </li>
               <?php endforeach; ?>
            </ul>
         </li>
         <?php endif; ?>
      </ul>
   </div>
</div>