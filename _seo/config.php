<?php
$config = array(
		'encoding' => 'utf-8',

		'module_meta_enabled' => 1,
		'module_urls_enabled' => 1,
		'module_headers_enabled' => 1,
		'module_query' => array(
				'enabled' => 0,
				'common_functions' => array(
						'_setH1'
				),
				'functions' => array(
						'/' => 'function_main',
						'/contacts/' => 'function_contacts',
				)
		),
		'module_label_replacement' => 1,
		'module_forms_enabled' => 1,
		'module_breadcrumbs_enabled' => 1,
		'module_breadcrumbs_kw_enabled' => 1,
		// Удаление рекламных get параметров
		'module_get_remove' => array('utm_source','utm_medium','utm_campaign','utm_term','utm_content','_openstat'),
		'adminConfig' => array(
				'additionalTags' => array(
						'h1', 't_1', //'t_2', // для textarea переменная должна начинаться с префикса t_
				),
				'breadcrumbs' => array(
					'tag' => 'crumbs', // тег для замены (<!--$$tag-->)
					'friendly_urls' => true, // используется ли ЧПУ (если нет, то крошки формируются только для внесенных в базу значений)
					'page_end' => '/', // например для "/parent/child.html" будет ".html" (используется только для 'friendly_urls'=>true)
					'divider' => ' / ', // разделитель крошек в html (например для " -> " будет "Новости -> Март")
				),
				'forms' => array(
						array(
							'tag' => 'form_1', // тег для замены (<!--$$tag-->), также используется как имя каталога для сохраняемых писем и id формы
							'header' => 'Заголовок формы', // ~ <h2>Заголовок формы</h2>
							'submit' => 'Отправить', // ~ по умолчанию "Отправить"
							'success' => 'Спасибо! ваше сообщение успешно доставлено!', // если не указать, при успешной отправке форма просто исчезнет
							'errors' => 'Пожалуйста, проверьте еще раз введенные данные.', // ~ главное сообщение об ошибке
							'css' => 'default.css', // подключаемый css файл из "modules/forms/styles/", если не надо, то false
							'emailSettings' => array(
								'from' => 'Иванов <mail@example.com>', // отправитель (просто email или "Имя <mail@example.com>")
								'to' => 'mail1@example.com,mail2@example.com', // получатели, только в формате "mail@example.com"
								'subject' => 'Письмо с сайта example.com', // ~ тема письма
								'intro' => 'С сайта example.com была отправлена форма такая-то со следующими данными', // ~ вводный текст письма
							),
							'fields' => array( // Список полей
								array(
									'name' => 'name', // атрибут name
									'letter_name' => 'Имя', // ~ имя поля в письме
									'tag' => 'input', // ~ input (по умолчанию) или textarea
									'label' => 'Как Вас зовут', // ~ метка в теге label
									'placeholder' => 'Введите Ваше имя', // ~ атрибут placeholder
									'type' => 'text', // ~ типы для input (по умолчанию text), не поддерживается checkbox и radio
									'validator' => 'notempty,max=100', // ~ допустимо min/max=int, notempty, email, phone; лучше в любом случае указывать max=X
									'required' => true, // ~ влияет только на вывод * в Label
									'error' => 'Поле обязательно для заполнения', // ~ сообщение при ошибке валидации
								),
								array(
									'name' => 'phone',
									'letter_name' => 'Номер телефона',
									'tag' => 'input',
									'label' => 'Номер Вашего телефона',
									'placeholder' => '+7-900-123-45-67',
									'type' => 'tel',
									'validator' => 'phone',
									'required' => true,
									'error' => 'Никаких букв',
								),
								array(
									'name' => 'email',
									'letter_name' => 'Email',
									'tag' => 'input',
									'type' => 'email',
									'label' => 'Email',
									'placeholder' => 'Email',
									'validator' => 'email',
									'required' => true,
									'error' => 'Введите адрес в формате: mail@example.com',
								),
								array(
									'name' => 'subject',
									'letter_name' => 'Тема сообщения',
									'tag' => 'input',
									'type' => 'text',
									'label' => 'Тема сообщения',
									'placeholder' => 'Тема',
								),
								array(
									'name' => 'message',
									'letter_name' => 'Текст сообщения',
									'tag' => 'textarea',
									'placeholder' => 'Текст сообщения',
									'validator' => 'min=10,max=1000',
									'required' => true,
									'error' => 'Не менее 10 и не более 1000 знаков',
								),
							),
						),
				),
				'information_systems' => array(
						array(
								'id' => 'reviews',
								'title' => 'Отзывы',
								'template_item' => 'information_item.php',
								'template_list' => 'information_items.php',
								'template_reply' => 'information_reply.php',
								'fields' => array(
										array( // все, кроме (id, title, type), используется только в отзывах пользователей
												'id' => 'title',
												'title' => 'Заголовок',
												'validator' => 'notempty,max=100',
												'error' => 'Не более 100 символов',
												'placeholder' => 'Заголовок сообщения',
												'label' => 'Заголовок (до 100 символов)',
												'required' => true,
										),
										array(
												'id' => 'text',
												'title' => 'Текст',
												'type' => 'textarea',
												'label' => 'Текст сообщения',
												'validator' => 'notempty,max=2000',
												'required' => true,
										),
										array(
												'id' => 'author',
												'title' => 'Автор',
												'validator' => 'notempty,max=2000',
												'label' => 'Ваше имя',
												'required' => true,
										),
								),
						),
						array(
								'id' => 'news',
								'title' => 'Новости',
								'template_item' => 'information_item.php',
								'template_list' => 'information_items.php',
								'fields' => array(
										array(
												'id' => 'title',
												'title' => 'Заголовок',
										),
										array(
												'id' => 'text',
												'title' => 'Текст',
												'type' => 'textarea',
										),
								),
						),
				),
				'login' => 'admin',
				'password' => '9beff0a36668837f7e6f3c4579838e22', // md5, можно сгенерить на http://md5x.ru/
		),
		'rememberMode' => 1
);

/*
 Функции обработки контента. Существует 3 варианта.
 работаем напрямую со строкой $GLOBALS['_seo_content'].
$pageInfo - информация о текущей странице из config.ini в виде массива.
 */
if(!function_exists('_setH1')){
	function _setH1($pageInfo)
	{
		// $GLOBALS['_seo_content'] = str_replace('ул. Рябиновая 45', 'ул. Рябиновая 46', $GLOBALS['_seo_content']);
	}
}

if(!function_exists('function_main')){
	function function_main($pageInfo)
	{

	}
}
return $config;