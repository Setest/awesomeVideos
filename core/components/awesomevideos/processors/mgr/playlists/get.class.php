<?php

/**
 * Create an Item
 */

class awesomeVideosPlaylistGetProcessor extends modObjectGetProcessor {
	public $objectType = 'awesomeVideosPlaylist';
	public $classKey = 'awesomeVideosPlaylist';
	public $languageTopics = array('awesomevideos');
	//public $permission = 'create';

	/**
	 * We doing special check of permission
	 * because of our objects is not an instances of modAccessibleObject
	 *
	 * @return mixed
	 */
	public function process() {
		if (!$this->checkPermissions()) {
			return $this->failure($this->modx->lexicon('access_denied'));
		}

		return parent::process();
	}

  /**
   * Return the response
   * @return array
   */
  public function cleanup() {
      return $this->success('',$this->object->toArray());
  }

}

return 'awesomeVideosPlaylistGetProcessor';