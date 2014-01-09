<?php
session_start();
define('_SEO_DIRECTORY', dirname(__FILE__) . '/../');
require_once _SEO_DIRECTORY . 'functions.php';
$config = require_once _SEO_DIRECTORY . 'config.php';

if(@!empty($_POST) && $_POST['login'] == $config['adminConfig']['login'] && md5($_POST['password']) == $config['adminConfig']['password']){
   $_SESSION['_seo_auth'] = 1;
   header('Location: /_seo/admin/');
   return;
}


?>
<!DOCTYPE html>
<html lang="en" ng-app>
<head>
   <meta charset="utf-8">
   <title>Matik SEO admin</title>
   <link rel="stylesheet" href="bootstrap.css">
   <link rel="stylesheet" href="styles.css">

   <?php if(isset($_SESSION['_seo_auth'])): ?>
      <script src="angular.min.js"></script>
      <script src="angular-sanitize.min.js"></script>
      <script src="scripts.js"></script>
   <?php endif; ?>
</head>

