function SeoController($scope, $http, $sce){
   $http.get('actions.php?action=get_items').success(function (data){
      $scope.items = data;
      angular.forEach($scope.items, function (item){
         item.rememberCache = $sce.trustAsHtml(item.rememberCache);
      });
   });

   $scope.SaveAll = function (){
      $http.post('actions.php', $scope.items).success(function (){
         $scope.alertMessage = "Сохранение успешно";

         setTimeout(function (){
            $scope.alertMessage = "";
            $scope.$apply();
         }, 2000);
      });
   };

   $scope.Remove = function (removeItem){
      $scope.items.splice($scope.items.indexOf(removeItem), 1);
   };

   $scope.Add = function (){
      $scope.items.unshift({
         url: $scope.add_url
      });
      $scope.add_url = '';
   };

}

function RedirectsController($scope, $http){
   $scope.redirectsAdd = '';
   $scope.items = {};
   updateItems();

   $scope.Add = function (){
      $http.post('actions.php?module=redirects&action=add', $scope.redirectsAdd).success(function (){
         updateItems();
      });
   };

   $scope.Delete = function (source, dest){
      $http.post('actions.php?module=redirects&action=delete', {source: source, dest: dest}).success(function (){
         updateItems();
      });
   };

   function updateItems(){
      $http.get('actions.php?module=redirects&action=get').success(function (data){
         $scope.items = data;
      });
   }


}