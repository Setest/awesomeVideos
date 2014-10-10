<?php

/**
 * The home manager controller for awesomeVideos.
 *
 */
class awesomeVideosHomeManagerController extends awesomeVideosMainController {
	/* @var awesomeVideos $awesomeVideos */
	public $awesomeVideos;


	/**
	 * @param array $scriptProperties
	 */
	public function process(array $scriptProperties = array()) {
	}


	/**
	 * @return null|string
	 */
	public function getPageTitle() {
		return $this->modx->lexicon('awesomevideos');
	}


	/**
	 * @return void
	 */
	public function loadCustomCssJs() {
		$this->addCss($this->awesomeVideos->config['cssUrl'] . 'mgr/main.css');
		$this->addCss($this->awesomeVideos->config['cssUrl'] . 'mgr/bootstrap.buttons.css');
		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/misc/utils.js');

		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/widgets/items.grid.js');
		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/widgets/playlists.grid.js');
		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/widgets/items.windows.js');
		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/widgets/home.panel.js');
		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/sections/home.js');
		$this->addHtml('<script type="text/javascript">
		Ext.onReady(function() {
			MODx.load({ xtype: "awesomevideos-page-home"});
		});
		</script>');
	}


	// $modx->regClientStartupScript($vidlister->config['jsUrl'].'mgr/widgets/videos.grid.js');
	// $modx->regClientStartupScript($vidlister->config['jsUrl'].'mgr/widgets/playlists.grid.js');
	// $modx->regClientStartupScript($vidlister->config['jsUrl'].'mgr/widgets/home.panel.js');
	// $modx->regClientStartupScript($vidlister->config['jsUrl'].'mgr/sections/index.js');



	/**
	 * @return string
	 */
	public function getTemplateFile() {
		return $this->awesomeVideos->config['templatesPath'] . 'home.tpl';
	}
}