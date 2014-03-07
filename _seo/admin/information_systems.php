<?php
require_once 'defines.php';
require_once _BLOCKS_DIRECTORY . 'header.php';
?>
<body>
<div ng-controller="InformationSystemsController">
   <?php if(!isset($_SESSION['_seo_auth'])): ?>
      <?php require_once _BLOCKS_DIRECTORY . 'auth.php'; ?>
   <?php else: ?>
      <?php require_once _BLOCKS_DIRECTORY . 'navigation.php'; ?>
      <div class="container">
         <div class="col-sm-6">
            <button class="btn btn-success" ng-click="AddUrl()" ng-disabled="ajaxInAction">Новый URL</button>
            <button class="btn btn-primary" ng-click="SaveAll()">Сохранить всё</button>

            <table class="table table-condensed table-information-items" style="margin-top: 30px;">
               <tbody ng-repeat="url in urls">
               <tr>
                  <td>
                     <div ng-show="!url.editable">
                        <a ng-href="http://<?php echo $_SERVER['HTTP_HOST']; ?>{{ url.url }}">{{ url.url }}</a>
                        <span class="glyphicon glyphicon-edit glyph" ng-click="EditUrl(url)"></span>
                     </div>

                     <div ng-show="url.editable">
                        <input type="text" ng-model="url.url" size="20"/>
                        <button class="btn btn-sm btn-success btn-xs" ng-click="StopEdit(url)">ok</button>
                     </div>
                  </td>
                  <td style="text-align: right">
                     <button class="btn btn-sm btn-info" ng-click="AddItemToUrl(url)">добавить</button>
                     <button class="btn btn-sm btn-danger" ng-click="DeleteUrl(url)">удалить</button>
                  </td>
               </tr>
               <tr ng-show="url.items.length > 0">
                  <td colspan="2">
                     <table class="table table-condensed table-stripped table-hover">
                        <tr ng-repeat="item in url.items" ng-click="EditItem(item)" ng-class="{'success': item == editItem}">
                           <td><a ng-href="{{ url.url }}{{ item.url }}" target="_blank">{{ item.title }}</a></td>
                           <td style="text-align: right">
                              <button class="btn btn-sm btn-danger btn-xs" ng-click="DeleteItem(url, item)">-</button>
                           </td>
                        </tr>
                     </table>
                  </td>
               </tr>
               </tbody>
            </table>
         </div>
         <div class="col-sm-6 col-sm-push-6" style="position: fixed; top: 100px;">
            <div class="well well-sm" ng-show="editItem">
               <div class="form-group form-inline">
                  <label>URL: </label>
                  {{ editItem.url }}
               </div>
               <?php
               $currentSystem = array();
               foreach($config['adminConfig']['information_systems'] as $information_system){
                  if($information_system['id'] == $_GET['id']){
                     $currentSystem = $information_system;
                     break;
                  }
               }
               foreach($currentSystem['fields'] as $field):?>
                  <div class="form-group">
                     <label for="<?php echo $field['id']; ?>">
                        <?php echo $field['title']; ?>
                     </label>
                     <?php if($field['type'] == 'textarea'): ?>
                        <textarea ng-model="editItem.<?php echo $field['id']; ?>" cols="30" rows="5" class="form-control"></textarea>
                     <?php else: ?>
                        <input type="text" ng-model="editItem.<?php echo $field['id']; ?>" class="form-control"/>
                     <?php endif; ?>
                  </div>
               <?php endforeach; ?>
               <div class="form-group"></div>
            </div>
         </div>
      </div>
   <?php endif; ?>
</div>
</body>
</html>
