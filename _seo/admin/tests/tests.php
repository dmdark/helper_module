<?php

function bool_to_label_class($bool) {
	return $bool ? 'label-success' : 'label-danger';
}

function test_mb_functions() {
	$result = (function_exists('mb_substr') && function_exists('mb_strlen') && mb_strlen('12345') == 5 && mb_strpos('12345', '234') === 1);
	return $result;
}

function test_php2js() {
	return php2js(array('test' => 1)) == '{ "test": "1" }';
}

function test_iconv()
{
   return function_exists('iconv');
}

function test_preg_match()
{
   return function_exists('preg_match');
}

// Проверка прав доступа (все файлы в /_seo/ должны иметь того же владельца, что и создает сейчас файл)
function test_permissions()
{
	if (file_put_contents('test.tmp','1') != 1) return false;
	$owner = fileowner('test.tmp');
	unlink('test.tmp');
	$fileList = test_permissions__getFiles(_SEO_DIRECTORY);
	if (!is_array($fileList)) return false;
	foreach ($fileList as $path => $value) {
		// Если не тот владелец и не удалось его поменять
		if (fileowner($path) != $owner & !chown($path,$owner)) return false;
		// Устанавливаем права 776 (если последняя 4, то м.б. проблемы с FTP)
		if (!chmod($path,0776)) {
			// Если не удалось, проверяем, какие сейчас права
			if ( (int)substr( strval( decoct(fileperms($path) & 0777) ) ,0,1) < 7 ) return false;
		}
	}
	return true;
}
function test_permissions__getFiles($path) {
	if ( (strlen($path)>0) && (substr($path,-1)!='/' && substr($path,-1)!='\\') ) $path.= '/';
	$fileList = array();
	if ($handle = opendir($path)) {
		while ( false !== ($entry=readdir($handle)) ) {
			// Выкидываем '.', '..' и '.htaccess' (на него обычно нельзя менять права)
			if ($entry!='.' && $entry!='..' & $entry!='.htaccess') {
				$fileList[$path.$entry] = '';
				if (filetype($path.$entry) == 'dir') $fileList[$path.$entry] = 'dir';
			}
		}
		closedir($handle);
		$thisList = $fileList;
		foreach ($thisList as $key => $value) {
			if ($value == 'dir') {
				$childArray = test_permissions__getFiles($key);
				if (!is_array($childArray)) return false;
				$fileList = array_merge($fileList,$childArray);
			}
		}
		return $fileList;
	} else return false;
}

// Проверка основного модуля
function test_index() {
	$data = config2file(_SEO_DIRECTORY.'/config.ini');
	$config = $GLOBALS['_seo_config'];
	$e = $config['encoding'];
	foreach ($data as $link) {
		// Проверка работы перенаправлений
		if ( array_key_exists('newUrl',$link) && $link['newUrl'] != $link['url']) {
			$redirects = array(
				array(
					'source' => $link['url'],
					'dest' => $link['newUrl']
				)
			);
			if ( !test_redirects($redirects) ) return false;
			echo 'RedirectsOK ';
		}
		$pageContent = file_get_contents('http://' . $_SERVER['SERVER_NAME'] . $link['url']);
		// Контент некоторых следующих проверок должен четко соответствовать указанному в 'index.php', т.к. regexp здесь не используется
		// Метаданные
		if (array_key_exists('module_meta_enabled',$config) && $config['module_meta_enabled']) {
			// Title
			if (array_key_exists('title',$link) && !empty($link['title'])) {
				if (!strpos($pageContent,'<title>'.$link['title'].'</title>')) {
					echo '!Title ';
					return false;
				}
			}
			// Description
			if (array_key_exists('description',$link) && !empty($link['description'])) {
				if (!strpos($pageContent,'<meta name="description" content="'.$link['description'].'"')) {
					echo '!Description ';
					return false;
				}
			}
			// Keywords
			if (array_key_exists('keywords',$link) && !empty($link['keywords'])) {
				if (!strpos($pageContent,'<meta name="keywords" content="'.$link['keywords'].'"')) {
					echo '!Keywords ';
					return false;
				}
			}
			echo 'MetaOK ';
		}
		if (array_key_exists('module_headers_enabled',$config) && $config['module_headers_enabled']) {
			// H1
			if (array_key_exists('h1',$link) && !empty($link['h1'])) {
				preg_match('/<h1[^>]*>(.*)<\/h1>/U',$pageContent,$matches);
				if (!array_key_exists(1,$matches) || $matches[1]!=$link['h1']) {
					echo '!H1 ';
					return false;
				}
			}
			echo 'H1OK ';
		}
		// additionalTags (только начинающиеся с 't_')
		if(array_key_exists('module_label_replacement',$config) && $config['module_label_replacement']) {
			foreach ($GLOBALS['_seo_config']['adminConfig']['additionalTags'] as $additionalTag) {
				if (strpos($additionalTag,'t_')!==false) {
					$tagValue = trim(getSpecialProperty($link['url'],$additionalTag));
					if (empty($tagValue)) continue;
					if (strtolower($e)!='utf-8') $tagValue = mb_convert_encoding($tagValue,$e,'utf-8');
					if (!strpos($pageContent,$tagValue)) {
						echo '!AddTags ';
						return false;
					}
				}
			}
			echo 'AddTagsOK ';
		}
	}
	return true;
}

// Проверка БД
function test_db() {
	return _s_DBtest(false);
}

// Проверка модуля Redirects 301 (также используется в проверке основного модуля)
function test_redirects($redirects=false) {
	if (!array_key_exists('module_urls_enabled',$GLOBALS['_seo_config']) || !$GLOBALS['_seo_config']['module_urls_enabled']) return false;
	if ( !in_array('mod_alias',apache_get_modules()) ) return false;
	if (!$redirects) $redirects = json_decode(file_get_contents(_SEO_DIRECTORY . 'redirects.ini'), true);
	if (empty($redirects)) return true;
	foreach ($redirects as $redirect) {
		$headers = get_headers('http://' . $_SERVER['SERVER_NAME'] . $redirect['source'], 1);
		if (!$headers) return false;
		// Поиск в заголовках ответа 301
		$headers_301 = false;
		foreach ($headers as $value) {
			if (preg_match('/^(?:.* )?301(?: .*)?$/',$value)) {
				$headers_301 = true;
				break;
			}
		}
		if (!$headers_301) return false;
		if (!array_key_exists('Location',$headers)) return false;
		// 'Location' может быть как массивом, так и строковым значением
		if (is_array($headers['Location'])) {
			$match = false;
			foreach ($headers['Location'] as $pageLocation) {
				$pageLocation = parse_url($pageLocation,PHP_URL_PATH);
				if ($pageLocation == $redirect['dest']) {
					$match = true;
					break;
				}
			}
			if (!$match) return false;
		} else {
			$pageLocation = parse_url($headers['Location'],PHP_URL_PATH);
			if ($pageLocation != $redirect['dest']) return false;
		}
	}
	return true;
}

// Проверка вывода страницы 404
function test_pageNotFound() {
	if (!array_key_exists('module_urls_enabled',$GLOBALS['_seo_config']) || !$GLOBALS['_seo_config']['module_urls_enabled']) return false;
	$errors404 = _s_getErrors404(true);
	foreach ($errors404 as $error) {
		$headers = get_headers('http://'.$_SERVER['SERVER_NAME'].$error);
		if (
			!in_array('Status: 404 Not Found',$headers) ||
			!( in_array('HTTP/1.0 404 Not Found',$headers) || in_array('HTTP/1.1 404 Not Found',$headers) )
		) return false;
	}
	return true;
}


// Требуется для проверки работы основного модуля
include_once(_SEO_DIRECTORY.'functions.php');
$GLOBALS['_seo_config'] = include(_SEO_DIRECTORY.'config.php');