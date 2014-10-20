<?php
error_reporting(E_ALL ^ E_NOTICE);  ini_set('display_errors', true);


require_once 'update.class.php';

class awesomeVideosItemUpdateFromGridProcessor extends awesomeVideosItemUpdateProcessor {

	/**
	 * @return bool
	 */
	public function initialize() {

		$data=$this->modx->fromJSON($this->getProperty('data'));
		$data=$data?$data:array();
		// print_r($data);
		// return;
		$this->setDefaultProperties($data);
		unset($this->properties['data']);
		// print_r($this->getProperties());
		// echo 123;
		return parent::initialize();
	}

}

return 'awesomeVideosItemUpdateFromGridProcessor';