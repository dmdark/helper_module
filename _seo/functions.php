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
		if (isset($result[$currentUrl][$currentTag])) {$result[$currentUrl][$currentTag] .= $line;}
		else $result[$currentUrl][$currentTag] = $line;
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
			$data[$key] = file_exists($dir . $key . '.html')? file_get_contents($dir . $key . '.html') : '';
      }
   }

   return $data;
}

function getSpecialProperty($url, $key)
{
	$filename = getDatabaseDirectoryForUrl($url) . $key . '.html';
   return file_exists($filename)? file_get_contents($filename) : '';
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
      return file_exists(_SEO_DIRECTORY . 'errors404.ini')? file(_SEO_DIRECTORY . 'errors404.ini', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : array();
   } else {
      return file_exists(_SEO_DIRECTORY . 'errors404.ini')? file_get_contents(_SEO_DIRECTORY . 'errors404.ini') : '';
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
            unlink(_SEO_DIRECTORY . 'db' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'is_' . $id . '.json');
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

function _s_renderInformationSystem($information_system_config, $properties)
{
   // избавляемся от добавленных частей, чтобы получить корректный урл
	$urls = array();
	foreach ($properties['url'] as $url) {
		$urls[] = preg_replace('/(\?|&)_s=[^&]+/simx', '', $url);
	}

	$itemList = array();

	foreach ($urls as $url) {
		$file = getDatabaseDirectoryForUrl($url) . 'is_' . $information_system_config['id'] . '.json';
		if(!file_exists($file)) continue;
		$information_items = json_decode(file_get_contents($file), true);
		// Очищаем массив от отключенных, добавляем URL к их параметрам
		$onlyVisibleItems = array();
		foreach ($information_items['items'] as $item) {
			if ($item['visibility']) {
				$item['is_url'] = $url;
				$onlyVisibleItems[] = $item;
			}
		}
		$information_items['items'] = $onlyVisibleItems;
		// Проверяем выводить ли 1 элемент, если да, то выводим и ничего больше не делаем
		if ( $properties['show_item'] && !empty($_GET['_s']) && $information_system_config['template_item'] ) {
			foreach($information_items['items'] as $item){
				if($item['url'] == $_GET['_s']){
					return _s_render($information_system_config['template_item'], array('information_item' => $item, 'information_items' => $information_items, 'config' => $information_system_config));
				}
			}
		}
		if(empty($information_items) || empty($information_items['items'])) continue;
		$itemList = array_merge($itemList,$information_items['items']);
	}

	if (empty($itemList)) return;

	// Применение Random
	if (array_key_exists('random',$properties) && $properties['random']) shuffle($itemList);
	// Применение max
	if (array_key_exists('max',$properties)) $itemList = array_slice($itemList,0,$properties['max']);

	// Вывод элементов в шаблоне
   if($information_system_config['template_list']){
		$return = _s_render($information_system_config['template_list'], array('items' => $itemList, 'config' => $information_system_config));
		// Добавление формы отзывов для посетителей
		if ($properties['reply'] && array_key_exists('template_reply',$information_system_config) ) {
			$replyTemplate = _SEO_DIRECTORY . 'templates/' . $information_system_config['template_reply'];
			if (file_exists($replyTemplate)) $return.= include($replyTemplate);
		}
      return $return;
   }
}

function _s_getInformationSystemUrl($system_url, $item_url)
{
   $url = $system_url;
   if(strpos($url, '?') !== false){
      $url .= '&';
   } else{
      $url .= '?';
   }
   $url .= '_s=' . $item_url;
   return $url;
}

function _s_render($file, $data)
{
   extract($data);
   $template = _SEO_DIRECTORY . 'templates' . DIRECTORY_SEPARATOR . $file;
   if(file_exists($template)){
      return include($template);
   }
}