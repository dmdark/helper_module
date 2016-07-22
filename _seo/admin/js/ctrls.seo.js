module.controller('SeoController', function ($scope, $http, $sce) {

	function showAlert(message) {
		$scope.alertMessage = message;
		setTimeout(function () {
			$scope.alertMessage = "";
			$scope.$apply();
		}, 2000);
	}

	$http.get('actions.php?action=get_items').success(function (data) {
		$scope.items = data;
		angular.forEach($scope.items, function (item) {
			item.rememberCache = $sce.trustAsHtml(item.rememberCache);
		});
	});

	$scope.SaveAll = function () {
		$http.post('actions.php', $scope.items).success(function () {
			showAlert("Сохранение успешно");
		});
	};

	$scope.Remove = function (removeItem) {
		$scope.items.splice($scope.items.indexOf(removeItem), 1);
	};

	$scope.Add = function () {
		var urlAlreadyExist = false;
		angular.forEach($scope.items, function (item, index) {
			if (!urlAlreadyExist && item.url === $scope.add_url) {
				urlAlreadyExist = true;
				window.location.hash = "url_number_" + index;
				showAlert("Такой url уже был добавлен ранее");
			}
		});
		if (!urlAlreadyExist) {
			$scope.items.unshift({
				url: $scope.add_url
			});
		}
		$scope.add_url = "";
	};

});