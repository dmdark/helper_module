<?php
require_once 'defines.php';
require_once _BLOCKS_DIRECTORY . 'header.php';
?>
<body ng-controller="breadCrumbs">
<?php if(!isset($_SESSION['_seo_auth'])): ?>
	<?php require_once _BLOCKS_DIRECTORY . 'auth.php'; ?>
<?php else: ?>
	<?php require_once _BLOCKS_DIRECTORY . 'navigation.php'; ?>

<!-- Стили для списков -->
	<style>
		.list-group {
			border: 1px solid #ddd;
			border-top: none;
			background-color: rgba(31,63,127,.05);
		}
		.list-group .list-group-item {
			border-left: none;
			border-right: none;
			border-bottom: none;
			border-radius: 0;
			background: none;
		}
		.list-group .list-group {
			padding-left: 30px;
			margin-bottom: 0;
			border-bottom: none;
			border-left: none;
			border-right: none;
		}
	</style>

<!-- Наружная часть -->
	<div>
		<div class="container" style="margin-top: 50px; margin-bottom: 30px;">
			<div class="row">
				<div class="col-xs-4">
					<div class="form-group">
						<input type="text" class="form-control" placeholder="URL раздела" ng-model="newSection.url">
					</div>
				</div>
				<div class="col-xs-4">
					<div class="form-group">
						<input type="text" class="form-control" placeholder="Заголовок раздела" ng-model="newSection.title">
					</div>
				</div>
				<div class="col-xs-4">
					<div class="pull-right">
						<button class="btn btn-success" ng-click="addSection(newSection, sections)"><span class="glyphicon glyphicon-plus"></span></button>
					</div>
				</div>
			</div>
		</div>
		<div class="container" ng-repeat="section in sections" ng-init="parent = section">
			<div class="row">
				<ul class="list-group">
					<div>
						<li class="list-group-item">
							<div class="container-fluid">
								<div class="row">
									<div class="col-xs-4">
										<input type="text" class="form-control" placeholder="URL ресурса" value="{{section.url}}" ng-model="section.url">
									</div>
									<div class="col-xs-4">
										<input type="text" class="form-control" placeholder="Заголовок раздела" value="{{section.title}}" ng-model="section.title">
									</div>
									<div class="col-xs-4">
										<div class="pull-right">
											<button class="btn btn-primary" ng-click="findChildren(section)" title="Найти и добавить подразделы автоматически"><span class="glyphicon glyphicon-zoom-in"></span></button>
											<button class="btn btn-success" ng-click="addSubSection(section)" title="Добавить подраздел"><span class="glyphicon glyphicon-plus"></span></button>
											<button class="btn btn-danger" ng-click="removeSection(sections,$index)" title="Удалить раздел"><span class="glyphicon glyphicon-remove"></span></button>
										</div>
									</div>
								</div>
							</div>
						</li>
						<div ng-app="Application" ng-controller="breadCrumbs">
							<ul class="list-group" ng-repeat="subsection in parent.items" ng-init="thisElement = subsection" ng-include="'tree_item_renderer.html'"></ul>
						</div>
					</div>
				</ul>
			</div>
		</div>
	</div>

<!-- Шаблон для внутренних элементов -->
	<script type="text/ng-template"  id="tree_item_renderer.html">
		<li class="list-group-item">
			<div class="container-fluid">
				<div class="row">
					<div class="col-xs-4">
						<input type="text" class="form-control" placeholder="URL ресурса" value="{{subsection.url}}" ng-model="subsection.url">
					</div>
					<div class="col-xs-4">
						<input type="text" class="form-control" placeholder="Заголовок раздела" value="{{subsection.title}}" ng-model="subsection.title">
					</div>
					<div class="col-xs-4">
						<div class="pull-right">
							<button class="btn btn-primary" ng-click="findChildren(subsection)" title="Найти и добавить подразделы автоматически"><span class="glyphicon glyphicon-zoom-in"></span></button>
							<button class="btn btn-success" ng-click="addSubSection(subsection)" title="Добавить подраздел"><span class="glyphicon glyphicon-plus"></span></button>
							<button class="btn btn-danger" ng-click="removeSubSection(parent,$index)" title="Удалить раздел"><span class="glyphicon glyphicon-remove"></span></button>
						</div>
					</div>
				</div>
			</div>
		</li>
		<ul class="list-group" ng-repeat="subsection in subsection.items" ng-init="parent = thisElement; thisElement = subsection" ng-include="'tree_item_renderer.html'"></ul>
	</script>

<!-- Сообщение о сохранении -->
	<div class="container-fluid" style="position: fixed; bottom: 12px;">
		<p class="text-danger" ng-hide="!alertMessage">{{ alertMessage }}</p>
		<button class="btn btn-primary" ng-click="saveAll()">Сохранить изменения</button>
	</div>

<!-- Временный вывод -->
<!--
	<div class="container" style="margin-top: 30px">
		<div class="row">
			<div class="col-xs-12">
				<pre>{{sections}}</pre>
			</div>
		</div>
	</div>
-->

<?php endif; ?>
</body>
</html>