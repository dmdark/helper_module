### Способ подключения модуля

---

#### Первый способ

1. Устанавливаем кодировку сайта в ``config.php``
2. Перехватываем начало вывода, вставив код начала буферизации
(установить его как можно раньше, например в самом верху
``index.php``):

```php
require_once dirname(__FILE__) . '/_seo/index.php';
ob_start("_seo_ob_callback");
```

---

#### Второй способ

1. Устанавливаем кодировку сайта в ``config.php``
2. Нужно перехватить начало вывода, вставив код начала буферизации
(установить его как можно раньше, например в самом верху
``index.php``):

```php
require_once dirname(__FILE__) . '/_seo/index.php';
ob_start();
```

**Не забываем проверить правильный ли путь!**

3. Устанавливаем в конце вывода следующий код (например в самом низу
``index.php``, **но до** любых вызовов функций ``exit()``, если такие
имеются):

```php
if (function_exists('_seo_apply')) {
	$GLOBALS['_seo_content'] = ob_get_clean();
	_seo_apply();
	echo $GLOBALS['_seo_content'];
}
```

---

#### Особенность Bitrix

1. В файле ``/bitrix/urlrewrite.php`` в самом верху устанавливаем код:

```php
require_once dirname(__FILE__) . '/../_seo/index.php';
```

2. В ``/bitrix/modules/main/include/prolog_before.php`` размещаем код:
```php
require_once dirname(__FILE__) . '/../../../../_seo/index.php';
ob_start();
```

3. В ``/bitrix/modules/main/include/epilog_after.php`` **перед**
кодом, который отвечает за компрессию страницу и блокирует весь
последующий вывод, размещаем:
```php
for($i=0; $i<count($arAllEvents); $i++) {
	ExecuteModuleEventEx($arAllEvents[$i]);
}
```

---

#### Особенность UMI.CMS

1. Подключаем модуль в самом верху файла ``/libs/root-src/index.php``
2. Меняем строку
```php
$buffer = OutputBuffer::current('HTTPOutputBuffer');
```
на
```php
class _SeoHttpOutputBuffer extends HTTPOutputBuffer {
	public function length() {
		if (function_exists('mb_strlen')) {
			return mb_strlen($this->buffer);
		} else {
			return strlen($this->buffer);
		}
	}
}
$buffer = OutputBuffer::current('_SeoHttpOutputBuffer');
```

---

#### Особенность Drupal

1. Требуется отключить кэш страниц и меню админа.
2. Модуль подключается в ``index.php`` с условием:
```php
if (!strpos($_SERVER['REQUEST_URI'], 'admin') && !strpos($_SERVER['REQUEST_URI'], 'image_captcha')) {
	require_once dirname(__FILE__) . '/_seo/index.php';
	ob_start("_seo_ob_callback");
}
```

---

### Модуль замены

Для вставки *с заменой* содержимого:
```html
<!--$$t_1-->
Вывод
<!--/$$t_1-->
```
Для вставки *без замены*:
```html
<!--$$t_1-->
```
``t_1`` - имя текстовой переменной, задается в конфиге.

---

### Хранение данных в БД MySQL

1. В ``_seo/config.php`` необходимо указать параметры подключения к БД.
2. Необходимо перейти на страницу тестирования модуля
(``/_seo/admin/test.php``). При тестировании создадутся необходимые
для модуля таблицы. Без них модуль не будет работать.

---

### Объединение настроек

Если необходимо добавить в текущие настройки некоторые новые.

1. Записываем новые настройки в файл и сохраняем их в ``config_new.ini``.
2. Вызываем ``site-name.dom/_seo/modules/merge_configs.php``.
3. Если настройки хранятся в БД, то изменения применены, удаляем ``config_new.ini``.
4. Если настройки хранятся в файле, то убеждаемся, что скрипт отработал
корректно, удаляем ``config.ini``, переименовываем ``config_new.ini``
в ``config.ini``.
