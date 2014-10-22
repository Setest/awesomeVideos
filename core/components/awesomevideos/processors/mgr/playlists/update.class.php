<?php
// error_reporting(E_ALL ^ E_NOTICE);  ini_set('display_errors', true);
class awesomeVideosPlaylistUpdateProcessor extends modObjectUpdateProcessor {
	public $objectType = 'awesomeVideosPlaylist';
	public $classKey = 'awesomeVideosPlaylist';
	public $languageTopics = array('awesomevideos');
	//public $permission = 'save';

	/**
	 * We doing special check of permission
	 * because of our objects is not an instances of modAccessibleObject
	 *
	 * @return bool|string
	 */
	public function beforeSave() {
		if (!$this->checkPermissions()) {
			return $this->modx->lexicon('access_denied');
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function beforeSet() {

		// print_r($this->getProperties());
		// echo 123;
		$id = (int)$this->getProperty('id');
		$playlistId = trim($this->getProperty('playlistId'));
		// $videoId = trim($this->getProperty('videoId'));
		if (empty($id)) {
			return $this->modx->lexicon('awesomeVideos_item_err_ns');
		}

		if (!empty($playlistId) &&
			 (!$playlistInfo=$this->awesomeVideos->getInfoByIdsFromYouTube(array('id'=>$playlistId),'playlists') OR empty($playlistInfo['items']))
			 ) {
			// проверяем на youtube существует ли такой плейлист
			$this->modx->error->addField('playlistId', $this->modx->lexicon('awesomeVideos_playlist_err_playlistIdNotExist', array('id'=>$playlistId)));
			$this->modx->error->addError($this->modx->lexicon('awesomeVideos_playlist_err_playlistIdNotExist', array('id'=>$playlistId)));
			// return $this->modx->lexicon('awesomeVideos_item_err_videoIdNotExist',array('id'=>$playlistId));
		}

		// print_r($playlistInfo);

		// $this->modx->error->addField('playlistId', 'TEST');
		// $this->modx->error->failure('Ошибка при сохранении RANK у документа с ID = ');

		if ($this->getProperty('playlist') && !trim($this->getProperty('playlist')) ) {
			$this->modx->error->addError($this->modx->lexicon('awesomeVideos_playlist_err_field', array('field'=>'playlist')));
			$this->modx->error->addField('playlist', $this->modx->lexicon('awesomeVideos_playlist_err_playlist'));
		}

		// if ($playlistId && empty($playlistId)) {
		// 	$this->modx->error->addError($this->modx->lexicon('awesomeVideos_playlist_err_field', array('field'=>'playlistId')));
		// 	$this->modx->error->addField('playlistId', $this->modx->lexicon('awesomeVideos_playlist_err_playlistId'));
		// }
		if ($playlistId && $this->modx->getCount($this->classKey, array('playlistId' => $playlistId, 'id:!=' => $id))) {
			$this->modx->error->addError($this->modx->lexicon('awesomeVideos_playlist_err_field', array('field'=>'playlistId')));
			$this->modx->error->addField('playlistId', $this->modx->lexicon('awesomeVideos_playlist_err_playlistIdExist'));
		}

		// $video->setProperties($scriptProperties);
		// unset($this->properties['tvs']);

		return parent::beforeSet();
	}

  /**
   * {@inheritDoc}
   * @return mixed
   */
  public function process() {
  	// подгрузим основной класс
		if ($this->loadClass() !== true) {
			return $this->failure();
		}
		return parent::process();
	}

	/**
	 * Loads awesomeVideos class to processor
	 *
	 * @return bool
	 */
	private function loadClass() {
		if (!$this->awesomeVideos = & $this->modx->getService('awesomevideos', 'awesomeVideos', $this->modx->getOption('awesomevideos_core_path', null, $this->modx->getOption('core_path') . 'components/awesomevideos/') . 'model/awesomevideos/',
			 array()
		 ))
		{
			return $this->failure($this->modx->lexicon('awesomeVideos_item_err_class'));
		}
		return true;
	}

}

return 'awesomeVideosPlaylistUpdateProcessor';