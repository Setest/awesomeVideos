<?php
// set_time_limit(10);
// ini_set("max_execution_time", "600"); // включаем 10 минут на ограничение работы скрипта
// ini_set("max_input_time", "600"); // включаем 10 минут на ограничение работы скрипта
// error_reporting(E_ALL ^ E_NOTICE);  ini_set('display_errors', true);

require_once dirname(dirname(__FILE__)).'/items/drag.class.php';

/**
 * Enable an Item
 */
class awesomeVideosPlaylistDragAndDropProcessor extends awesomeVideosItemsDragAndDropProcessor {
	public $classKey = 'awesomeVideosPlaylist';
	public $objectType = 'awesomeVideosPlaylist';
}

return 'awesomeVideosPlaylistDragAndDropProcessor';