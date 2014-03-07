<?php

$validationTypes = '';
$validationContent = '';
$validationValue = 0;

// $_REQUEST['validationType'] == '"min=10,max=50,email"' (параметры проверки через запятую, может приходить с кавычками)
if (isset($_REQUEST['validationType'])) $validationTypes = trim($_REQUEST['validationType']);
if (isset($_REQUEST['content'])) $validationContent = trim($_REQUEST['content']);

// $_REQUEST['validateField'] приходит от JS при проверке отдельных полей, которому посылается 1 или 0 (хорошо/плохо)
if (isset($_REQUEST['validateField'])) echo ValidateField($validationContent, $validationTypes)? 1:0;

// Проверка содержимого (строка) на соответствие списку проверок (строка)
// зависит от validateContent()
function ValidateField($content,$validationTypes) {
	// Преобразование $validationType в массив
	$validationTypes = strtr(trim($validationTypes),array('"'=>'','\''=>'',' '=>''));
	$validationTypes = explode(',',$validationTypes);
	// Если 1 из проверок не пройдет, $validated == 0, проверка останавливается
	$validated = true;
	foreach ($validationTypes as $validationType) {
		if (!$validated) break;
		// Разбивка для случаев типа min=10 или email (равно чему-то или нет)
		$validationType = explode('=',$validationType);
		$typeVal = 0;
		if (array_key_exists(1,$validationType)) $typeVal = $validationType[1];
		$validated = validateContent($content,$validationType[0],$typeVal);
	}
	return $validated;
}

// Проверка содержимого на соответствие одному значению
function validateContent($string,$type,$typeVal=0) {
	switch ($type) {
		case 'email':
			$pattern = '/\A[-_.a-z0-9]+@[-.a-z0-9]+?\.[-a-z]{2,6}\Z/i';
			break;
		case 'phone':
			$pattern = '/\A[-+()0-9]{5,30}\Z/';
			break;
		case 'notempty':
			$pattern = '/\A.+\Z/';
			break;
		case 'min':
			$pattern = '/\A.{'.$typeVal.',}\Z/us';
			break;
		case 'max':
			$pattern = '/\A.{0,'.$typeVal.'}\Z/us';
			break;
	}
	if (!isset($pattern{0})) return false;
	return preg_match($pattern,trim($string));
}