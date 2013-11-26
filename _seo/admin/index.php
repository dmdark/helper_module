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
            <li class="active"><a href="/_seo/admin/">Редактирование</a></li>
            <li><a href="/" target="_blank">Перейти на сайт!</a></li>
            <li><a href="/_seo/admin/test.php">Потестировать</a></li>
         </ul>

      </div>
   </div>

   <ul class="nav nav-list nav-side">
      <li ng-repeat="item in items">
         <a href="#{{ item.url }}">{{ item.url }}</a>
      </li>
   </ul>


   <div class="container">
      <div class="row">
         <form class="form-inline">
            <div class="col-lg-8">
               <input type="text" class="form-control" placeholder="Новая страница" ng-model="add_url">
            </div>

            <div class="col-lg-1">
               <button type="submit" class="btn btn-default" ng-click="Add()">Добавить</button>
            </div>
         </form>
      </div>

      <div class="save_button">
         <div class="alert" ng-hide="!alertMessage">{{ alertMessage }}</div>
         <button class="btn btn-large btn-primary" ng-click="SaveAll()">Сохранить изменения</button>
      </div>


      <div class="row row-item row-first-{{ $first }}" ng-class="" ng-repeat="item in items">
         <div class="panel">
            <div class="panel-heading">
               {{ $index + 1 }}. <a target="_blank" name="{{ item.url }}" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>{{ item.url }}">
                  http://<?php echo $_SERVER['HTTP_HOST']; ?><span>{{ item.url }}</span>
               </a>
               <button class="btn btn-danger pull-right" ng-click="Remove(item)">удалить</button>
            </div>
            <div class="input-group">
               <span class="input-group-addon">http://<?php echo $_SERVER['HTTP_HOST']; ?></span>
               <input type="text" class="form-control" placeholder="Текущий URL" ng-model="item.url">
            </div>
            <div class="input-group" style="margin-top: 7px;">
               <span class="input-group-addon">http://<?php echo $_SERVER['HTTP_HOST']; ?></span>
               <input type="text" class="form-control" placeholder="Новый URL" ng-model="item.newUrl">
            </div>
            <div class="input-group" style="margin-top: 7px;">
               <span class="input-group-addon">Title</span>
               <input type="text" class="form-control" placeholder="Заголовок страницы" ng-model="item.title">
            </div>
            <div class="input-group" style="margin-top: 7px;">
               <span class="input-group-addon">Description</span>
               <input type="text" class="form-control" placeholder="Описание страницы" ng-model="item.description">
            </div>
            <div class="input-group" style="margin-top: 7px;">
               <span class="input-group-addon">Keywords</span>
               <input type="text" class="form-control" placeholder="Ключевые слова" ng-model="item.keywords">
            </div>
            <?php
            $additionalTags = @$config['adminConfig']['additionalTags'];
            if(!empty($additionalTags)) foreach($additionalTags as $additionalTag): ?>
               <div class="input-group" style="margin-top: 7px;">
                  <span class="input-group-addon"><?php echo $additionalTag; ?></span>
                  <input type="text" class="form-control" placeholder="<?php echo $additionalTag; ?>" ng-model="item.<?php echo $additionalTag; ?>">
               </div>
            <?php endforeach; ?>

            <div style="text-align: right;" ng-show="item.rememberCache">
               <a href="" ng-click="item.showCache = !item.showCache">show remember cache</a>
            </div>
            <code ng-show="item.showCache" ng-bind-html="item.rememberCache">
            </code>
         </div>
      </div>

   </div>
<?php endif; ?>
</body>
</html>
