<?php
if(!function_exists('php2js')){
   function php2js($a = false)
   {
      if(is_null($a)) return 'null';
      if($a === false) return 'false';
      if($a === true) return 'true';

      if(is_scalar($a)){
         if(is_float($a)){
            // Always use "." for floats.
            $a = str_replace(",", ".", strval($a));
         }

         // All scalars are converted to strings to avoid indeterminism.
         // PHP's "1" and 1 are equal for all PHP operators, but
         // JS's "1" and 1 are not. So if we pass "1" or 1 from the PHP backend,
         // we should get the same result in the JS frontend (string).
         // Character replacements for JSON.
         static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
               array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
         return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      $isList = true;
      for($i = 0, reset($a); $i < count($a); $i++, next($a)){
         if(key($a) !== $i){
            $isList = false;
            break;
         }
      }
      $result = array();
      if($isList){
         foreach($a as $v) $result[] = php2js($v);
         return '[ ' . join(', ', $result) . ' ]';
      } else{
         foreach($a as $k => $v) $result[] = php2js($k) . ': ' . php2js($v);
         return '{ ' . join(', ', $result) . ' }';
      }
   }
}
if(!function_exists('json_decode')){
   require_once _SEO_DIRECTORY . '/libs/JSON/JSON.php';

   function json_decode($arg)
   {
      global $_s_services_json;
      if(!isset($_s_services_json)){
         $_s_services_json = new Services_JSON();
      }
      return $_s_services_json->decode($arg);
   }
}

function config2file($file, $needConvert = true)
{
   $result = array();

   $lines = file($file);
   $currentUrl = 'undef';
   $currentTag = 'undef';
   foreach($lines as $line){
      if(preg_match('/^([a-zA-Z0-9]+)=/simxu', $line, $regs)){
         $currentTag = $regs[1];
         $line = trim(mb_substr($line, mb_strlen($currentTag) + 1));
         if($currentTag == 'url'){
            $currentUrl = $line;
         }
      }
      if(preg_match('/^=/simx', $line, $regs)){
         continue;
      }
      if($needConvert && $GLOBALS['_seo_config']['encoding'] != 'utf-8' && function_exists('iconv')){
         $line = iconv('utf-8', $GLOBALS['_seo_config']['encoding'], $line);
      }
      @$result[$currentUrl][$currentTag] .= $line;
   }
   return $result;
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
   unset($data['rememberCache']);
   unset($data['showCache']);
   foreach($data as $key => $val){
      // не сохраняем особые типы данных
      if(strpos($key, 't_') === false){
         $config .= $key . '=' . $val . "\n";
      }
   }
   return $config;
}

function getRememberCache()
{
   $filePath = dirname(__FILE__) . '/admin/remember_cache.txt';
   if(!file_exists($filePath)){
      return array();
   }

   return json_decode(file_get_contents($filePath), true);
}

function writeRememberCache($cache)
{
   file_put_contents(dirname(__FILE__) . '/admin/remember_cache.txt', php2js($cache));
}

// database for special items
function getDatabaseDirectoryForUrl($url)
{
   $dirName = rawurlencode($url);
   $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR;
   if(!is_dir($dir)){
      mkdir($dir, 0777, true);
   }
   return $dir;
}

function saveSpecialData(&$data)
{
   $dir = getDatabaseDirectoryForUrl($data['url']);
   foreach($data as $key => $value){
      if(strpos($key, 't_') !== false){
         $propertyFile = $dir . $key . '.html';
         file_put_contents($propertyFile, $value);
         unset($data[$key]);
      }
   }
}

function addSpecialProperties(&$data)
{
   $dir = getDatabaseDirectoryForUrl($data['url']);

   $keys = $GLOBALS['_seo_config']['adminConfig']['additionalTags'];
   foreach($keys as $key){
      if(strpos($key, 't_') !== false){
         $data[$key] = @file_get_contents($dir . $key . '.html');
         if(empty($data[$key])){
            $data[$key] = '';
         }
      }
   }

   return $data;
}

function getSpecialProperty($url, $key)
{
   return @file_get_contents(getDatabaseDirectoryForUrl($url) . $key . '.html');
}

// redirect functions

define('R_DELIM', '===');
define('R_FILE', _SEO_DIRECTORY . 'redirects.ini');

function _s_addRedirects($postData, $replaceAll = true)
{
   $resultArray = _s_getRedirects();

   $pairs = explode("\n", $postData);
   foreach($pairs as $pair){
      list($source, $dest) = array_map('_s_clear_url', explode(' ', $pair));
      if(!empty($source) && !empty($dest)){
         $resultArray[] = array(
               'source' => $source,
               'dest' => $dest,
         );
      }
   }

   file_put_contents(_SEO_DIRECTORY . 'redirects.ini', php2js($resultArray));
}

function _s_getRedirects()
{
   $decoded = json_decode(file_get_contents(_SEO_DIRECTORY . 'redirects.ini'), true);
   return (!empty($decoded)) ? $decoded : array();
}

function _s_deleteRedirect($source, $dest)
{
   $redirects = _s_getRedirects();

   foreach($redirects as $i => $item){
      if($item['source'] == $source && $item['dest'] == $dest){
         array_splice($redirects, $i, 1);
      }
   }
   file_put_contents(_SEO_DIRECTORY . 'redirects.ini', php2js($redirects));
}

function _s_clear_url($urlStr)
{
   return trim(parse_url($urlStr, PHP_URL_PATH) . parse_url($urlStr, PHP_URL_QUERY));
}

// 404 errors
function _s_getErrors404($asArray = false)
{
   if($asArray){
      return file(_SEO_DIRECTORY . 'errors404.ini');
   } else{
      return @file_get_contents(_SEO_DIRECTORY . 'errors404.ini');
   }
}

function _s_saveErrors404($data)
{
   $results = array();
   $errors = explode("\n", $data);
   foreach($errors as $error){
      $url = _s_clear_url($error);
      if(!empty($url)){
         $results[] = $url;
      }
   }
   file_put_contents(_SEO_DIRECTORY . 'errors404.ini', join("\n", $results));
}

// information_systems
function _s_getInformationSystem($id)
{
   $urls = array();
   if($handle = opendir(_SEO_DIRECTORY . 'db' . DIRECTORY_SEPARATOR)){
      while(false !== ($dir = readdir($handle))){
         if($dir != "." && $dir != ".."){
            $jsonFile = _SEO_DIRECTORY . 'db' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'is_' . $id . '.json';
            if(file_exists($jsonFile)){
               $urls[] = json_decode(file_get_contents($jsonFile), true);
            }
         }
      }
      closedir($handle);
   }
   return $urls;
}

function _s_deleteInformationSystem($id)
{
   if($handle = opendir(_SEO_DIRECTORY . 'db' . DIRECTORY_SEPARATOR)){
      while(false !== ($dir = readdir($handle))){
         if($dir != "." && $dir != ".."){
            @unlink(_SEO_DIRECTORY . 'db' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'is_' . $id . '.json');
         }
      }
      closedir($handle);
   }
}

function _s_saveInformationSystems($id, $urls)
{
   _s_deleteInformationSystem($id);

   foreach($urls as $url){
      $dir = getDatabaseDirectoryForUrl($url['url']);
      file_put_contents($dir . 'is_' . $id . '.json', php2js($url));
   }
}