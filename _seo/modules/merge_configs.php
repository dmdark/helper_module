<?php
include_once dirname(__FILE__) . '/../index.php';

$configFile = dirname(__FILE__) . '/../config.ini';
$newConfigFile = dirname(__FILE__) . '/../config_new.ini';
if (!file_exists($newConfigFile)) exit();

$old_config = config2file($configFile);

$isMySqlStorage = false;
if (_s_StorageType() == 'mysql') {
	$isMySqlStorage = true;
	$GLOBALS['_seo_config']['dataInfo']['type'] = 'file';
}
$upd_config = config2file($newConfigFile);
if ($isMySqlStorage) {
	$GLOBALS['_seo_config']['dataInfo']['type'] = 'mysql';
}

$new_config = array_replace_recursive($old_config, $upd_config);

$new_config_items = array();
while($item = array_shift($new_config)) {
	if(!empty($item['newUrl']) && !empty($new_config[$item['newUrl']])) {
		unset($new_config[$item['newUrl']]['url']);
		$item = array_merge($item, $new_config[$item['newUrl']]);
		unset($new_config[$item['newUrl']]);
	}
	$new_config_items[] = item2config($item);
}

_s_saveConfig2file(__DIR__.'/new_config.ini', implode("===\n", $new_config_items));
exit();
