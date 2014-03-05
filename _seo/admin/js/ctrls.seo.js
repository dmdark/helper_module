module.controller('SeoController', function ($scope, $http, $sce){
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

});