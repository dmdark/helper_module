<?php
$config = array(
   'encoding' => 'utf-8',

   'module_meta_enabled' => 1,
   'module_urls_enabled' => 1,
   'module_headers_enabled' => 1,
   'module_query' => array(
      'enabled' => 0,
      'use_library' => null, //'PQLite',  'phpQuery', null
      'common_functions' => array(
         '_setH1'
      ),
      'functions' => array(
         '/' => 'function_main',
         '/contacts/' => 'function_contacts',
      )
   ),
   'adminConfig' => array(
      'additionalTags' => array(
         'h1', /* 'h2', 'h3',*/
      ),
      'login' => 'admin',
      'password' => '9beff0a36668837f7e6f3c4579838e22', // md5, можно сгенерить на http://md5x.ru/
   ),
   'rememberMode' => 1
);

/*
 Функции обработки контента. Существует 3 варианта.
1. use_library => 'phpQuery'. Большая и навороченная библиотека, но нужен xml модуль, плюс иногда глючит кодировка, не любит кривой хтмл. В $doc - объект phpQuery.
2. use_library => 'PQLite'. Аналог phpQuery. Меньше. Меньше проблем. В $doc - объект PQLite.
3. use_library => null. $doc приходит пустой, работаем напрямую со строкой $GLOBALS['_seo_content'].

$pageInfo - информация о текущей странице из config.ini в виде массива.
 */
if(!function_exists('_setH1')){
   function _setH1($doc = '', $pageInfo)
   {
      if(@get_class($doc) == 'phpQuery'){
         //$doc['.contact-info h1'] = $pageInfo['h1'];
      } elseif(@get_class($doc) == 'PQLite'){
         if(!empty($pageInfo['h1'])){
            $tags = $doc->find('h1');
            $tags->setInnerHtml($pageInfo['h1']);
         }
      } elseif(empty($doc)){
         // $GLOBALS['_seo_content'] = str_replace('ул. Рябиновая 45', 'ул. Рябиновая 46', $GLOBALS['_seo_content']);
      }

   }
}

if(!function_exists('function_main')){
   function function_main($doc, $pageInfo)
   {
      if(@get_class($doc) == 'PQLite'){
         $tags = $doc->find('title');
         $str = $tags->getOuterHtml();
         $str .= "\n" . '<meta name="keywords" content="товары для дома оптом, товары для дома оптом недорого, товары для дома оптом дешево, товары для дома оптом, купить" />';
         $tags->setOuterHtml($str);
      }
   }
}
return $config;