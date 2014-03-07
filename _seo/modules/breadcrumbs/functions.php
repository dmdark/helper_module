<?php
// Формирование хлебных крошек
// Используется в _seo/index.php
// Зависит от _seo/index.php getCurrentUrl()

function _s_makeCrumbs() {
	// Параметры
	$config = $GLOBALS['_seo_config']['adminConfig']['breadcrumbs'];
	$e = $GLOBALS['_seo_config']['encoding'];
	// Текщий URL
	$url = trim(getCurrentUrl());
	// Массив введенных в админке значений в 2м массив
	$breadcrumbRelations = file_get_contents(dirname(__FILE__).'/breadcrumbs.json');
	$breadcrumbRelations = json_decode($breadcrumbRelations, true);
	$breadcrumbRelations = _s_CrumbsGetRelations($breadcrumbRelations,'');
	// Начальные значения
	$crumbString = '';

	// Если эта страница есть в базе
	if (array_key_exists($url,$breadcrumbRelations)) {
		$crumbString = _s_getCrumbs($url,$breadcrumbRelations);
	}
	// Если страницы нет в базе, то обрабатываем только, если на сайте ЧПУ
	elseif ($config['friendly_urls']) {
		$urlExist = false;
		// Удаляем параметры страницы (?page=3&x=10)
		$url = preg_replace('/\?.*$/','',$url);
		if (array_key_exists($url,$breadcrumbRelations)) $urlExist = true;
		// Удаляем "/index.php", "/index.html" и т.п.
		if (!$urlExist) {
			$url = preg_replace('/\/(index\.[a-z0-9]+)?$/i','',$url).$config['page_end'];
			if (array_key_exists($url,$breadcrumbRelations)) $urlExist = true;
		}
		// Если после замен URL нашелся
		if ($urlExist) {
			$crumbString = _s_getCrumbs($url,$breadcrumbRelations);
		}
		// Иначе ищем заголовок в тексте
		else {
			$pageTitle = '';
			// Ищем в h1
			preg_match('/<h1(?: [^>]+)?>(.+?)<\/h1(?: [^>]+)?>/i',$GLOBALS['_seo_content'],$matches);
			if (array_key_exists(1,$matches)) $pageTitle = strip_tags($matches[1]);
			if (strlen($pageTitle) == 0) {
				preg_match('/<title(?: [^>]+)?>(.+?)<\/title(?: [^>]+)?>/i',$GLOBALS['_seo_content'],$matches);
				if (array_key_exists(1,$matches)) $pageTitle = strip_tags($matches[1]);
			}
			if (strtolower($e)!='utf-8') $pageTitle = mb_convert_encoding($pageTitle,'utf-8',$e);
			$crumbString = $pageTitle;
			// Ищем ближайшего предка
			// Отсекаем последний алиас
			$url = preg_replace('/\/[^\/]*\/?$/','',$url).$config['page_end'];
			$urlArray = explode('/',$url);
			// Очищаем массив алиасов от пустых элементов
			if ( strlen( $urlArray[count($urlArray)-1] ) == 0 ) unset($urlArray[count($urlArray)-1]);
			if (count($urlArray) > 0) {if (strlen($urlArray[0]) == 0) unset($urlArray[0]);}
			// Если остались алиасы, перебираем от последнего к 1-му, пока не найдем запись в базе
			$aliasCount = count($urlArray);
			while ($aliasCount > 0) {
				$urlString = '/'.implode('/',$urlArray).$config['page_end'];
				if (array_key_exists($urlString,$breadcrumbRelations)) {
					$crumbString = _s_getCrumbs($urlString,$breadcrumbRelations,true).$crumbString;
					break;
				}
				$aliasCount--;
			}
		}
	}
	// Вывод получившегося значения
	$crumbString = '<span class="_s_crumbs">'.$crumbString.'</span>';
	if (strtolower($e)!='utf-8') $crumbString = mb_convert_encoding($crumbString,$e,'utf-8');
	$GLOBALS['_seo_content'] = preg_replace('/<!--\$\$'.$config['tag'].'-->/i',$crumbString,$GLOBALS['_seo_content']);

}

// Получение строки с хлебными крошками, $lastLink - последний элемент ссылкой как и остальные
function _s_getCrumbs($url,$urlArray,$lastLink=false) {
	$config = $GLOBALS['_seo_config']['adminConfig']['breadcrumbs'];
	$crumbString = '';
	if (array_key_exists($url,$urlArray)) {
		$parent = $urlArray[$url]['parent'];
		if (!$lastLink) {$crumbString = $urlArray[$url]['title'];}
		else $crumbString = '<a href="'.$url.'">'.$urlArray[$url]['title'].'</a>'.$config['divider'];
		// Если есть родительские элементы
		while (strlen($parent) > 0) {
			if (array_key_exists($parent,$urlArray)) {
				$crumbString = '<a href="'.$parent.'">'.$urlArray[$parent]['title'].'</a>'.$config['divider'].$crumbString;
				$parent = $urlArray[$parent]['parent'];
			}
			else $parent = '';
		}
	}
	return $crumbString;
}

// Перевод многомерного массива в 2-мерный
function _s_CrumbsGetRelations($array,$parent) {
	$outputArray = array();
	foreach ($array as $value) {
		$outputArray[($value['url'])] = array('parent'=>$parent,'title'=>$value['title']);
		// Если есть дочерние
		if (count($value['items']) > 0) $outputArray+= _s_CrumbsGetRelations($value['items'],$value['url']);
	}
	return $outputArray;
}