<?php
// Проверка всех полей формы по параметрам из /_seo/config.php
// Зависит от:
// - /_seo/config.php (2 уровня выше)
// - /_seo/modules/forms/inputvalidator.php (вместе с этим файлом)

if (isset($_REQUEST['form-tag'])) {$formTag = $_REQUEST['form-tag'];}
else die();

$config = include('../../config.php');
if (!array_key_exists('forms',$config['adminConfig'])) die('No forms in config file');

// Ищем tag текущей формы
$form = false;
foreach ($config['adminConfig']['forms'] as $value) {
	if (array_key_exists('tag',$value)) {
		if ($value['tag'] == $formTag) {
			$form = $value;
			break;
		}
	};
}

if (!$form) die('No such form');

// Проверяем все значения формы
$valid = 1;
$json = '';
include_once('inputvalidator.php');
foreach ($form['fields'] as $field) {
	if (array_key_exists('validator',$field)) {
		if (strlen($field['validator'])>0) {
			if ( !isset( $_REQUEST[($field['name'])] ) ) die('Corrupt request');
			if ( !ValidateField( $_REQUEST[($field['name'])],$field['validator'] ) ) {
				$json.= ',"'.$field['name'].'": 0';
				$valid = 0;
			}
		}
	}
}

$json = '{"formvalid":'.($valid).$json.'}';
echo $json;

// Отправляем письмо
if (1 == $valid && array_key_exists('emailSettings',$form)) {
	// Тело письма
	$mailBody = '';
	// Вводный текст
	if (array_key_exists('intro',$form['emailSettings'])) {
		$mailBody.= '<p>'.$form['emailSettings']['intro'].'</p>';
	} else $mailBody.= '<p>С сайта было отправлено письмо со следующими данными:</p>';
	// Вывод данных каждого поля
	foreach ($form['fields'] as $field) {
		if ( isset( $_REQUEST[($field['name'])] ) ) {
			$mailBody.= '<p><strong>';
			$mailBody.=  array_key_exists('letter_name',$field)? $field['letter_name']:$field['name'];
			$mailBody.= ':</strong> ';
			$mailBody.=  (@$field['tag']=='textarea')? '<br /><pre>'.$_REQUEST[($field['name'])].'</pre>' : $_REQUEST[($field['name'])];
			$mailBody.= '</p>';
		}
	}
	// Тело письма в utf-8
	if (strtolower($config['encoding'])!='utf-8') $formHTML = mb_convert_encoding($formHTML,'utf-8',$config['encoding']);

	// Имя отправителя
	$mailFrom =  array_key_exists('from',$form['emailSettings'])? $form['emailSettings']['from']:'mail@example.com';
	// Тема письма
	$mailSubj =  array_key_exists('subject',$form['emailSettings'])? $form['emailSettings']['subject']:'Письмо с сайта';
	// Получатели
	if (!array_key_exists('to',$form['emailSettings'])) die('No email addresses to send to');
	$mailRecipients = $form['emailSettings']['to'];
	$mailRecipients = explode(',',$mailRecipients);
	$nl = "\r\n";
	// Сохранение адресов вида "Имя <mail@x.y>" в требуемый почтовиками формат
	function addressBase64($value) {return '=?UTF-8?B?'.base64_encode($value[1]).'?= <'.$value[2].'>';}
	if ( preg_match('/^[^<]+<[^>]+>/i', $mailFrom) ) {
		$mailFrom = preg_replace_callback('/^([^<]*)<([^>]*)>/i', 'addressBase64', $mailFrom);
	}
	$mailSubj = '=?UTF-8?B?'.base64_encode($mailSubj).'?=';
	$mailHeader = 'From: '.$mailFrom.$nl.
		'Reply-To: '.$mailFrom.$nl.
		'X-Mailer: PHP/'.phpversion().$nl.
		'X-Priority: 3 (Normal)'.$nl.
		'Mime-Version: 1.0'.$nl.
		'Content-Type: text/html; charset=utf-8'.$nl.
		'Content-Transfer-Encoding: 8bit'.$nl.$nl;
	
	foreach ($mailRecipients as $mailRecipient) {
		mail(
			trim($mailRecipient),
			$mailSubj,
			$mailBody,
			$mailHeader
		);
	}
	
	// Сохраняем письмо в html
	$filename = date('Y-m-d--U').'.htm';
	if (!is_dir('letters-html')) mkdir('letters-html') or die('Нет доступа к записи на диск');
	// $formTag должен быть получен в formvalidator.php
	if (!is_dir('letters-html/'.$formTag)) mkdir('letters-html/'.$formTag) or die('Нет доступа к записи на диск');
	file_put_contents('letters-html/'.$formTag.'/'.$filename,'<!doctype html><html lang="ru"><head><meta charset="UTF-8"><title>Document</title></head><body>'.$mailBody.'</body></html>');

}