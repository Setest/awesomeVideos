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

		$modx23 = !empty($this->modx->version) && version_compare($this->modx->version['full_version'], '2.3.0', '>=');
		$this->modx->controller->addHtml('<script type="text/javascript">
			Ext.onReady(function() {
				MODx.modx23 = '.(int)$modx23.';
			});
		</script>');
		if (!$modx23) {
			$this->addCss($this->awesomeVideos->config['cssUrl'] . 'mgr/font-awesome.min.css');
		}

		// подгружаем основные скрипты, без них форма редактирования не откроется т.к. использует TV параметры
		// $managerUrl = $this->modx->getOption('manager_url', MODX_MANAGER_URL, $this->modx->_userConfig);
		$mgrUrl = $this->modx->getOption('manager_url',null,MODX_MANAGER_URL);

		// $this->addJavascript($mgrUrl.'assets/modext/util/utilities.js');
		$this->addJavascript($mgrUrl.'assets/modext/util/datetime.js');
		$this->addJavascript($mgrUrl.'assets/modext/widgets/element/modx.panel.tv.renders.js');

		// $this->addJavascript($mgrUrl.'assets/modext/widgets/resource/modx.grid.resource.security.js');
		// $this->addJavascript($mgrUrl.'assets/modext/widgets/resource/modx.panel.resource.tv.js');
		// $this->addJavascript($mgrUrl.'assets/modext/widgets/resource/modx.panel.resource.js');


		// подгружаем все остальное, что нам нужно
		$this->addCss($this->awesomeVideos->config['cssUrl'] . 'mgr/main.css');
		$this->addCss($this->awesomeVideos->config['cssUrl'] . 'mgr/rowEditor.css');
		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/misc/utils.js');
		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/misc/rowEditor.js');

		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/widgets/items.grid.js');
		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/widgets/playlists.grid.js');
		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/widgets/items.windows.js');
		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/widgets/home.panel.js');
		$this->addJavascript($this->awesomeVideos->config['jsUrl'] . 'mgr/sections/home.js');

		// $modx->regClientStartupHTMLBlock('<div id="tvslist"></div>');

		$this->addHtml('<script type="text/javascript">
		Ext.onReady(function() {
			MODx.load({ xtype: "awesomevideos-page-home"});
		});
		</script>');

		/* load RTE */
		// $this->loadRichTextEditor();

		// migx
		$onRichTextEditorInit = $this->loadRichTextEditor();
		$this->addHtml($onRichTextEditorInit);

	}


  /**
   * Initialize a RichText Editor, if set
   *
   * @return void
   */
  public function loadRichTextEditor() {
      /* register JS scripts */

      $rte = isset($this->scriptProperties['which_editor']) ? $this->scriptProperties['which_editor'] : $this->modx->getOption('which_editor', '', $this->modx->_userConfig);
      $this->setPlaceholder('which_editor', $rte);

      /* Set which RTE if not core */
      if ($this->modx->getOption('use_editor', false, $this->modx->_userConfig) && !empty($rte)) {
          /* invoke OnRichTextEditorRegister event */
          $textEditors = $this->modx->invokeEvent('OnRichTextEditorRegister');
          $this->setPlaceholder('text_editors', $textEditors);

          $this->rteFields = array('ta');
          $this->setPlaceholder('replace_richtexteditor', $this->rteFields);

          /* invoke OnRichTextEditorInit event */
          //$resourceId = $this->resource->get('id');
          $onRichTextEditorInit = $this->modx->invokeEvent('OnRichTextEditorInit', array(
              'editor' => $rte,
              'elements' => $this->rteFields,
              //'id' => $resourceId,
              //'resource' => &$this->resource,
              //'mode' => !empty($resourceId) ? modSystemEvent::MODE_UPD : modSystemEvent::MODE_NEW,
              ));

          if (is_array($onRichTextEditorInit)) {
              $onRichTextEditorInit = implode('', $onRichTextEditorInit);

              $this->setPlaceholder('onRichTextEditorInit', $onRichTextEditorInit);
              return $onRichTextEditorInit;
          }
      }
  }


	/**
	 * @return string
	 */
	public function getTemplateFile() {
		return $this->awesomeVideos->config['templatesPath'] . 'home.tpl';
	}
}