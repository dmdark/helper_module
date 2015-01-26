<?php
header('Content-Type: text/html; charset=utf-8');
require_once dirname(__FILE__) . '/defines.php';
require_once _SEO_DIRECTORY . 'functions.php';

$GLOBALS['_seo_config'] = include dirname(__FILE__) . '/../config.php';
if (!isset($HTTP_RAW_POST_DATA)) $HTTP_RAW_POST_DATA = '[]';
if (!isset($_GET['module'])) $_GET['module'] = false;
if (!isset($_GET['action'])) $_GET['action'] = false;

if($_GET['module'] == 'information_systems'){
	if($_GET['action'] == 'get' && $_GET['id']){
		echo php2js(_s_getInformationSystem($_GET['id']));
		exit;
	}
	if($_GET['action'] == 'saveAll' && $_GET['id']){
		_s_saveInformationSystems($_GET['id'], json_decode(trim($HTTP_RAW_POST_DATA), true));
		exit;
	}
}

if($_GET['module'] == 'redirects'){
	if($_GET['action'] == 'add'){
		$postData = trim($HTTP_RAW_POST_DATA);
		if(!empty($postData)){
			_s_addRedirects($postData);
		}
		exit;
	}
	if($_GET['action'] == 'get'){
		echo php2js(_s_getRedirects());
		exit;
	}
	if($_GET['action'] == 'delete'){
		$postData = json_decode($HTTP_RAW_POST_DATA, true);
		_s_deleteRedirect($postData['source'], $postData['dest']);
		exit;
	}
	exit;
}

if($_GET['module'] == 'error404'){
	if($_GET['action'] == 'get'){
		echo _s_getErrors404();
		exit;
	}
	if($_GET['action'] == 'save'){
		_s_saveErrors404($HTTP_RAW_POST_DATA);
		exit;
	}
	exit;
}

if($_GET['action'] == 'get_items'){
	$config = config2file(_SEO_DIRECTORY . 'config.ini', false);
	$rememberCache = getRememberCache();
	foreach($config as $key=>$info){
		if(array_key_exists('newUrl',$info) && isset($rememberCache[$info['newUrl']])){
			$info['rememberCache'] = nl2br(print_r($rememberCache[$info['newUrl']], true));
			$config[$key]['rememberCache'] = $info['rememberCache'];
		}
		$specialData = addSpecialProperties($info);
		$config[$key] = $specialData;
	}
	echo php2js(array_values($config));
	return;
}

if ($_GET['module'] == 'breadcrumbs') {
	$crumbs_dir = _SEO_DIRECTORY.'modules/breadcrumbs/';
	if ($_GET['action'] == 'get') {
		if (_s_StorageType() == 'mysql') {
			$crumbs_config = _s_DBmanageData('get','breadcrumbs');
			if (!$crumbs_config) $crumbs_config = '[]';
		} else {
			$crumbs_config = file_get_contents($crumbs_dir.'breadcrumbs.json');
		}
		echo $crumbs_config;
		exit;
	}
	if ($_GET['action'] == 'save') {
		$postData = trim($HTTP_RAW_POST_DATA);
		if (!empty($postData)) {
			if (_s_StorageType() == 'mysql') {
				_s_DBmanageData('save','breadcrumbs',$postData);
			} else {
				file_put_contents($crumbs_dir.'breadcrumbs.json',$postData);
			}
		}
		exit;
	}
	if ($_GET['action'] == 'find') {
		// post - относительный url (обязательно начинающийся с '/')
		$url = trim($HTTP_RAW_POST_DATA);
		$output = array();
		// Начинаем искать, только если сервер возвращает ответ 200
		$pageRequest = get_headers('http://'.$_SERVER['SERVER_NAME'].$url, 1);
		if (strpos($pageRequest[0],'200') !== false) {
			$pageContent = file_get_contents('http://'.$_SERVER['SERVER_NAME'].$url);
			// Очищаем ссылки сначала от 'http://site.dom', затем от 'site.dom'
			$pageContent = str_ireplace('http://'.$_SERVER['SERVER_NAME'],'',$pageContent);
			$pageContent = str_ireplace($_SERVER['SERVER_NAME'],'',$pageContent);
			$pattern = '/<a (?:[-\\ a-z0-9=\'"\/] )*href *= *(?:"|\')('.str_ireplace('/','\/',$url).'[-_a-z0-9]+\/)+/i';
			preg_match_all($pattern,$pageContent,$matches);
			if (array_key_exists(1,$matches) && count($matches[1]) > 0) {
				// Удаляем дубликаты
				$matches = array_unique($matches[1]);
				foreach ($matches as $childUrl) {
					$pageRequest = get_headers('http://'.$_SERVER['SERVER_NAME'].$childUrl, 1);
					if (strpos($pageRequest[0],'200') !== false) {
						$pageContent = file_get_contents('http://'.$_SERVER['SERVER_NAME'].$childUrl);
						// Ищем заголовок в тексте
						$pageTitle = '';
						preg_match('/<h1(?: [^>]+)?>(.+?)<\/h1(?: [^>]+)?>/i',$pageContent,$titleMatches);
						if (array_key_exists(1,$titleMatches)) $pageTitle = strip_tags($titleMatches[1]);
						if (strlen($pageTitle) == 0) {
							preg_match('/<title(?: [^>]+)?>(.+?)<\/title(?: [^>]+)?>/i',$GLOBALS['_seo_content'],$titleMatches);
							if (array_key_exists(1,$titleMatches)) $pageTitle = strip_tags($titleMatches[1]);
						}
						if (strtolower($GLOBALS['_seo_config']['encoding'])!='utf-8') $pageTitle = mb_convert_encoding($pageTitle,'utf-8',$GLOBALS['_seo_config']['encoding']);
						$output[]= array('url'=>$childUrl,'title'=>$pageTitle,'items'=>array());
					}
				}
			}
		}
		$output = json_encode($output);
		echo $output;
		exit;
	}
}

$postData = json_decode($HTTP_RAW_POST_DATA, true);
if(!empty($postData)){
	$configData = '';
	$i = 0;
	foreach($postData as $data){
		$data = (array) $data;
		if(empty($data['url'])) continue;
		// сохраняем особые типы данных
		$data = saveSpecialData($data);
		if($i++ > 0){
			$configData .= "===\n";
		}
		$configData .= item2config($data);
	}
	if(!empty($configData)){
		_s_saveConfig2file(_SEO_DIRECTORY . 'config.ini', $configData);
	}
}