<?php

// error_reporting(2047); ini_set('display_errors', true);
// echo 888;

/**
 * Class awesomeVideosMainController
 */
require_once dirname(__FILE__) . '/model/awesomevideos/awesomevideos.class.php';

abstract class awesomeVideosMainController extends modExtraManagerController {
	/** @var awesomeVideos $awesomeVideos */
	public $awesomeVideos;


	/**
	 * @return void
	 */
	public function initialize() {
		// echo $path;

		// $corePath=$this->modx->getOption('base_path').$this->modx->getOption('awesomevideos_core_path', null, $this->modx->getOption('core_path') . 'components/awesomevideos/') ;
		$corePath = $this->modx->getOption('awesomevideos_core_path', null, $this->modx->getOption('core_path') . 'components/awesomevideos/');
		require_once $corePath . 'model/awesomevideos/awesomevideos.class.php';

		$this->awesomeVideos = new awesomeVideos($this->modx);
		$this->addCss($this->awesomeVideos->config['cssUrl'] . 'mgr/main.css');
		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/awesomevideos.js');

		$this->addHtml('
		<script type="text/javascript">
			awesomeVideos.config = ' . $this->modx->toJSON($this->awesomeVideos->config) . ';
		</script>
		');
			// awesomeVideos.config.connector_url = "' . $this->awesomeVideos->config['connectorUrl'] . '";


/*
    $config_js = preg_replace(array('/^\n/', '/\t{6}/'), '', "
        awesomeVideosConfig = {
            ctx: '{$ctx}'
            ,sitePath: '{$this->config['sitePath']}'
            ,jsUrl: '{$this->config['jsUrl']}{$ctx}'
            ,cssUrl: '{$this->config['cssUrl']}{$ctx}'
            ,connectorUrl: '{$this->config['connectorUrl']}'
            ,imageSourceId: '{$this->config['imageSourceId']}'
            ,imageNoPhoto: '{$this->config['imageNoPhoto']}'
            ,imageCachePath: '{$this->config['imageCachePath']}'
        };
    ");
		$this->addHtml('<script type="text/javascript">'.$config_js.'</script>');
*/


		parent::initialize();
	}


	/**
	 * @return array
	 */
	public function getLanguageTopics() {
		return array('awesomevideos:default');
	}


	/**
	 * @return bool
	 */
	public function checkPermissions() {
		return true;
	}
}


/**
 * Class IndexManagerController
 */
class IndexManagerController extends awesomeVideosMainController {

	/**
	 * @return string
	 */
	public static function getDefaultController() {
		return 'home';
	}
}