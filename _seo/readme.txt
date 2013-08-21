Способ подключения модуля:

1. Устанавливаем кодировку сайта в config.php
2. Нужно перехватить начало вывода, вставив код начала буферизации (установить его как можно раньше), например в самом верху index.php:

require_once dirname(__FILE__) . '/_seo/index.php';
ob_start();

Не забываем проверить правильный ли путь!

3. Устанавливаем в конце вывода следующий код (например в самом низу index.php, НО ДО любых вызовов функций exit(), если такие имеются):

if (function_exists('_seo_apply')) {
   $GLOBALS['_seo_content'] = ob_get_clean();
   _seo_apply();
   echo $GLOBALS['_seo_content'];
}



Особенности BITRIX.
1. Необходимо разместить код
require_once dirname(__FILE__) . '/../_seo/index.php';
в файл /bitrix/urlrewrite.php в самый верх.

2. Размещаем код
 require_once dirname(__FILE__) . '/_seo/index.php';
 ob_start();

в /bitrix/modules/main/include/prolog_before.php

3. Размещаем

if (function_exists('_seo_apply')) {
   $GLOBALS['_seo_content'] = ob_get_clean();
   _seo_apply();
   echo $GLOBALS['_seo_content'];
}

в /bitrix/modules/main/include/epilog_after.php  ПЕРЕД кодом, который отвечает за компрессию страницу и блокирует весь последующий вывод:

for($i=0; $i<count($arAllEvents); $i++)
	ExecuteModuleEventEx($arAllEvents[$i]);