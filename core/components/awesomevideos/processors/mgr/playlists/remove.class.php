<?php
// error_reporting(E_ALL ^ E_NOTICE);  ini_set('display_errors', true);

require_once dirname(dirname(__FILE__)).'/items/remove.class.php';

/**
 * Enable an Item
 */
class awesomeVideosPlaylistRemoveProcessor extends awesomeVideosItemRemoveProcessor {
	public $classKey = 'awesomeVideosPlaylist';
	public $objectType = 'awesomeVideosPlaylist';
}

return 'awesomeVideosPlaylistRemoveProcessor';