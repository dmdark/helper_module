<?php
session_start();

require_once _SEO_DIRECTORY . 'functions.php';
$config = require_once _SEO_DIRECTORY . 'config.php';

if(@!empty($_POST) && $_POST['login'] == $config['adminConfig']['login'] && md5($_POST['password']) == $config['adminConfig']['password']){
   $_SESSION['_seo_auth'] = 1;
   header('Location: /_seo/admin/');
   return;
}


?>
<!DOCTYPE html>
<html lang="en" ng-app="application">
<head>
   <meta charset="utf-8">
   <title>Matik SEO admin</title>
   <link rel="stylesheet" href="css/bootstrap.css">
   <link rel="stylesheet" href="css/styles.css">

   <?php if(isset($_SESSION['_seo_auth'])): ?>
      <script src="js/vendors/jquery2.1.js"></script>
      <script src="js/vendors/bootstrap.js"></script>
      <script src="js/vendors/angular.min.js"></script>
      <script src="js/vendors/angular-sanitize.min.js"></script>
      <script src="js/application.js"></script>
      <script src="js/filters.js"></script>
   <?php
   if($handle = opendir(_SEO_DIRECTORY . 'admin/js')){
   while(false !== ($entry = readdir($handle))){
   if(strpos($entry, 'ctrls.') === 0){
   ?>
      <script type="text/javascript" src="js/<?php echo $entry; ?>"></script>
   <?php
   }
   }
   }
      ?>

   <?php endif; ?>
</head>

