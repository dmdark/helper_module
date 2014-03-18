<?php
$formId = $information_system_config['id'];
$html = '<form class="_seo_is_reply" id="_seo_is_'.$formId.'_reply" action="">';
$html.= '<h2 class="header">Добавьте ваш отзыв</h2>';
$html.= '<p class="success" data-form-success>Спасибо! Ваш отзыв принят</p>';
$html.= '<p class="error" data-form-validation-error>Пожалуйста, проверьте еще раз введенные данные.</p>';
$html.= '<fieldset>';
// Создание основных полей
foreach ($information_system_config['fields'] as $field) {
	$required = array_key_exists('required',$field)? ' <span class="required">*</span>' : '';
	// Label
	if (array_key_exists('label',$field)) $html.= '<label for="_seo_is_'.$formId.'__'.$field['title'].'">'.$field['label'].$required.'</label>';
	// Placeholder
	$placeholder = array_key_exists('placeholder',$field)? $field['placeholder'] : $field['title'];
	// Проверка на JS
	$validator = array_key_exists('validator',$field)? 'data-validate="true" data-validation-type="'.$field['validator'].'"' : '';
	// Тип
	if (array_key_exists('type',$field) && $field['type'] == 'textarea') {
		$html.= '<textarea id="_seo_is_'.$formId.'__'.$field['title'].'" name="'.$field['id'].'" placeholder="'.$placeholder.'" '.$validator.' rows=5></textarea>';
	} else $html.= '<input id="_seo_is_'.$formId.'__'.$field['title'].'" name="'.$field['id'].'" placeholder="'.$placeholder.'" '.$validator.' />';
	if (array_key_exists('error',$field)) $html.= '<p class="error" data-validation-error="'.$field['id'].'">'.$field['error'].'</p>';
}
// 3 обязательных поля: имя модуля; id инфосистемы; url, указанный в админке
$html.= '<input type="hidden" name="_seo_module" value="infosystems"/>';
$html.= '<input type="hidden" name="form-tag" value="'.$formId.'"/>';
$html.= '<input type="hidden" name="reply-url" value="'.$properties['url'][0].'"/>';
$html.= '<button type="submit">Отправить</button></fieldset></form>';
$html.= '<script src="/_seo/frontend/js/form_validator.js"></script>';
$html.= '<script>_seoInitializeJQuery(function($) { $("#_seo_is_'.$formId.'_reply").formValidator(); });</script>';

return $html;