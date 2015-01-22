<?php
require_once 'defines.php';
require_once _BLOCKS_DIRECTORY . 'header.php';
?>
<body ng-controller="Error404Controller" style="background-color: #FFF4F4;">

<?php if(!isset($_SESSION['_seo_auth'])): ?>
	<?php require_once _BLOCKS_DIRECTORY . 'auth.php'; ?>
<?php else: ?>
	<?php require_once _BLOCKS_DIRECTORY . 'navigation.php'; ?>
	<div class="container">
		<div style="margin-bottom: 10px;">Добавлять в формате: <br/>
			<code>url1</code><br/>
			<code>url2</code><br/>
		</div>
		<textarea class="form-control" rows="20" ng-model="urls" wrap="off"></textarea>
		<button class="btn btn-success btn-large" ng-click="Save()">Сохранить</button>
	</div>
<?php endif; ?>
</body>
</html>
