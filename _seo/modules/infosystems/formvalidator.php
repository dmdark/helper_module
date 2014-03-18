<?php
// Проверка всех полей формы инфосистем по параметрам из /_seo/config.php
// Должен вызываться из /_seo/modules/formvalidator.php
// Зависит от (должны быть получены заранее в /_seo/modules/formvalidator.php):
// - /_seo/config.php
// - /_seo/modules/inputvalidator.php

if (isset($_REQUEST['form-tag']) && isset($_REQUEST['reply-url'])) {
	$formTag = $_REQUEST['form-tag'];
	$replyUrl = $_REQUEST['reply-url'];
} else die();

$seoDir = dirname(__FILE__).'/../../';

if (!array_key_exists('information_systems',$config['adminConfig'])) die('No Information Systems in config file');

// Ищем tag текущей системы
$form = false;
foreach ($config['adminConfig']['information_systems'] as $value) {
	if ( array_key_exists('id',$value) && ($value['id'] == $formTag) ) {
		$form = $value;
		break;
	};
}

if (!$form) die('No such form');

// Проверяем все значения формы
$valid = 1;
$json = '';
foreach ($form['fields'] as $field) {
	if (array_key_exists('validator',$field)) {
		if (strlen($field['validator'])>0) {
			if ( !isset( $_REQUEST[($field['id'])] ) ) die('Corrupt request');
			if ( !ValidateField( $_REQUEST[($field['id'])],$field['validator'] ) ) {
				$json.= ',"'.$field['id'].'": 0';
				$valid = 0;
			}
		}
	}
}

$json = '{"formvalid":'.($valid).$json.'}';
echo $json;

// Добавляем в админку
if (1 == $valid) {
	$filename = $seoDir.'db/'.rawurlencode($replyUrl).'/is_'.$formTag.'.json';
	if (file_exists($filename)) {
		$infoItem = array();
		foreach ($form['fields'] as $field) {
			$infoItem[($field['id'])] = $_REQUEST[($field['id'])];
			if (strtolower($config['encoding'])!='utf-8') $infoItem[($field['id'])] = mb_convert_encoding($infoItem[($field['id'])],'utf-8',$config['encoding']);
		}
		$infoItem['visibility'] = false;
		$info = file_get_contents($filename);
		$info = json_decode($info,true);
		$info['items'][] = $infoItem;
		$info = json_encode($info);
		file_put_contents($filename,$info);
	}
}