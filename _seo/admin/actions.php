<?php
header('Content-Type: text/html; charset=utf-8');
require_once dirname(__FILE__) . '/defines.php';
require_once _SEO_DIRECTORY . 'functions.php';

$GLOBALS['_seo_config'] = include dirname(__FILE__) . '/../config.php';

if(@$_GET['module'] == 'information_systems'){
   if($_GET['action'] == 'get' && $_GET['id']){
      echo php2js(_s_getInformationSystem($_GET['id']));
      exit;
   }
   if($_GET['action'] == 'saveAll' && $_GET['id']){
      _s_saveInformationSystems($_GET['id'], json_decode(trim($HTTP_RAW_POST_DATA), true));
      exit;
   }
}

if(@$_GET['module'] == 'redirects'){
   if($_GET['action'] == 'add'){
      $postData = trim($HTTP_RAW_POST_DATA);
      if(!empty($postData)){
         _s_addRedirects($postData);
      }
      exit;
   }
   if($_GET['action'] == 'get'){
      echo php2js(_s_getRedirects());
      exit;
   }

   if($_GET['action'] == 'delete'){
      $postData = json_decode($HTTP_RAW_POST_DATA, true);
      _s_deleteRedirect($postData['source'], $postData['dest']);
      exit;
   }
   exit;
}

if(@$_GET['module'] == 'error404'){
   if($_GET['action'] == 'get'){
      echo _s_getErrors404();
      exit;
   }
   if($_GET['action'] == 'save'){
      _s_saveErrors404($HTTP_RAW_POST_DATA);
      exit;
   }
   exit;
}

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

