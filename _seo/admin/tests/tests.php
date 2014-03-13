<?php

function bool_to_label_class($bool)
{
   return $bool ? 'label-success' : 'label-danger';
}

function test_mb_functions()
{
   try{
      $result = (function_exists('mb_substr') && function_exists('mb_strlen') && mb_strlen('12345') == 5 && mb_strpos('12345', '234') === 1);
      return $result;
   } catch (Exception $e){
      return false;
   }

}

function test_php2js()
{
   try{
      return php2js(array('test' => 1)) == '{ "test": "1" }';
   } catch (Exception $e){
      return false;
   }
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