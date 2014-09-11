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
				unset($urlArray[$aliasCount]);
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


// Замена хлебных крошек
function _s_applyCrumbsKw() {

	$pageInfo = getCurrentPageInfo();
	$crumbsTagStart = '<!--$$crumbskw-->';
	$crumbsTagEnd = '<!--/$$crumbskw-->';
	if (empty($pageInfo['crumbskw'])) return _s_crumbsKwReplace(false);

	preg_match('/'.preg_quote($crumbsTagStart,'/').'(.*?)'.preg_quote($crumbsTagEnd,'/').'/ms',$GLOBALS['_seo_content'],$matches);
	if (!array_key_exists(1,$matches) || empty($matches[1])) return _s_crumbsKwReplace(false);

	// Исходный блок
	$sourceBlock = $matches[1];

	// Шаблоны: общий, одной родительской ссылки, текущего элемента
	$tplFolder = dirname(__FILE__).'/../../templates/breadcrumbs/';
	$tplContainer = file_get_contents($tplFolder.'container.html');
	$tplParent = file_get_contents($tplFolder.'parents.html');
	$tplCurrent = file_get_contents($tplFolder.'item.html');

	// Массив используемых плейсхолдеров (подготовленный для регулярок)
	$phArray = array('{{items}}','{{link}}','{{title}}','{{text}}');
	$phArray = array_map('preg_quote',$phArray);

	// Очищаем исходный блок и шаблоны от переводов строк
	$sourceBlock = _s_crumbsKwClearNl($sourceBlock);
	$tplContainer = _s_crumbsKwClearNl($tplContainer);
	$tplParent = _s_crumbsKwClearNl($tplParent);
	$tplCurrent = _s_crumbsKwClearNl($tplCurrent);

	// Массив, содержащий всю основную информацию
	$crumbs = array(
		'oldContent' => array(),
		'inWork' => array(),
		'items' => array(),
		'current' => array(),
	);

	// Получаем содержимое контейнера (если его нет, то останавливаемся)
	$containerText = _s_crumbsKwGetItemInfo($sourceBlock,$tplContainer,array('items'=>false),true);
	if (!$containerText['items']) {
		return _s_crumbsKwReplace(false);
	}
	$crumbs['oldContent']['items'] = $containerText['items'];

	// Получаем родительские элементы
	$parentPattern = '/('.str_replace($phArray,'.*?',preg_quote($tplParent,'/')).')/';
	preg_match_all($parentPattern,$crumbs['oldContent']['items'],$matches);
	if (is_array($matches) && array_key_exists(1,$matches) && is_array($matches[1])) {
		$crumbs['inWork']['parents'] = array();
		foreach ($matches[1] as $parent) {
			$crumbs['inWork']['parents'][] = $parent;
			$crumbs['oldContent']['items'] = str_replace($parent,'',$crumbs['oldContent']['items']);
		}
	}
	if (is_array($crumbs['inWork']['parents'])) {
		foreach ($crumbs['inWork']['parents'] as $value) {
			$crumbs['items'][] = _s_crumbsKwGetItemInfo($value,$tplParent);
		}
	}
	unset($matches);

	// Получаем текущий элемент
	$currentPattern = '/('.str_replace($phArray,'.*?',preg_quote($tplCurrent,'/')).')/';
	preg_match($currentPattern,$crumbs['oldContent']['items'],$matches);
	// Если не получаем текущий, останавливаемся
	if (!is_array($matches) || !array_key_exists(1,$matches)) {
		return _s_crumbsKwReplace(false);
	}
	// Если текущий получили пустым, для {{text}} добавляем жадность
	if (empty($matches[1])) {
		$currentPattern = str_replace(preg_quote('{{text}}'),'.*',preg_quote($tplCurrent,'/'));
		$currentPattern = '/('.str_replace($phArray,'.*?',$currentPattern).')/';
		preg_match($currentPattern,$crumbs['oldContent']['items'],$matches);
	}
	// Записываем старое содержимое хлебной крошки в $crumbs['oldContent']['current']
	$crumbs['oldContent']['current'] = trim($matches[1]);
	unset($matches);
	// Информация о текущей странице $crumbs['current'] - массив
	$crumbs['current'] = _s_crumbsKwGetItemInfo($crumbs['oldContent']['current'],$tplCurrent,false,array('text'=>true));

	$crumbs['current']['text'] = _s_crumbsGetNewText($pageInfo['crumbskw'],$crumbs['current']['text']);


	// Собираем новый блок
	$result = '';
	foreach ($crumbs['items'] as $itemInfo) {
		$result.= _s_crumbsKwPlaceItemInfo($itemInfo,$tplParent).' ';
	}
	$result.= _s_crumbsKwPlaceItemInfo($crumbs['current'],$tplCurrent);
	$result = _s_crumbsKwPlaceItemInfo(array('items'=>$result),$tplContainer);

	return _s_crumbsKwReplace($result);
}
// Новые строки и множественные пробелы в 1 пробел
function _s_crumbsKwClearNl($text) {
	$text = str_replace(array("\n","\r","\t"),' ',$text);
	return trim(preg_replace('/\s+/',' ',$text));
}
// Получение информации из строки по шаблону с элементами {{link}} и пр.
function _s_crumbsKwGetItemInfo($string,$tpl,$info=false,$greed=false) {
	if (!$info) {
		$info = array(
			'link' => false,
			'title' => false,
			'text' => false,
		);
	}
	$tpl = preg_quote($tpl,'/');
	foreach ($info as $key => $value) {
		if (is_array($greed) && array_key_exists($key,$greed) && $greed[$key]) {
			$replacement = '(.*)';
		} elseif ($greed) {
			$replacement = '(.*)';
		} else $replacement = '(.*?)';
		$pattern = str_replace(preg_quote('{{'.$key.'}}'),$replacement,$tpl);
		$pattern = preg_replace('/'.preg_quote('\{\{').'.*?'.preg_quote('\}\}').'/','.*?',$pattern);
		preg_match('/'.$pattern.'/',$string,$matches);
		if (array_key_exists(1,$matches)) $info[$key] = $matches[1];
	}
	return $info;
}
// Запись значений вместо плейсхолдеров
function _s_crumbsKwPlaceItemInfo($array,$tpl) {
	foreach ($array as $key => $value) {
		if (!$value) $value = '';
		$tpl = str_replace('{{'.$key.'}}',$value,$tpl);
	}
	return $tpl;
}
// Получение нового текста из шаблона
function _s_crumbsGetNewText($pattern,$oldString) {
	preg_match_all('/\[(.*?)\]/',$pattern,$matches);
	if (array_key_exists(1,$matches) && is_array($matches[1])) {
		foreach ($matches[1] as $words) {
			$words = explode('|',$words);
			shuffle($words);
			$pattern = preg_replace('/\[.*?\]/',$words[1],$pattern,1);
		}
	}
	$newString = str_replace('{T}',$oldString,$pattern);
	// Замена {t} на текст крошки в нижнем регистре
	$encoding = $GLOBALS['_seo_config']['encoding'];
	// 1-й символ в нижний регистр
	$strLen = mb_strlen($oldString,$encoding);
	$firstChar = mb_substr($oldString,0,1,$encoding);
	$then = mb_substr($oldString,1,$strLen-1,$encoding);
	$oldString = mb_strtolower($firstChar, $encoding) . $then;
	$newString = str_replace('{t}',$oldString,$newString);
	return $newString;
}
// Замена содержимого хлебных крошек
function _s_crumbsKwReplace($newContent) {
	$crumbsTagStart = '<!--$$crumbskw-->';
	$crumbsTagEnd = '<!--/$$crumbskw-->';
	// Если ничего не надо применять просто удаляем теги
	if (!$newContent) {
		$GLOBALS['_seo_content'] = str_replace(array($crumbsTagStart,$crumbsTagEnd),'',$GLOBALS['_seo_content']);
		return false;
	}
	$GLOBALS['_seo_content'] = preg_replace('/'.preg_quote($crumbsTagStart,'/').'(.*?)'.preg_quote($crumbsTagEnd,'/').'/ms',$newContent,$GLOBALS['_seo_content']);
	return true;
}