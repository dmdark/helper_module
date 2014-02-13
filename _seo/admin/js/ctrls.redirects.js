function RedirectsController($scope, $http){
   $scope.redirectsAdd = '';
   $scope.items = {};
   updateItems();

   $scope.Add = function (){
      $http.post('actions.php?module=redirects&action=add', $scope.redirectsAdd).success(function (){
         $scope.redirectsAdd = '';
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