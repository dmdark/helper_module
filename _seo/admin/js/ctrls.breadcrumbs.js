module.controller('breadCrumbs', function($scope,$http) {

	// Проверка, на существование URL
	function urlExist(urlString,arr) {
		for (var i=0; i<arr.length; i++) {
			if (arr[i].url === urlString) return true;
		}
		return false;
	}

	$scope.addSection = function(newSection,sections) {
		if ( !urlExist(newSection.url,$scope.sections) ) {
			sections.push({"url": newSection.url, "title": newSection.title, "items": []});
		} else {
			alert('Такой URL был добавлен ранее!');
		}
		// Очистка поля
		newSection.url = '';
		newSection.title = '';
	}

	$scope.addSubSection = function(section) {
		section.items.push({"url": "", "title": "", "items": []});
	}

	$scope.removeSection = function(sections,index) {
		sections.splice(index,1);
	}

	$scope.removeSubSection = function(element,index) {
		element.items.splice(index,1);
	}

	$scope.saveAll = function() {
		$http.post('actions.php?module=breadcrumbs&action=save', $scope.sections).success(function() {
			$scope.alertMessage = "Сохранение успешно";
			setTimeout(function() {
				$scope.alertMessage = "";
			}, 2000);
		});
	}

	function getSections(){
		$http.get('actions.php?module=breadcrumbs&action=get').success(function (data) {
			if ( Array.isArray(data) ) $scope.sections = data;
			else $scope.sections = [];
		});
	}
	getSections();

	if ( !Array.isArray($scope.sections) ) $scope.sections = [];

});