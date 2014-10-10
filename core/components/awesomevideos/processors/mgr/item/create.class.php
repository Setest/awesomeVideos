<?php

/**
 * Create an Item
 */
class awesomeVideosItemCreateProcessor extends modObjectCreateProcessor {
	public $objectType = 'awesomeVideosItem';
	public $classKey = 'awesomeVideosItem';
	public $languageTopics = array('awesomevideos');
	//public $permission = 'create';


	/**
	 * @return bool
	 */
	public function beforeSet() {
		$name = trim($this->getProperty('name'));
		if (empty($name)) {
			$this->modx->error->addField('name', $this->modx->lexicon('awesomevideos_item_err_name'));
		}
		elseif ($this->modx->getCount($this->classKey, array('name' => $name))) {
			$this->modx->error->addField('name', $this->modx->lexicon('awesomevideos_item_err_ae'));
		}

		return parent::beforeSet();
	}

}

return 'awesomeVideosItemCreateProcessor';