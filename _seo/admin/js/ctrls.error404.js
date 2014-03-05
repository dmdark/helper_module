module.controller('Error404Controller', function ($scope, $http){
   $scope.urls = '';

   updateItems();

   $scope.Save = function (){
      $http.post('actions.php?module=error404&action=save', $scope.urls).success(function (){
         updateItems();
      });
   };

   function updateItems(){
      $http.get('actions.php?module=error404&action=get').success(function (data){
         $scope.urls = data;
      });
   }
});