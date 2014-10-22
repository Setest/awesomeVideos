<?php
include_once 'setting.inc.php';

$_lang['awesomeVideos'] = 'Awesome videos';
$_lang['awesomeVideos_desc'] = 'Показывает YouTube видео на Вашем сайте';
$_lang['awesomeVideos_menu_desc'] = 'Пример расширения для разработки.';
$_lang['awesomeVideos_intro_msg'] = 'Вы можете выделять сразу несколько предметов при помощи Shift или Ctrl.';

/**
 * ERRORS
 */

$_lang['awesomeVideos_err'] = 'Ошибка!';
$_lang['awesomeVideos_err_ajax'] = 'Ошибка получения данных!';

/**
 * ITEMS
 */

$_lang['awesomeVideos_items'] = 'Видео';
$_lang['awesomeVideos_item'] = 'Видео';
$_lang['awesomeVideos_item_new'] = 'Новое видео';
$_lang['awesomeVideos_item_update'] = 'Обновить видео';
$_lang['awesomeVideos_item_remove'] = 'Удалить видео';
$_lang['awesomeVideos_item_remove.confirm'] = 'Вы уверенны что хотите удалить это видео?';
$_lang['awesomeVideos_item_error.nf'] = 'Видео не найденно';

$_lang['awesomeVideos_item_active'] = 'Активно';
$_lang['awesomeVideos_item_id'] = 'Video ID';
$_lang['awesomeVideos_item_special'] = 'Особенное';
$_lang['awesomeVideos_item_chosen'] = 'Избранное';
$_lang['awesomeVideos_item_image'] = 'Картинка';
$_lang['awesomeVideos_item_name'] = 'Название';
$_lang['awesomeVideos_item_description'] = 'Описание';
$_lang['awesomeVideos_item_keywords'] = 'Ключевые слова';
$_lang['awesomeVideos_item_source'] = 'Источник';
$_lang['awesomeVideos_item_author'] = 'Автор';
$_lang['awesomeVideos_item_duration'] = 'Продолжительность';
$_lang['awesomeVideos_item_duration.seconds'] = 'Секунд';
$_lang['awesomeVideos_item_advanced'] = 'Продвинутые';
$_lang['awesomeVideos_item_jsondata'] = 'JSON data';
$_lang['awesomeVideos_item_created'] = 'Дата создания';
$_lang['awesomeVideos_item_topic'] = 'Рубрика';

$_lang['awesomeVideos_item_channel'] = 'Канал';
$_lang['awesomeVideos_item_channelId'] = 'ID канала';
$_lang['awesomeVideos_item_user'] = 'Владелец';
$_lang['awesomeVideos_item_playlist'] = 'Плейлист';
$_lang['awesomeVideos_item_playlistId'] = 'ID плейлиста';

$_lang['awesomeVideos_item_form_tab_main'] = 'Основные';
$_lang['awesomeVideos_item_form_tab_tv'] = 'TV параметры';


$_lang['awesomeVideos_item_topic_empty'] = 'Ничего не выбранно';
$_lang['awesomeVideos_item_topic_notfound'] = 'Значение не найденно';


$_lang['awesomeVideos_item_create'] = 'Создать видео';
$_lang['awesomeVideos_item_update'] = 'Изменить видео';
$_lang['awesomeVideos_item_enable'] = 'Включить видео';
$_lang['awesomeVideos_items_enable'] = 'Включить видео ролики';
$_lang['awesomeVideos_item_disable'] = 'Отключить видео';
$_lang['awesomeVideos_items_disable'] = 'Отключить видео ролики';
$_lang['awesomeVideos_item_remove'] = 'Удалить';
$_lang['awesomeVideos_items_remove'] = 'Удалить видео ролики';
$_lang['awesomeVideos_item_remove_confirm'] = 'Вы уверены, что хотите удалить это видео?';
$_lang['awesomeVideos_items_remove_confirm'] = 'Вы уверены, что хотите удалить эти видео ролики?';
$_lang['awesomeVideos_item_active'] = 'Включено';

$_lang['awesomeVideos_item_err_class'] = 'Не могу загрузить основной класc.';

$_lang['awesomeVideos_item_err_name'] = 'Вы должны указать имя видеоролика.';
$_lang['awesomeVideos_item_err_videoId'] = 'Вы должны указать ID видеоролика.';
$_lang['awesomeVideos_item_err_videoIdNotExist'] = 'Видео с ID = [[+id]] не существует на сервере youTube, будьте внимательны!';

$_lang['awesomeVideos_item_err_ae'] = 'Видеоролик с таким именем уже существует.';
$_lang['awesomeVideos_item_err_videoIdExist'] = 'Видеоролик с таким ID уже существует.';
$_lang['awesomeVideos_item_err_nf'] = 'Видеоролик не найден.';
$_lang['awesomeVideos_item_err_ns'] = 'Видеоролик не указан.';
$_lang['awesomeVideos_item_err_remove'] = 'Ошибка при удалении видеоролика.';
$_lang['awesomeVideos_item_err_save'] = 'Ошибка при сохранении видеоролика.';

/**
 * PLAYLISTS
 */

$_lang['awesomeVideos_playlist_new'] = 'Добавить плейлист';
$_lang['awesomeVideos_playlists_import'] = 'Импорт плейлистов';
$_lang['awesomeVideos_playlists_synchronize'] = 'Связать ролики с плейлистами';
$_lang['awesomeVideos_playlist_remove'] = 'Удалить';
$_lang['awesomeVideos_playlists_remove'] = 'Удалить несколько плейлистов';
$_lang['awesomeVideos_playlist_remove_confirm'] = 'Вы уверены, что хотите удалить этот плейлист?';
$_lang['awesomeVideos_playlists_remove_confirm'] = 'Вы уверены, что хотите удалить эти плейлисты?';
$_lang['awesomeVideos_playlist_create'] = 'Создать плейлист';
$_lang['awesomeVideos_playlist_update'] = 'Изменить плейлист';

$_lang['awesomeVideos_playlist_user'] = 'Имя пользователя';

$_lang['awesomeVideosPlaylist_err_ns'] = 'Id не указано';
$_lang['awesomeVideos_playlist_err_playlist'] = 'Вы должны указать имя плейлиста!';
$_lang['awesomeVideos_playlist_err_playlistId'] = 'Вы должны указать ID плейлиста!';
$_lang['awesomeVideos_playlist_err_playlistIdExist'] = 'Плейлист с таким ID уже есть в таблице!';
$_lang['awesomeVideos_playlist_err_playlistIdNotExist'] = 'Плейлист с ID: &laquo;[[+id]]&raquo; не существует на сервере youTube, будьте внимательны!<br/>Данные не сохранены!';
$_lang['awesomeVideos_playlist_err_field'] = 'Ошибка при сохранении поля: [[+field]]';

/**
 * GRID
 */

$_lang['awesomeVideos_grid_search'] = 'Поиск';
$_lang['awesomeVideos_grid_actions'] = 'Действия';

/**
 * CONSOLE
 */

$_lang['awesomeVideos_console_finish'] = 'Импорт завершен!';

/**
 * SYNCHRONIZE
 */
$_lang['awesomeVideos_synchronize'] = 'Синхронизация данных';
$_lang['awesomeVideos_synchronize_finish'] = 'Синхронизация завершена успешно!';
$_lang['awesomeVideos_synchronize_finish_with_err'] = 'Синхронизация завершена с ошибками!';


/**
 * IMPORT
 */

$_lang['awesomeVideos_import'] = 'Импорт видео';
$_lang['awesomeVideos_import_started'] = 'Импорт с [[+source]] для [[+user]]';
$_lang['awesomeVideos_import_complete'] = 'Всего импортированно [[+total]] [[+source]] видео ([[+new]] новых) для пользователя [[+user]].';
$_lang['awesomeVideos_import_err'] = 'Импорт не удался.';
$_lang['awesomeVideos_import_err.client'] = 'REST client недоступен.';
$_lang['awesomeVideos_import_current'] = 'Импортируется [[+page]] страница из [[+pages]], содержит записей: ';
