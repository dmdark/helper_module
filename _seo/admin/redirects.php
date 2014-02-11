<?php
require_once 'defines.php';
require_once _BLOCKS_DIRECTORY . 'header.php';
?>
<body ng-controller="RedirectsController">

<?php if(!isset($_SESSION['_seo_auth'])): ?>
   <?php require_once _BLOCKS_DIRECTORY . 'auth.php'; ?>
<?php else: ?>
   <?php require_once _BLOCKS_DIRECTORY . 'navigation.php'; ?>
   <div class="container">
      <div style="margin-bottom: 10px;">Добавлять в формате: <br/>
         <code>
            url url_куда_ведет<br/>
            url url_куда_ведет
         </code>
      </div>
      <textarea class="form-control" rows="10" ng-model="redirectsAdd" wrap="off"></textarea>
      <button class="btn btn-success btn-large" ng-click="Add()">Добавить</button>

      <div class="row" style="margin-top: 20px;">
         <table class="table table-hover table-striped table-condensed">
            <tr ng-repeat="item in items">
               <td style="text-align: right; padding-right: 15px;">
                  <a target="_blank" ng-href="http://<?php echo $_SERVER['HTTP_HOST']; ?>{{item.source}}">{{ item.source }}</a>
               </td>
               <td>
                  <a target="_blank" ng-href="http://<?php echo $_SERVER['HTTP_HOST']; ?>{{item.dest}}">{{ item.dest }}</a>
               </td>
               <td style="text-align: right">
                  <button class="btn btn-small btn-danger" ng-click="Delete(item.source, item.dest)">del</button>
               </td>
            </tr>
         </table>
      </div>
   </div>
<?php endif; ?>
</body>
</html>
