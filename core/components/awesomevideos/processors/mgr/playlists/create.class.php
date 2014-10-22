<?php

/**
 * Create an Item
 */

class awesomeVideosPlaylistCreateProcessor extends modObjectCreateProcessor {
	public $objectType = 'awesomeVideosPlaylist';
	public $classKey = 'awesomeVideosPlaylist';
	public $languageTopics = array('awesomevideos');
	//public $permission = 'create';


	/**
	 * @return bool
	 */
	public function beforeSet() {
		$playlistId = trim($this->getProperty('playlistId'));

		if (!empty($playlistId)){
			if (!$playlistInfo=$this->awesomeVideos->getInfoByIdsFromYouTube(array('id'=>$playlistId),'playlists') OR empty($playlistInfo['items']))
			{
				// проверяем на youtube существует ли такой плейлист
				$this->modx->error->addField('playlistId', $this->modx->lexicon('awesomeVideos_playlist_err_playlistIdNotExist', array('id'=>$playlistId)));
				// $this->modx->error->addError($this->modx->lexicon('awesomeVideos_playlist_err_playlistIdNotExist', array('id'=>$playlistId)));
				// return $this->modx->lexicon('awesomeVideos_item_err_videoIdNotExist',array('id'=>$playlistId));
			}
			if ($this->modx->getCount($this->classKey, array('playlistId' => $playlistId))) {
				$this->modx->error->addField('videoId', $this->modx->lexicon('awesomeVideos_playlist_err_playlistIdExist'));
				return $this->modx->lexicon('awesomeVideos_playlist_err_playlistIdExist');
			}
		}

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

return 'awesomeVideosPlaylistCreateProcessor';