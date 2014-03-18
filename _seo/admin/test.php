<?php
require_once 'defines.php';
require_once _BLOCKS_DIRECTORY . 'header.php';
?>

<body>

<?php if(!isset($_SESSION['_seo_auth'])): ?>
   <?php require_once _BLOCKS_DIRECTORY . 'auth.php'; ?>
<?php else: ?>
   <?php require_once _BLOCKS_DIRECTORY . 'navigation.php'; ?>

   <div class="container">
      <?php require_once(dirname(__FILE__) . '/tests/tests.php'); ?>
      <label class="label <?php echo bool_to_label_class(test_mb_functions()); ?>">Модуль mbstring</label>
      <label class="label <?php echo bool_to_label_class(test_php2js()); ?>">php2js</label>
      <label class="label <?php echo bool_to_label_class(test_iconv()); ?>">iconv</label>
      <label class="label <?php echo bool_to_label_class(test_preg_match()); ?>">preg_match</label>
      <label class="label <?php echo bool_to_label_class(test_permissions()); ?>">permissions</label>
      <label class="label <?php echo bool_to_label_class(test_index()); ?>">Основной модуль</label>
		<label class="label <?php echo bool_to_label_class(test_redirects()); ?>">301</label>
		<label class="label <?php echo bool_to_label_class(test_pageNotFound()); ?>">404</label>
   </div>
<?php endif; ?>
</body>
</html>
