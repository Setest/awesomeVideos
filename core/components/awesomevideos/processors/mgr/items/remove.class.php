<?php
// error_reporting(E_ALL ^ E_NOTICE);  ini_set('display_errors', true);
class awesomeVideosItemRemoveProcessor extends modObjectProcessor {
	public $objectType = 'awesomeVideosItem';
	public $classKey = 'awesomeVideosItem';
	public $languageTopics = array('awesomevideos');
	//public $permission = 'save';


	/**
	 * @return array|string
	 */
	public function process() {
// echo 123;
// print_r($this->object);
		if (!$this->checkPermissions()) {
			return $this->failure($this->modx->lexicon('access_denied'));
		}

		if ($this->loadClass() !== true) {
			return $this->failure();
		}

		$ids = $this->modx->fromJSON($this->getProperty('ids'));
		if (empty($ids)) {
			return $this->failure($this->modx->lexicon('awesomevideos_item_err_ns'));
		}

		foreach ($ids as $id) {
			/** @var awesomeVideosItem $object */
			if (!$object = $this->modx->getObject($this->classKey, $id)) {
				return $this->failure($this->modx->lexicon('awesomevideos_item_err_nf'));
			}

			$object->remove();

		}

		if ($this->modx->error->hasError()){
				return $this->failure($this->modx->error->getErrors());
		}

		return $this->success();
	}

	/**
	 * Loads awesomeVideos class to processor
	 *
	 * @return bool
	 */
	private function loadClass() {
		// $obj=$this->object->xpdo->awesomevideos

			// echo 123;
		if (!$this->awesomeVideos = & $this->modx->getService('awesomevideos', 'awesomeVideos', $this->modx->getOption('awesomevideos_core_path', null, $this->modx->getOption('core_path') . 'components/awesomevideos/') . 'model/awesomevideos/',
			 array()
		 ))
		{
			return $this->failure($this->modx->lexicon('awesomeVideos_item_err_class'));
		}
		return true;
	}

}

return 'awesomeVideosItemRemoveProcessor';