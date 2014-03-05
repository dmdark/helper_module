module.controller('InformationSystemsController', function ($scope, $http, $location, $filter){
   $scope.editItem = null;
   $scope.urls = [];

   $scope.$watch('editItem.title', function (newTitle){
      $scope.editItem.url = $filter('translit')(newTitle);
   });

   $http.get('actions.php?module=information_systems&action=get&id=' + getCurrentId()).success(function (data){
      $scope.urls = data;
   });

   $scope.AddUrl = function (){
      $scope.urls.unshift({
         'url': '/unknown_url_' + ($scope.urls.length + 1) + '/',
         'items': []
      });
   };

   $scope.EditUrl = function (url){
      url.editable = true;
   };

   $scope.StopEdit = function (url){
      url.editable = false;
   };

   $scope.DeleteUrl = function (url){
      for(var i = 0; i < $scope.urls.length; i++){
         if($scope.urls[i] == url){
            $scope.urls.splice(i, 1);
            $scope.editItem = null;
            break;
         }
      }
   };

   $scope.AddItemToUrl = function (url){
      url.items.unshift({
         title: 'Новый элемент'
      });
   };

   $scope.EditItem = function (item){
      $scope.editItem = item;
   };


   $scope.DeleteItem = function (url, item){
      for(var i = 0; i < url.items.length; i++){
         if(url.items[i] == item){
            url.items.splice(i, 1);
            $scope.editItem = null;
            break;
         }
      }
   };

   $scope.SaveAll = function (){
      $http.post('actions.php?module=information_systems&action=saveAll&id=' + getCurrentId(), angular.toJson($scope.urls)).success(function (){

      });
   };

   function getCurrentId(){
      return (window.location.search).split('=')[1];
   }
});

