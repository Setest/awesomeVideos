<?php
// error_reporting(E_ALL ^ E_NOTICE);  ini_set('display_errors', true);

require_once 'update.class.php';

class awesomeVideosPlaylistUpdateFromGridProcessor extends awesomeVideosPlaylistUpdateProcessor {

	/**
	 * @return bool
	 */
	public function beforeSet() {
		$canSave = parent::beforeSet();
    if ($canSave !== true || $this->modx->error->hasError() ) {
			return $this->modx->error->getErrors();
			// return $this->modx->error->failture('');
    }
    return true;
	}

	/**
	 * @return bool
	 */
	public function initialize() {

		$data=$this->modx->fromJSON($this->getProperty('data'));
		$data=$data?$data:array();
		$this->setDefaultProperties($data);
		unset($this->properties['data']);
		return parent::initialize();
	}

}

return 'awesomeVideosPlaylistUpdateFromGridProcessor';