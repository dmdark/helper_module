<?php
require_once dirname(__FILE__) . '/functions.php';
initConfig();

// отправляем правильную кодировку
header('Content-Type: text/html; charset=' . $GLOBALS['_seo_config']['encoding']);

if(@$GLOBALS['_seo_config']['module_urls_enabled']){
   // проверяем, может нам пришел старый урл, который мы должны бы заменить. Редиректим!
   $pageInfo = getCurrentPageInfo(false);
   if(!empty($pageInfo['newUrl'])){
      header('HTTP/1.1 301 Moved Permanently');
      header('Location: http://' . $_SERVER['HTTP_HOST'] . $pageInfo['newUrl']);
      exit;
   }

   $pageInfo = getCurrentPageInfo(true, false);
   if(!empty($pageInfo['newUrl'])){
      header('HTTP/1.1 200 Ok');
      $_SERVER['REQUEST_URI'] = $pageInfo['url'];
      $_GET = getGETparamsFromUrl($pageInfo['url']);
   }

}


function _seo_apply()
{
   if(@$GLOBALS['_seo_config']['module_urls_enabled']){
      // делаем ЧПУ, заменяя ссылки
      applyUrls();
   }

   // заменяем метатэги
   if(@$GLOBALS['_seo_config']['module_meta_enabled'] && !empty($GLOBALS['_seo_content'])){

      applyMeta();
   }

   // вызываем пользовательскую функцию для страницы и меняем контент как хотим
   if(@$GLOBALS['_seo_config']['module_query']['enabled'] && !empty($GLOBALS['_seo_content'])){
      $commonFunctions = @$GLOBALS['_seo_config']['module_query']['common_functions'];
      $userFunction = @$GLOBALS['_seo_config']['module_query']['functions'][getCurrentUrl()];
      if(!empty($commonFunctions) || !empty($userFunction)){
         $doc = '';
         $library = @$GLOBALS['_seo_config']['module_query']['use_library'];
         if($library == 'phpQuery'){
            require_once dirname(__FILE__) . '/phpQuery-onefile.php';
            $doc = phpQuery::newDocumentHTML($GLOBALS['_seo_content']);
         } elseif($library == 'PQLite'){
            require_once dirname(__FILE__) . '/PQLite/PQLite.php';
            $doc = new PQLite($GLOBALS['_seo_content']);

         }

         // common functions
         if(!empty($commonFunctions)) foreach($commonFunctions as $commonFunction){
            call_user_func($commonFunction, $doc, getCurrentPageInfo());
         }

         // specific page function
         if(!empty($userFunction)){
            call_user_func($userFunction, $doc, getCurrentPageInfo());
         }

         if($library == 'phpQuery'){
            $GLOBALS['_seo_content'] = $doc->htmlOuter();
         } elseif($library == 'PQLite'){
            $GLOBALS['_seo_content'] = $doc->getHTML();
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
   return html_entity_decode($_SERVER['REQUEST_URI']);
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
   foreach($GLOBALS['_seo_config']['pages'] as $oldUrl => $pageInfo){
      if(!empty($pageInfo['newUrl'])){
         $oldUrlStr = '(' . preg_quote(htmlspecialchars(str_replace('%2F', '/', rawurlencode($oldUrl)))) . ')|(' . preg_quote(htmlspecialchars($oldUrl)) . ')';
         $GLOBALS['_seo_content'] = preg_replace('!(<a[^<>]+?href=("|\'))(http://)?(' . preg_quote($_SERVER['HTTP_HOST']) . ')?' . $oldUrlStr . '(("|\')([^<>]+?)?>.+?</a>)!simx',
               '$1' . $pageInfo['newUrl'] . '$7', $GLOBALS['_seo_content']);
      }
   }

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