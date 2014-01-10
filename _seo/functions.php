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

function _s_saveRedirects($postData)
{
   $resultArray = array();

   $pairs = explode("\n", $postData);
   foreach($pairs as $pair){
      list($source, $dest) = explode(' ', $pair);
      $source = trim(parse_url($source, PHP_URL_PATH) . parse_url($source, PHP_URL_QUERY));
      $dest = trim(parse_url($dest, PHP_URL_PATH) . parse_url($dest, PHP_URL_QUERY));
      if(!empty($source) && !empty($dest)){
         $resultArray[] = $source . '===' . $dest;
      }
   }
   file_put_contents(_SEO_DIRECTORY . 'redirects.ini', join("\n", $resultArray));
}

function _s_getRedirects()
{
   $contents = file_get_contents(_SEO_DIRECTORY . 'redirects.ini');
   $pairs = explode("\n", $contents);

   $result = array();
   if(!empty($pairs)){
      foreach($pairs as $pair){
         list($source, $dest) = explode('===', $pair);
         if(!empty($source) && !empty($dest)){
            $result[$source] = $dest;
         }
      }
   }
   return $result;
}

function _s_deleteRedirect($source, $dest)
{
   $resultArray = array();
   $redirects = _s_getRedirects();
   foreach($redirects as $key => $value){
      if(!($key == $source && $dest == $value)){
         $resultArray[] = $key . ' ' . $value;
      }
   }
   _s_saveRedirects(join("\n", $resultArray));
}