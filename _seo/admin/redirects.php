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

   </div>
<?php endif; ?>
</body>
</html>
