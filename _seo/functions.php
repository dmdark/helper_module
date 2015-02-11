<?php
if(!function_exists('php2js')) {
	function php2js($a = false) {
		if(is_null($a)) return 'null';
		if($a === false) return 'false';
		if($a === true) return 'true';
		if(is_scalar($a)) {
			if(is_float($a)){
				// Always use "." for floats.
				$a = str_replace(",", ".", strval($a));
			}
			// All scalars are converted to strings to avoid indeterminism.
			// PHP's "1" and 1 are equal for all PHP operators, but
			// JS's "1" and 1 are not. So if we pass "1" or 1 from the PHP backend,
			// we should get the same result in the JS frontend (string).
			// Character replacements for JSON.
			static $jsonReplaces = array(
				array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
				array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"')
			);
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
	function json_decode($arg) {
		global $_s_services_json;
		if(!isset($_s_services_json)){
			$_s_services_json = new Services_JSON(3);
		}
		return $_s_services_json->decode($arg);
	}
}
if(!function_exists('json_encode')){
	require_once _SEO_DIRECTORY . '/libs/JSON/JSON.php';
	function json_encode($arg) {
		global $_s_services_json;
		if(!isset($_s_services_json)){
			$_s_services_json = new Services_JSON(3);
		}
		return $_s_services_json->encode($arg);
	}
}

if(!function_exists('file_put_contents')) {
	function file_put_contents($fileName,$content) {
		$handle = fopen($fileName,"w");
		$writeResult = fwrite($handle,$content);
		fclose($handle);
		return $writeResult;
	};
}
if(!function_exists('get_headers')) {
	function get_headers($url,$format=0) {
		$url = parse_url($url);
		$end = "\r\n\r\n";
		$fp = fsockopen($url['host'], (empty($url['port'])? 80 : $url['port']), $errno, $errstr, 30);
		if($fp) {
			$out = "GET / HTTP/1.1\r\n";
			$out.= "Host:  ".$url['host']."\r\n";
			$out.= "Connection: Close\r\n\r\n";
			$var = '';
			fwrite($fp, $out);
			while (!feof($fp)) {
				$var.=fgets($fp, 1280);
				if(strpos($var,$end)) break;
			}
			fclose($fp);
			$var = preg_replace("/\r\n\r\n.*\$/",'',$var);
			$var = explode("\r\n",$var);
			$v = array();
			if($format) {
				foreach($var  as $i) {
					if(preg_match('/^([a-zA-Z -]+):  +(.*)$/',$i,$parts)) $v[$parts[1]] = $parts[2];
				}
			}
			return $v;
		} else return false;
	}
}

// Получение конфигурации (название функции не соответствует выполняемому действию)
function config2file($file, $needConvert = true)
{
	$result = array();
	$currentUrl = 'undef';
	$currentTag = 'undef';
	if (_s_StorageType() == 'mysql') {
		$configContent = _s_DBmanageData('get','config');
		$lines = explode("\n",$configContent);
	} else {
		$lines = file($file);
	}
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

// Сохранение конфигурации
function _s_saveConfig2file($file,$data)
{
	if (_s_StorageType() == 'mysql') {
		_s_DBmanageData('save','config',$data);
	} else {
		return file_put_contents($file,$data);
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
	$content = false;
	if (_s_StorageType() == 'mysql') {
		$content = _s_DBmanageData('get','remember');
	} else {
		$filePath = dirname(__FILE__) . '/admin/remember_cache.txt';
		if(file_exists($filePath)) $content = file_get_contents($filePath);
	}
	return json_decode($content, true);
}
function writeRememberCache($cache)
{
	$content = php2js($cache);
	if (_s_StorageType() == 'mysql') {
		_s_DBmanageData('save','remember',$content);
	} else {
		file_put_contents(dirname(__FILE__) . '/admin/remember_cache.txt', $content);
	}
}

// database for special items
function getDatabaseDirectoryForUrl($url) {
	$dirName = md5($url);
	$dbDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR;
	if (!is_dir($dbDir)) mkdir($dbDir,0777);
	$dir = $dbDir . $dirName . DIRECTORY_SEPARATOR;
	if (!is_dir($dir)) mkdir($dir,0777);
	return $dir;
}
function saveSpecialData($data) {
	foreach($data as $key => $value){
		if(strpos($key, 't_') !== false){
			if (_s_StorageType() == 'mysql') {
				_s_DBmanageSpecial('save',$key,$data['url'],$value);
			} else {
				$dir = getDatabaseDirectoryForUrl($data['url']);
				$propertyFile = $dir . $key . '.html';
				file_put_contents($propertyFile, $value);
			}
			unset($data[$key]);
		}
	}
	return $data;
}

function addSpecialProperties($data)
{
	$storageType = _s_StorageType();
	if ($storageType != 'mysql') $dir = getDatabaseDirectoryForUrl($data['url']);
	$keys = $GLOBALS['_seo_config']['adminConfig']['additionalTags'];
	foreach($keys as $key){
		if(strpos($key, 't_') !== false){
			$content = false;
			if ($storageType == 'mysql') {
				$content = _s_DBmanageSpecial('get',$key,$data['url']);
			} else {
				if (file_exists($dir . $key . '.html')) $content = file_get_contents($dir . $key . '.html');
			}
			$data[$key] = $content? $content : '';
		}
	}
	return $data;
}

function getSpecialProperty($url, $key) {
	$content = false;
	if (_s_StorageType() == 'mysql') {
		$content = _s_DBmanageSpecial('get',$key,$url);
	} else {
		$filename = getDatabaseDirectoryForUrl($url) . $key . '.html';
		if (file_exists($filename)) $content = file_get_contents($filename);
	}
	return $content? $content : '';
}


// redirect functions
function _s_addRedirects($postData, $replaceAll = true) {
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
	_s_saveRedirectData(_SEO_DIRECTORY . 'redirects.ini', php2js($resultArray));
}
function _s_getRedirects() {
	if (_s_StorageType() == 'mysql') {
		$content = _s_DBmanageData('get','redirects');
	} else {
		$content = file_get_contents(_SEO_DIRECTORY . 'redirects.ini');
	}
	$decoded = json_decode($content, true);
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
	_s_saveRedirectData(_SEO_DIRECTORY . 'redirects.ini', php2js($redirects));
}
function _s_saveRedirectData($file,$data) {
	if (_s_StorageType() == 'mysql') {
		_s_DBmanageData('save','redirects',$data);
	} else {
		return file_put_contents($file,$data);
	}
}

function _s_clear_url($urlStr) {
	$urlInfo = parse_url($urlStr);
	$path = $urlInfo['path'];
	$query = $urlInfo['query'];
	if (!empty($query)) return trim($path.'?'.$query);
	return trim($path);
}

// 404 errors
function _s_getErrors404($asArray = false)
{
	$configExist = false;
	if (_s_StorageType() == 'mysql') {
		$content = _s_DBmanageData('get','errors404');
		if ($content) $configExist = true;
	} else {
		$fName = _SEO_DIRECTORY . 'errors404.ini';
		$configExist = file_exists(_SEO_DIRECTORY . 'errors404.ini');
		if ($configExist) $content = file_get_contents($fName);
	}
	if (!$configExist) {
		return $asArray? array() : '';
	}
	if ($asArray){
		$lines = explode("\n",$content);
		return array_filter($lines);
	} else return $content;
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
	$result = join("\n", $results);
	if (_s_StorageType() == 'mysql') {
		_s_DBmanageData('save','errors404',$result);
	} else {
		file_put_contents(_SEO_DIRECTORY . 'errors404.ini',$result);
	}
}

// information_systems
function _s_getInformationSystem($id)
{
	$urls = array();
	if (_s_StorageType() == 'mysql') {
		$content = _s_DBmanageSpecial('getByParam','is_'.$id);
		foreach($content as $url) {
			$urls[] = json_decode($url['data']);
		}
	} else {
		$dbDir = _SEO_DIRECTORY . 'db' . DIRECTORY_SEPARATOR;
		if (!is_dir($dbDir)) mkdir($dbDir);
		if($handle = opendir($dbDir)){
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
	}
	return $urls;
}

function _s_deleteInformationSystem($id)
{
	if (_s_StorageType() == 'mysql') {
		_s_DBmanageSpecial('deleteByParam','is_'.$id);
	} else {
		if($handle = opendir(_SEO_DIRECTORY . 'db' . DIRECTORY_SEPARATOR)){
			while(false !== ($dir = readdir($handle))){
				if($dir != "." && $dir != ".."){
					unlink(_SEO_DIRECTORY . 'db' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . 'is_' . $id . '.json');
				}
			}
			closedir($handle);
		}
	}
}

function _s_saveInformationSystems($id, $urls)
{
	_s_deleteInformationSystem($id);
	$storageType = _s_StorageType();
	foreach($urls as $url){
		$content = php2js($url);
		if ($storageType == 'mysql') {
			_s_DBmanageSpecial('save','is_'.$id,$url['url'],$content);
		} else {
			$dir = getDatabaseDirectoryForUrl($url['url']);
			file_put_contents($dir . 'is_' . $id . '.json', $content);
		}
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

	if (empty($itemList)) return '';

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
	return '';
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
	return '';
}


// Работа с БД
function _s_DBconnect() {
	if (_s_StorageType() != 'mysql') return false;
	$dbConfig = $GLOBALS['_seo_config']['dataInfo']['mysql_config'];
	$connect = @mysql_connect($dbConfig['host'], $dbConfig['user'], $dbConfig['password']);
	if (!$connect) die('MySQL connection error');
	mysql_select_db($dbConfig['db_name']);
	return $connect;
}
function _s_DBtest($tableName) {
	if (
		!array_key_exists('dataInfo',$GLOBALS['_seo_config']) ||
		!array_key_exists('type',$GLOBALS['_seo_config']['dataInfo']) ||
		$GLOBALS['_seo_config']['dataInfo']['type'] != 'mysql' ||
		!array_key_exists('mysql_config',$GLOBALS['_seo_config']['dataInfo'])
	) return false;
	$dbConfig = $GLOBALS['_seo_config']['dataInfo']['mysql_config'];
	if (!array_key_exists('prefix',$dbConfig)) return false;
	if ($tableName) {
		$query = 'SHOW TABLES LIKE \''.$dbConfig['prefix'].$tableName.'\'';
		$result = _s_DBquery($query);
	}
	if (!$tableName || !$result) $result = _s_DBcreateTables($tableName);
	return $result? true : false;
}
function _s_DBquery($query,$rows = false) {
	$connect = _s_DBconnect();
	$result = mysql_query($query,$connect);
	if (!is_resource($result)) return $result;
	if ($rows) {
		$output = array();
		if (mysql_num_rows($result) != 0 ) {
			while ($row = mysql_fetch_assoc($result)) {
				$output[] = $row;
			}
		}
		return $output;
	}
	return mysql_fetch_array($result,MYSQL_NUM);
}
function _s_DBcreateTables($tableName=false,$tables=false) {
	$dbConfig = $GLOBALS['_seo_config']['dataInfo']['mysql_config'];
	if (!array_key_exists('prefix',$dbConfig)) return false;
	if (!$tables) {
		$tables = array(
			'main' => array(
				'url' => 'text',
				'data' => 'text',
				'additional' => 'text',
			),
			'special' => array(
				'param' => 'varchar(50)',
				'url' => 'varchar(50)',
				'data' => 'text',
			),
			'bigdata' => array(
				'module' => 'varchar(50)',
				'data' => 'mediumtext',
			),
		);
	}
	if (!$tableName) {
		$result = false;
		foreach ($tables as $tableName=>$columns) {
			$dbQuery = 'CREATE TABLE IF NOT EXISTS '.$dbConfig['prefix'].$tableName.' (id int(11) NOT NULL AUTO_INCREMENT';
			foreach($columns as $columnName=>$columnType) {
				$dbQuery.= ',`'.$columnName.'` '.$columnType;
				if (
						strpos('varchar', $columnType) === false &&
						strpos('text', $columnType) === false
				) $dbQuery.= ' DEFAULT NULL';
			}
			$dbQuery.= ',PRIMARY KEY (id));';
			$result = _s_DBquery($dbQuery);
		}
		return $result;
	} else {
		if (!array_key_exists($tableName,$tables)) return false;
		$dbQuery = 'CREATE TABLE IF NOT EXISTS '.$dbConfig['prefix'].$tableName.' (id int(11) NOT NULL AUTO_INCREMENT';
		foreach($tables[$tableName] as $columnName=>$columnType) {
			$dbQuery.= ',`'.$columnName.'` '.$columnType;
			if (
				strpos('varchar', $columnType) === false &&
				strpos('text', $columnType) === false
			) $dbQuery.= ' DEFAULT NULL';
		}
		$dbQuery.= ',PRIMARY KEY (id));';
		return _s_DBquery($dbQuery);
	}
}
// Работа с полями в таблице bigdata
function _s_DBmanageData($action,$field,$data=false) {
	$dataTableName = 'bigdata';
	$dataTable = $GLOBALS['_seo_config']['dataInfo']['mysql_config']['prefix'].$dataTableName;
	if ($action == 'save' && is_string($field) && !empty($field) && is_string($data) && !empty($data)) {
		$query = 'SELECT * FROM `'.$dataTable.'` WHERE `module`=\''.$field.'\'';
		$result = _s_DBquery($query);
		if (empty($result)) {
			$query = 'INSERT INTO `'.$dataTable.'`(`module`,`data`) VALUES (\''.$field.'\',\''.mysql_real_escape_string($data).'\')';
		} else $query = 'UPDATE `'.$dataTable.'` SET `data`=\''.mysql_real_escape_string($data).'\' WHERE `module`=\''.$field.'\'';
		$result = _s_DBquery($query);
		return $result;
	}
	if ($action == 'get' && is_string($field) && !empty($field)) {
		$query = 'SELECT `data` FROM `'.$dataTable.'` WHERE `module`=\''.$field.'\' LIMIT 1';
		$result = _s_DBquery($query);
		if (!empty($result) && is_array($result)) return $result[0];
		return false;
	}
}
// Работа с полями в таблице special
function _s_DBmanageSpecial($action,$param,$url=false,$data=false) {
	if (!is_string($param) || empty($param)) return false;
	if (in_array($action,array('save','get')) && empty($url)) return false;
	$dataTableName = 'special';
	$dataTable = $GLOBALS['_seo_config']['dataInfo']['mysql_config']['prefix'].$dataTableName;
	if (!is_string($param) || empty($param)) return false;
	if ($url && is_string($url)) $url = md5($url);
	if ($action == 'save') {
		$query = 'SELECT * FROM `'.$dataTable.'` WHERE `param`=\''.$param.'\' AND `url`=\''.$url.'\'';
		$result = _s_DBquery($query);
		if (empty($result)) {
			$query = 'INSERT INTO `'.$dataTable.'`(`param`,`url`,`data`) VALUES (\''.$param.'\',\''.$url.'\',\''.mysql_real_escape_string($data).'\')';
		} else $query = 'UPDATE `'.$dataTable.'` SET `data`=\''.mysql_real_escape_string($data).'\' WHERE `param`=\''.$param.'\' AND `url`=\''.$url.'\'';
		$result = _s_DBquery($query);
		return $result;
	}
	if ($action == 'get') {
		$query = 'SELECT `data` FROM `'.$dataTable.'` WHERE `param`=\''.$param.'\' AND `url`=\''.$url.'\' LIMIT 1';
		$result = _s_DBquery($query);
		if (!empty($result) && is_array($result)) return $result[0];
		return false;
	}
	if ($action == 'getByParam') {
		$query = 'SELECT `url`,`data` FROM `'.$dataTable.'` WHERE `param`=\''.$param.'\'';
		$result = _s_DBquery($query,true);
		return $result;
	}
	if ($action == 'deleteByParam') {
		$query = 'DELETE FROM `'.$dataTable.'` WHERE `param`=\''.$param.'\'';
		return _s_DBquery($query);
	}
}
// Определение типа хранилища (file/mysql)
function _s_StorageType() {
	$storage = 'file';
	if (
		!array_key_exists('dataInfo',$GLOBALS['_seo_config']) ||
		!array_key_exists('type',$GLOBALS['_seo_config']['dataInfo']) ||
		($GLOBALS['_seo_config']['dataInfo']['type'] != 'mysql') ||
		!array_key_exists('mysql_config',$GLOBALS['_seo_config']['dataInfo'])
	) return $storage;
	return $GLOBALS['_seo_config']['dataInfo']['type'];
}