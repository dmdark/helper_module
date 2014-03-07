<?php

// Формирование формы
// используется в _seo/index.php
function _s_makeForm() {

	$e = $GLOBALS['_seo_config']['encoding'];
	$moduleUrl = '/_seo/modules/forms/';

	foreach ($GLOBALS['_seo_config']['adminConfig']['forms'] as $form) {
		if (!array_key_exists('tag',$form)) continue;

		$formHTML = '<form id="'.$form['tag'].'" class = "_seo_form" action="#">';
		if (array_key_exists('header',$form)) $formHTML.= '<h2 class="header">'.$form['header'].'</h2>';
		if (array_key_exists('errors',$form)) $formHTML.= '<p class="error" data-form-validation-error>'.$form['errors'].'</p>';
		if (array_key_exists('success',$form)) $formHTML.= '<p class="success" data-form-success>'.$form['success'].'</p>';
		$formHTML.= '<fieldset>';
		foreach ($form['fields'] as $field) {
			if (!array_key_exists('name',$field)) continue;
			// Label
			if (array_key_exists('label',$field)) {
				$formHTML.= '<label for="'.$form['tag'].'_'.$field['name'].'">'.$field['label'];
				if (array_key_exists('required',$field) && $field['required']) $formHTML.= ' <span class="required">*</span>';
				$formHTML.= '</label>';
			}
			// Input||Textarea
			if ($field['tag'] == 'textarea') {$formHTML.= '<textarea';}
			else {$formHTML.= '<input';}
			$formHTML.= ' id="'.$form['tag'].'_'.$field['name'].'" name="'.$field['name'].'" type="';
			if (array_key_exists('type',$field)) {$formHTML.=$field['type'];} else {$formHTML.='text';}
			$formHTML.= '" ';
			if (array_key_exists('placeholder',$field)) $formHTML.= 'placeholder="'.$field['placeholder'].'" ';
			if (array_key_exists('validator',$field)) $formHTML.= 'data-validate="true" data-validation-type="'.$field['validator'].'"';
			if ($field['tag'] == 'textarea') {$formHTML.= '></textarea>';}
			else {$formHTML.= ' />';}
			// P.error
			if (array_key_exists('error',$field)) {
				$formHTML.= '<p class="error" data-validation-error="'.$field['name'].'">'.$field['error'].'</p>';
			}
		}
		// Тег формы - обязателен для окончательной проверки формы перед отправкой
		$formHTML.= '<input type="hidden" name="form-tag" value="'.$form['tag'].'"/>';
		// Submit
		$formHTML.= '</fieldset><button type="submit">';
		if (array_key_exists('submit',$form)) {$formHTML.= $form['submit'];}
		else {$formHTML.= 'Отправить';}
		$formHTML.= '</button></form>';
		// Добавление стилей
		if (array_key_exists('css',$form) && $form['css'] && file_exists(dirname(__FILE__).'/styles/'.$form['css']))
			$formHTML.= '<style>'.file_get_contents(dirname(__FILE__).'/styles/'.$form['css']).'</style>';
		// Замена кодировки
		if (strtolower($e)!='utf-8') $formHTML = mb_convert_encoding($formHTML,$e,'utf-8');
		// Подключение JavaScript
		$formHTML.= '<script src="'.$moduleUrl.'js/form_validator.js"></script><script>_seoInitializeJQuery(function($) { $("#' . $form['tag'] . '").formValidator(); }); </script>';

		// Заменяем метку на форму
		$GLOBALS['_seo_content'] = preg_replace('/<!--\$\$'.$form['tag'].'-->/i',$formHTML,$GLOBALS['_seo_content'],(-1),$count);

	}

}