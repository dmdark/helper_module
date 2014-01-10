<?php
header('Content-Type: text/html; charset=utf-8');
define('_SEO_DIRECTORY', dirname(__FILE__) . '/../');
require_once _SEO_DIRECTORY . 'functions.php';
$GLOBALS['_seo_config'] = include dirname(__FILE__) . '/../config.php';

if(@$_GET['action'] == 'get_items'){
   $config = config2file(_SEO_DIRECTORY . 'config.ini', false);
   $rememberCache = getRememberCache();

   foreach($config as &$info){
      if(isset($rememberCache[$info['newUrl']])){
         $info['rememberCache'] = nl2br(print_r($rememberCache[$info['newUrl']], true));
      }

      $specialData = addSpecialProperties($info);
   }

   echo php2js(array_values($config));
   return;
}

$postData = json_decode($HTTP_RAW_POST_DATA, true);
if(!empty($postData)){
   $configData = '';
   $i = 0;

   foreach($postData as $data){
      if(empty($data['url'])) continue;

      // сохраняем особые типы данных
      saveSpecialData($data);

      if($i++ > 0){
         $configData .= "===\n";
      }
      $configData .= item2config($data);
   }
   if(!empty($configData)){
      file_put_contents(_SEO_DIRECTORY . 'config.ini', $configData);
   }
}

