<?php
// error_reporting(E_ALL ^ E_NOTICE);  ini_set('display_errors', true);
class awesomeVideosItemUpdateProcessor extends modObjectUpdateProcessor {
	public $objectType = 'awesomeVideosItem';
	public $classKey = 'awesomeVideosItem';
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

	public function afterSave() {

		if ($chosen = $this->getProperty('chosen')){
			// ИЗБРАННЫМ может быть только текущий объект!!! таковы правила проекта.
			$c = $this->modx->newQuery($this->classKey);
			$c->command('update');
			$c->set(array(
				'chosen'  => 0
			));
			$c->where(array(
				'id:!='    => (int)$this->getProperty('id'),
			));

			if (!$stmt = $c->prepare() or !$stmt->execute())
			{
				$error=$stmt->errorInfo();
				return $this->modx->error->failure($error[2]." in query: ".$c->toSQL());
			}
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
		$name = trim($this->getProperty('name'));
		$videoId = trim($this->getProperty('videoId'));
		if (empty($id)) {
			return $this->modx->lexicon('awesomeVideos_item_err_ns');
		}

		if (empty($videoId)) {
			$this->modx->error->addField('videoId', $this->modx->lexicon('awesomeVideos_item_err_videoId'));
		}else{
			// проверяем на youtube существует ли такой ролик
			// if ( $videoInfo=$this->awesomeVideos->getVideoByIdsFromYouTube(array('id'=>$videoId)) ){
			if ( !$videoInfo=$this->awesomeVideos->getVideoByIdsFromYouTube(array('id'=>$videoId)) OR empty($videoInfo['items'])
			){
				return $this->modx->lexicon('awesomeVideos_item_err_videoIdNotExist',array('id'=>$videoId));
			}
		}

		if (empty($name)) {
			$this->modx->error->addField('name', $this->modx->lexicon('awesomeVideos_item_err_name'));
		}
		elseif ($this->modx->getCount($this->classKey, array('name' => $name, 'id:!=' => $id))) {
			$this->modx->error->addField('name', $this->modx->lexicon('awesomeVideos_item_err_ae'));
		}

		// $video->setProperties($scriptProperties);
		unset($this->properties['tvs']);

		$tvlist=array();
		foreach ($this->getProperties() as $key => $val) {
			if (substr($key, 0, 2)=="tv" && strpos($key, "tvbrowser")===false){
				$tvlist[(int)substr($key, 2)]=$val;
				unset($this->properties[$key]);
			}
			if (strpos($key, "tvbrowser")) unset($this->properties[$key]);
		}

		// 2. если есть TV, получим имя каждого из значений и то как это значение должно выглядеть.
		// Например нам пришли данные без разделитея, а нам надо сохранить их в правильном формате, ведь мы не храним
		// их в главной таблице TV.

		// print_r($this->object->parseTVS($tvlist));
		$tvsParse=$this->object->parseTVS($tvlist);
		if ($tvsParse) $this->setProperty('tvdata',$this->toJSON($tvsParse));

		// $this->modx->error->addField('name', 'Временная ошибка');

		return parent::beforeSet();
	}

  /**
   * {@inheritDoc}
   * @return mixed
   */
  public function process() {
  	// подгрузим основной класс
  	// print_r($this->loadClass());

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

return 'awesomeVideosItemUpdateProcessor';