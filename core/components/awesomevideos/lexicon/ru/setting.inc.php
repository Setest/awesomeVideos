<?php

$_lang['area_awesomevideos_main'] = 'Основные';

/* Settings */
$_lang['setting_awesomeVideos.video.topic.getdatatype'] = 'Способ выборки данных';
$_lang['setting_awesomeVideos.video.topic.getdatatype_desc'] = 'Впишите соответствующее цифровое значение:
<i>
	<ul>
		<li>1-getResources</li>
		<li>2-get_tree_setest</li>
	</ul>
<i>';
$_lang['setting_awesomeVideos.video.topic.parent'] = 'Рубрика';
$_lang['setting_awesomeVideos.video.topic.parent_desc'] = 'Укажите Id элемента являющийся родителем всех рубрик.';

/* YouTube */
$_lang['setting_awesomeVideos.youtube.active'] = 'Активировать';
$_lang['setting_awesomeVideos.youtube.active_desc'] = 'Активировать источник';
$_lang['setting_awesomeVideos.youtube.apikey'] = '';
$_lang['setting_awesomeVideos.youtube.apikey_desc'] = '';
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