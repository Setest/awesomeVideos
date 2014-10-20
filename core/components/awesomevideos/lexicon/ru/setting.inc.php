<?php

$_lang['area_awesomevideos_main'] = 'Основные';

/* Settings */

$_lang['setting_awesomeVideos.video.topic.source'] = 'Источник рубрики';
$_lang['setting_awesomeVideos.video.topic.source_desc'] = 'По умолчанию - пусто, это значит что используется метод <b>newQuery</b>
из класса <b>modObjectGetListProcessor</b>. Если вписать имя или идентификатор сниппета, то он будет запускаться в качестве
обработчика, с параметами указанными в ключе <b>awesomeVideos.video.topic.params</b>, сниппет может возвращать типы JSON, array, boolean.
Можно также вписать данные вручную в формате JSON, например:
<pre>[
	{"id":"","topic":"empty"},
	{"id":"0","topic":"one"},
	{"id":"test","topic":"second value"},
	{"id":"33","topic":"another topic value"}
]</pre>
<p><b>Учтите данные приходящие от сниппета должны содежать массив каждый параметр которого хранит массив с параметрами id и topic.</b></p>
';

$_lang['setting_awesomeVideos.video.topic.params'] = 'Параметры выборки рубрики';
$_lang['setting_awesomeVideos.video.topic.params_desc'] = '<b>Все указанные тут параметры будут влиять на выбрку рубрики</b>.
Указываются в виде JSON, если Вы используете свой сниппет для выборки, то на выходе данные должны быть также в виде JSON.
Например для getResources, нужно дополнительно указать:
<pre>
...
"tpl": "{id:\"[[+id]]\",topic: \"[[+pagetitle]]\"},",
...
</pre>
В параметры where может содержать строку или JSON, но тогда кавычки в нем должны быть экранированными.<br/><br/>
Пример параметров при выполнении newQuery (т.е. topic.source - пустой):
<pre>
{"where":"{\"parent\":\"10\"}","limit":"0","start":"0"}
</pre>
<br/><br/>
Для сниппета getResources:
<pre>
{"parents":"10","tpl":"@INLINE {\"id\":\"[[+id]]\",\"topic\": \"[[+pagetitle]]\"},"}
</pre>

<br/><br/>
Для сниппета get_tree_setest:
<pre>
{"empty_value":"false", "parent":"10", "context":"web", "additional":"", "template":"4", "separator":"-", "depth":"11", "debug":"0", "style":"0", "return_type":"json"}
</pre>
';

$_lang['setting_awesomeVideos.video.topic.tpl'] = 'Шаблон рубрики';
$_lang['setting_awesomeVideos.video.topic.tpl_desc'] = 'Указываем вид строки рубрики в виде плейсхолдеров. Данный параметр будет игнорироваться
при использовании сниппета в качестве источника рубрики. Пример:
<pre>
[[+pagetitle]] ([[+id]])
</pre>
';

$_lang['setting_awesomeVideos.video.topic.fieldId'] = 'Название поля выборки';
$_lang['setting_awesomeVideos.video.topic.fieldId_desc'] = 'По данному полю происходит получения значения Topic при открытии грида и в сниппете, т.к.
при использовании своего сниппета, этот критерий может оличаться от стандартного. Используется в параметре where. По умолчанию: <b>id</b>';

$_lang['setting_awesomeVideos.video.topic.tplId'] = 'Шаблон ID рубрики';
$_lang['setting_awesomeVideos.video.topic.tpl_desc'] = 'Используется в основном при запуске парсера для сниппета. По умолчанию: [[+id]]';


$_lang['setting_awesomeVideos.video.topic.tplparse'] = 'Использовать шаблон в сниппете?';
$_lang['setting_awesomeVideos.video.topic.tplparse_desc'] = 'При true будет происходить парсинг данных полученные в результате работы сниппета,
в соответствии с шаблоном указанным в параметре <b>awesomeVideos.video.topic.tpl</b>.';


/* YouTube */
$_lang['setting_awesomeVideos.youtube.active'] = 'Активировать';
$_lang['setting_awesomeVideos.youtube.active_desc'] = 'Активировать источник';
$_lang['setting_awesomeVideos.youtube.apikey'] = 'Ключ разработчика для доступа к YouTube Data API v3';
$_lang['setting_awesomeVideos.youtube.apikey_desc'] = 'Его можно получить воспользовавшись <a target="_blank" href="https://console.developers.google.com/">Google Developers Console</a>, более подробная информация <a target="_blank" href="https://www.youtube.com/watch?v=Im69kzhpR3I">сдесь</a>.';
$_lang['setting_awesomeVideos.youtube.user'] = 'Login';
$_lang['setting_awesomeVideos.youtube.user_desc'] = 'Имя пользователя (канала)';


$_lang['setting_awesomeVideos.video.imageCachePath'] = 'Путь к папке кеша превьюшек';
$_lang['setting_awesomeVideos.video.imageCachePath_desc'] = 'Если параметр <b>imageSourceId</b> не указан, путь к папке берется из корня сайта.';

$_lang['setting_awesomeVideos.video.imageSourceId'] = 'Источник изображений куда будут попадать превьюшки';
$_lang['setting_awesomeVideos.video.imageSourceId_desc'] = '';

$_lang['setting_awesomeVideos.video.source_detail'] = 'Источник данных в виде JSON';
$_lang['setting_awesomeVideos.video.source_detail_desc'] = 'Можно использовать различные комбинации, например:
	<ul>
		<li>[{"c":"UCsjEcIIR9nVFI1RYE9rawfg"},{"u":"MrSetest"},{"p":"PLK2K6UAy2uj8iTbSMEAmG2dcoW7vwhkcD"}]</li>
		<li>[{"c":"UCtsDl3hsddpyDzHSdrU2OOg"},{"c":"UCtsDl3hsddpyDzHSdrU2OOg2222fd"},{"u":"MrSetest"},{"p":"PLK2K6UAy2uj8iTbSMEAmG2dcoW7vwhkcD"}]</li>
		<li>[{"p":"PLK2K6UAy2uj-uidRBkSASBDo9nN_Kd_hU"},{"p":"PLK2K6UAy2uj8iTbSMEAmG2dcoW7vwhkcD"}]</li>
	</ul>
Где: c - id канала, u - имя пользователя, p - id плейлиста. Последовательность и кол-во не имеет значение, может повлиять только на время импорта.
';