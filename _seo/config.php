<?php
$config = array(
   'encoding' => 'utf-8', // @todo get it automaticaly
   'using_htaccess' => 0,
   /*
    Два способа подключения:
    1. using_htaccess => 1. В .htaccess прописывается код:

    # -==== SEO MODULE ===-
     # если обращения напрямую на index.php
     RewriteCond %{REQUEST_URI} !^/admin
     RewriteCond %{REQUEST_METHOD} !=POST
     RewriteCond %{REQUEST_URI} !.*\.(ico|gif|jpg|jpeg|png|js|css)
     RewriteCond %{REQUEST_FILENAME} -f
     RewriteCond %{REQUEST_FILENAME} index\.php$
     RewriteCond %{QUERY_STRING} !seo_request=1
     RewriteRule $ _seo/index.php?_seo=$1 [QSA,L]

     # перехват всего
     RewriteCond %{REQUEST_URI} !^/admin
     RewriteCond %{REQUEST_METHOD} !=POST
     RewriteCond %{REQUEST_URI} !.*\.(ico|gif|jpg|jpeg|png|js|css)
     RewriteCond %{REQUEST_FILENAME} !-f
     RewriteCond %{REQUEST_FILENAME} !-d
     RewriteCond %{QUERY_STRING} !seo_request=1
     RewriteRule ^(.*)$ /_seo/index.php?_seo=$1 [QSA,L]

     # перехват главной страницы
     RewriteCond %{REQUEST_URI} !^/admin
     RewriteCond %{REQUEST_METHOD} !=POST
     RewriteCond %{REQUEST_URI} !.*\.(ico|gif|jpg|jpeg|png|js|css)
     RewriteCond %{REQUEST_URI} "^/$"
     RewriteCond %{QUERY_STRING} !seo_request=1
     RewriteRule ^(.*) /_seo/index.php?_seo=$1 [QSA,L]
     # -==== SEO MODULE END ===-

   2. using_htaccess => 0
         Перехватывается буфер (например в index.php в самом верху), прописывается
         require_once dirname(__FILE__) . '/_seo/index.php';
         ob_start();

         После всего вывода (например в конце index.php - НО ДО вызова функции exit() если такая есть) прописывается

         $GLOBALS['_seo_content'] = ob_get_clean();
         _seo_apply();
         echo $GLOBALS['_seo_content'];

       */
   'using_entry_point' => null, // для сайтов где всё идет через 1 точку, можно вызывать её напрямую. Иначе удалить.

   'module_meta_enabled' => 1,
   'module_urls_enabled' => 1,
   'module_headers_enabled' => 1,
   'module_query' => array(
      'enabled' => 1,
      'use_library' => null, //'PQLite', // PQLite, phpQuery, null
      'common_functions' => array(
         '_setH1'
      ),
      'functions' => array(
         '/' => 'function_main',
         /*'/contacts/ => function_contacts,*/
      )
   ),
   'adminConfig' => array(
      'additionalTags' => array(
         'h1', /* 'h2', 'h3',*/
      ),
      'login' => 'admin',
      'password' => '9beff0a36668837f7e6f3c4579838e22', // md5, можно сгенерить на http://md5x.ru/
   ),
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