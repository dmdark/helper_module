<?php
$config = array(
   'encoding' => 'utf-8',

   'module_meta_enabled' => 1,
   'module_urls_enabled' => 1,
   'module_headers_enabled' => 1,
   'module_query' => array(
      'enabled' => 0,
      'common_functions' => array(
         '_setH1'
      ),
      'functions' => array(
         '/' => 'function_main',
         '/contacts/' => 'function_contacts',
      )
   ),
   'module_label_replacement' => 1,
   'adminConfig' => array(
      'additionalTags' => array(
         'h1', 't_1', //'t_2', // для textarea переменная должна начинаться с префикса t_
      ),
      'login' => 'admin',
      'password' => '9beff0a36668837f7e6f3c4579838e22', // md5, можно сгенерить на http://md5x.ru/
   ),
   'rememberMode' => 1
);

/*
 Функции обработки контента. Существует 3 варианта.
 работаем напрямую со строкой $GLOBALS['_seo_content'].
$pageInfo - информация о текущей странице из config.ini в виде массива.
 */
if(!function_exists('_setH1')){
   function _setH1($pageInfo)
   {
      // $GLOBALS['_seo_content'] = str_replace('ул. Рябиновая 45', 'ул. Рябиновая 46', $GLOBALS['_seo_content']);
   }
}

if(!function_exists('function_main')){
   function function_main($pageInfo)
   {

   }
}
return $config;