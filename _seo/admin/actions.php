<?php

define('_SEO_DIRECTORY', dirname(__FILE__) . '/../');
require_once _SEO_DIRECTORY . 'functions.php';

if(@$_GET['action'] == 'get_items'){
   $config = config2file(_SEO_DIRECTORY . 'config.ini');
   echo php2js(array_values($config));
   return;
}

$postData = json_decode($HTTP_RAW_POST_DATA, true);
if(!empty($postData)){
   $configData = '';
   $i = 0;
   foreach($postData as $data){
      if(empty($data['url'])) continue;
      if($i++ > 0){
         $configData .= "===\n";
      }
      $configData .= item2config($data);
   }
   if(!empty($configData)){
      file_put_contents(_SEO_DIRECTORY . 'config.ini', $configData);
   }
}

function item2config($data)
{
   $config = '';
   $config .= 'url=' . $data['url'] . "\n";
   if(!empty($data['newUrl'])){
      $config .= 'newUrl=' . $data['newUrl'] . "\n";
   }
   $config .= 'title=' . $data['title'] . "\n";
   unset($data['url']);
   unset($data['newUrl']);
   unset($data['title']);
   foreach($data as $key => $val){
      $config .= $key . '=' . $val . "\n";
   }
   return $config;
}