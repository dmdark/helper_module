<?php
require_once dirname(__FILE__) . '/admin/defines.php';
require_once dirname(__FILE__) . '/functions.php';
initConfig();

// отправляем правильную кодировку
header('Content-Type: text/html; charset=' . $GLOBALS['_seo_config']['encoding']);

if(@$GLOBALS['_seo_config']['module_urls_enabled']){
   // проверяем, может нам пришел старый урл, который мы должны бы заменить. Редиректим!
   $pageInfo = getCurrentPageInfo(false);
   if(!empty($pageInfo['newUrl'])){

      if(@$GLOBALS['_seo_config']['rememberMode']){
         $cache = getRememberCache();
         $rememberCache = array();
         $rememberCache['_GET'] = $_GET;

         $the_server = array();
         foreach($_SERVER as $key => $value){
            if(strpos($key, 'HTTP_') === 0) continue;
            if(strpos($key, 'SERVER_') === 0) continue;
            if(strpos($key, 'REMOTE_') === 0) continue;
            if(strpos($key, 'REDIRECT_') === 0) continue;
            if(strpos($key, 'GATEWAY_') === 0) continue;
            if($key == 'PATH') continue;
            if($key == 'REQUEST_TIME') continue;
            $the_server[$key] = $value;
         }
         $rememberCache['_SERVER'] = $the_server;

         $cache[$pageInfo['newUrl']] = $rememberCache;
         writeRememberCache($cache);
      }

      header('HTTP/1.1 301 Moved Permanently');
      header('Location: http://' . $_SERVER['HTTP_HOST'] . $pageInfo['newUrl']);
      exit;
   }

   // быть может модуль редиректов хочет вмешаться?
   $redirects = _s_getRedirects();
   foreach($redirects as $redirect){
      if($redirect['source'] == $_SERVER['REQUEST_URI']){
         header('HTTP/1.1 301 Moved Permanently');
         header('Location: http://' . $_SERVER['HTTP_HOST'] . $redirect['dest']);
         exit;
      }
   }


   // быть может модуль 404 ошибки?
   $errors404 = _s_getErrors404(true);
   if(in_array($_SERVER['REQUEST_URI'], $errors404)){
      header("HTTP/1.0 404 Not Found");
      header("Status: 404 Not Found");
      $page404 = _SEO_DIRECTORY . '404.html';
      if(file_exists($page404)){
         echo file_get_contents($page404);
      }
      exit;
   }

   $pageInfo = getCurrentPageInfo(true, false);
   if(!empty($pageInfo['newUrl'])){
      header('HTTP/1.1 200 Ok');
      $_SERVER['REQUEST_URI'] = $pageInfo['url'];
      $_GET = getGETparamsFromUrl($pageInfo['url']);

      $cache = getRememberCache();
      if(isset($cache[$pageInfo['newUrl']])){
         $rememberCache = $cache[$pageInfo['newUrl']];
         foreach($rememberCache['_SERVER'] as $key => $value){
            $_SERVER[$key] = $value;
         }
         foreach($rememberCache['_GET'] as $key => $value){
            $_GET[$key] = $value;
            $_REQUEST[$key] = $value;
         }
      }
   }
}

function _seo_ob_callback($buffer)
{
   $GLOBALS['_seo_content'] = $buffer;
   _seo_apply();
   return $GLOBALS['_seo_content'];
}

function _seo_apply()
{
   initConfig();

   if(@$GLOBALS['_seo_config']['encoding'] == 'utf-8'){
      deleteNonUtfSymbols();
   }

   if(@$GLOBALS['_seo_config']['module_urls_enabled']){
      // делаем ЧПУ, заменяя ссылки
      applyUrls();
   }

   // заменяем метатэги
   if(@$GLOBALS['_seo_config']['module_meta_enabled'] && !empty($GLOBALS['_seo_content'])){
      applyMeta();
   }

   if(@$GLOBALS['_seo_config']['module_label_replacement'] && !empty($GLOBALS['_seo_content'])){
      applyLabelReplacement();
   }

   applyInformationSystems();


   // вызываем пользовательскую функцию для страницы и меняем контент как хотим
   if(@$GLOBALS['_seo_config']['module_query']['enabled'] && !empty($GLOBALS['_seo_content'])){
      $commonFunctions = @$GLOBALS['_seo_config']['module_query']['common_functions'];
      $userFunction = @$GLOBALS['_seo_config']['module_query']['functions'][getCurrentUrl()];
      if(!empty($commonFunctions) || !empty($userFunction)){

         // common functions
         if(!empty($commonFunctions)) foreach($commonFunctions as $commonFunction){
            call_user_func($commonFunction, getCurrentPageInfo());
         }

         // specific page function
         if(!empty($userFunction)){
            call_user_func($userFunction, getCurrentPageInfo());
         }
      }


   }

   // ищем первый h1
   if(@$GLOBALS['_seo_config']['module_headers_enabled']){
      applyHeaders();
   }
}


// =============== FUNCTIONS ==================

function getCurrentUrl()
{
   if(!defined('_seo_request_uri')){
      define('_seo_request_uri', html_entity_decode($_SERVER['REQUEST_URI']));
   }
   return _seo_request_uri;
}

function getCurrentPageInfo($searchNewPage = true, $searchOldPage = true)
{
   $currentUrl = getCurrentUrl();

   if($searchOldPage){
      $pageInfo = @$GLOBALS['_seo_config']['pages'][$currentUrl];
      if(!empty($pageInfo)) return $pageInfo;
   }
   if($searchNewPage){
      foreach($GLOBALS['_seo_config']['pages'] as $pageInfo){
         if(@$pageInfo['newUrl'] == $currentUrl){
            return $pageInfo;
         }
      }
   }
}

function applyMeta()
{
   try{
      $pageInfo = getCurrentPageInfo();
      // $GLOBALS['_seo_content'] .= '<pre>'.getCurrentUrl().print_r($pageInfo, true);

      $add_regexp = '';
      if($GLOBALS['_seo_config']['encoding'] == 'utf-8'){
         $add_regexp = 'u';
      }
      if((!empty($pageInfo['description']) || !empty($pageInfo['title']) || !empty($pageInfo['keywords'])) && function_exists('mb_strpos')){
         $headStart = mb_strpos($GLOBALS['_seo_content'], '<head');
         $headEnd = mb_strpos($GLOBALS['_seo_content'], '</head>');
         $headHtml = mb_substr($GLOBALS['_seo_content'], $headStart, $headEnd - $headStart);
         $closeHeader = mb_strpos($headHtml, '>');
         $headHtml = mb_substr($headHtml, $closeHeader + 1);

         if(!empty($pageInfo['title'])){
            $headHtml = preg_replace('%<title>(.+?)</title>%simx', '<title>' . $pageInfo['title'] . '</title>', $headHtml);
         }
         if(!empty($pageInfo['description'])){
            if(preg_match('/<meta[^>]+name="description"[^>]+content="([^>]+)?"/simx', $headHtml)){
               $headHtml = preg_replace('/<meta[^>]+name="description"[^>]+content="([^>]+)?"/simx' . $add_regexp, '<meta name="description" content="' . $pageInfo['description'] . '"', $headHtml);
            } else{
               $headHtml .= '<meta name="description" content="' . $pageInfo['description'] . '" />' . "\n";
            }
         }
         if(!empty($pageInfo['keywords'])){
            if(preg_match('/<meta[^>]+name="keywords"[^>]+content="([^>]+)?"/simx', $headHtml)){
               $headHtml = preg_replace('/<meta[^>]+name="keywords"[^>]+content="([^>]+)?"/simx' . $add_regexp, '<meta name="keywords" content="' . $pageInfo['keywords'] . '"', $headHtml);
            } else{
               $headHtml .= '<meta name="keywords" content="' . $pageInfo['keywords'] . '" />' . "\n";
            }
         }
         /*$GLOBALS['_seo_content'] = mb_substr($GLOBALS['_seo_content'], 0, $headStart + $closeHeader) . $headHtml . (mb_substr($GLOBALS['_seo_content'], $headEnd + 7));*/
         // $GLOBALS['_seo_content'] = substr($GLOBALS['_seo_content'], 0, $headStart + $closeHeader + 1) . $headHtml . (substr($GLOBALS['_seo_content'], $headEnd));
         $GLOBALS['_seo_content'] = preg_replace('%<head(.+?)</head>%simx' . $add_regexp, '<head>' . $headHtml . '</head>', $GLOBALS['_seo_content']);
      }
   } catch (Exception $e){
      echo '<!-- Seo module error: ' . $e->getTrace() . '-->';
   }
}

function applyHeaders()
{
   try{
      $pageInfo = getCurrentPageInfo();
      if(!empty($pageInfo['h1']) && function_exists('mb_strpos')){
         $h1Start = mb_strpos($GLOBALS['_seo_content'], '<h1');
         $h1End = mb_strpos($GLOBALS['_seo_content'], '</h1>');
         $h1Html = mb_substr($GLOBALS['_seo_content'], $h1Start, $h1End - $h1Start + mb_strlen('</h1>'));
         $h1StartTagEnd = mb_strpos($h1Html, '>');
         $GLOBALS['_seo_content'] = mb_substr($GLOBALS['_seo_content'], 0, $h1Start + $h1StartTagEnd + 1) . $pageInfo['h1'] . mb_substr($GLOBALS['_seo_content'], $h1End);
      }

   } catch (Exception $e){
      echo '<!-- Seo module error: ' . $e->getTrace() . '-->';
   }
}

function applyUrls()
{
   $add_regexp = '';
   if($GLOBALS['_seo_config']['encoding'] == 'utf-8'){
      $add_regexp = 'u';
   }
   foreach($GLOBALS['_seo_config']['pages'] as $oldUrl => $pageInfo){
      if(!empty($pageInfo['newUrl'])){
         $oldUrlStr = '((' . preg_quote(htmlspecialchars(str_replace('%2F', '/', rawurlencode($oldUrl)))) . ')|(' . preg_quote(htmlspecialchars($oldUrl)) . '))';
         $GLOBALS['_seo_content'] = preg_replace('!(<a[^<>]+?href=("|\'))(http://)?(' . preg_quote($_SERVER['HTTP_HOST']) . ')?' . $oldUrlStr . '(("|\')([^<>]+?)?>.+?</a>)!simx' . $add_regexp,
               '$1' . $pageInfo['newUrl'] . '$8', $GLOBALS['_seo_content']);
      }
   }

}

function applyLabelReplacement()
{
   $pageInfo = getCurrentPageInfo();

   $e = $GLOBALS['_seo_config']['encoding'];

   $keys = $GLOBALS['_seo_config']['adminConfig']['additionalTags'];
   foreach($keys as $key){
      if(strpos($key, 't_') !== false){
         $value = trim(getSpecialProperty($pageInfo['url'], $key));
         if(empty($value)){
            continue;
         }

         $startKey = '<!--$$' . $key . '-->';
         $endKey = '<!--/$$' . $key . '-->';

         $posStart = mb_strpos($GLOBALS['_seo_content'], $startKey, null, $e);
         $posEnd = mb_strpos($GLOBALS['_seo_content'], $endKey, $posStart, $e);
         if(empty($posEnd)){
            $posEnd = $posStart;
         }
         $posEnd += mb_strlen($endKey, $e);

         if(!empty($posStart)){
            $GLOBALS['_seo_content'] = mb_substr($GLOBALS['_seo_content'], 0, $posStart, $e) . $value . mb_substr($GLOBALS['_seo_content'], $posEnd, 99999999, $e);
         }
      }
   }
}

function applyInformationSystems()
{
   $e = $GLOBALS['_seo_config']['encoding'];
   $configSystems = $GLOBALS['_seo_config']['adminConfig']['information_systems'];

   if(empty($configSystems)) return;
   foreach($configSystems as $config_item){
      $searchFor = '<!--$*' . $config_item['id'] . '-->';
      $pos = mb_strpos($GLOBALS['_seo_content'], $searchFor, null, $e);

      if(empty($pos)) continue;


      $html = _s_renderInformationSystem($config_item, $_SERVER['REQUEST_URI']);

      $GLOBALS['_seo_content'] = str_replace($searchFor, $html, $GLOBALS['_seo_content']);
   }
   // <!--**is_news-->
}

function initConfig()
{
   $GLOBALS['_seo_config'] = include dirname(__FILE__) . '/config.php';

   $data = config2file(dirname(__FILE__) . '/config.ini');
   $GLOBALS['_seo_config']['pages'] = $data;

}

function getGETparamsFromUrl($url)
{
   $get = array();
   if(preg_match('/\?(.+)/simx', $url, $regs)){
      $getStr = $regs[1];
      if(!empty($getStr)){
         $getArray = explode('&', $getStr);
         foreach($getArray as $getItemStr){
            list($name, $val) = explode('=', $getItemStr);
            $get[$name] = $val;
         }
      }
   }
   return $get;
}

function deleteNonUtfSymbols()
{
   $GLOBALS['_seo_content'] = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]' .
         '|[\x00-\x7F][\x80-\xBF]+' .
         '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' .
         '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})' .
         '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
         '?', $GLOBALS['_seo_content']);

   $GLOBALS['_seo_content'] = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]' .
         '|\xED[\xA0-\xBF][\x80-\xBF]/S', '?', $GLOBALS['_seo_content']);

}