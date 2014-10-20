<?php
// set_time_limit(10);
// ini_set("max_execution_time", "600"); // включаем 10 минут на ограничение работы скрипта
// ini_set("max_input_time", "600"); // включаем 10 минут на ограничение работы скрипта

error_reporting(E_ALL ^ E_NOTICE);  ini_set('display_errors', true);

/**
 * Enable an Item
 */
class awesomeVideosItemsGetTopicProcessor extends modObjectGetListProcessor {
	public $objectType = 'modResource';
	public $classKey = 'modResource';
	public $languageTopics = array('awesomevideos');
	public $defaultSortField = 'id';
	public $topicParams = array();
	public $topics = array();	// массив всех топиков, чтоб не запускать весь процесс несколько раз при вызове getTopicVal

  /**
   * {@inheritDoc} Возвращает значение топика.
   * @return mixed
   */
  public function getTopicVal($id=null) {
  	if (!$id) return false;
  	$id = (string)$id;

  	if (!$this->topics){
  		if (!$this->initialize() or !$this->process()){
  			return false;
  		}
  	}
  	return $this->topics[$id];
  }


  /**
   * {@inheritDoc}
   * @return mixed
   */
  public function process() {
  	$this->topicParams=trim($this->modx->getOption('awesomeVideos.video.topic.params',null,array()));
		if (!is_array($this->topicParams = $this->modx->fromJSON( $this->topicParams ))) $this->topicParams=array();

  	$this->setProperty('maxIterations', intval($this->modx->getOption('parser_max_iterations', null, 10)));
		// $this->setProperty('type',false);
		$this->setProperty('topicTpl',$this->modx->getOption('awesomeVideos.video.topic.tpl',null,''));
		$this->setProperty('topicTplId',$this->modx->getOption('awesomeVideos.video.topic.tplId',null,''));
  	$result=array();
  	$topicSource=trim($this->modx->getOption('awesomeVideos.video.topic.source',null,false));
  	if ($topicSource){
  		// var_dump($topicSource);
  		// проверяем является ли он сниппетом или JSON-ом
  		if (strpos($topicSource, '[') !== false or strpos($topicSource, '{') !== false ) {
  			$result=$this->modx->fromJSON($topicSource);
			}else{
				// $this->setProperty('type',true);
				// это сниппет, запустим его
				if (!$result=$this->runSnippetDirectly($topicSource)){
					return $this->failure($this->modx->error->getErrors());
				}
			}
			foreach ($result as $key => $value) {
				$this->topics[$value['id']]=$value['topic'];
			}
			return $this->outputArray( $result, count($result) );
  	}

  	// сниппет или локальное хранилище не используется
		if (!empty($this->topicParams)){
			$this->setDefaultProperties( array_merge($this->properties,$this->topicParams) );
		}

  	return parent::process();
  }

  /**
   * Стартуем сниппет напрямую, чтоб можно было получить данные также в виде array
   *
   * @param int|string $snippet	имя или id сниппета
   * @return mixed
   */
  public function runSnippetDirectly($snippet) {
		// получаем сниппет
		// var_dump($this->modx->getOption('awesomeVideos.video.topic.tplparse',null,null));
		// $this->modx->error->addError("Пробная ошибка");	return false;
		$type = (is_numeric($snippet))? 'id':'name';
		if($s = $this->modx->getObject('modSnippet', array(
			$type => $snippet
		))){

			if($f = $s->getScriptName() and !function_exists($f) and $s->loadScript())	$s->setCacheable(false);
			// получим параметры сниппета по-умолчанию и передадим данные в сниппет
			$result=$f( array_merge($s->getProperties(),$this->topicParams) );

			// print_r($this->topicParams);
			// return;

			if ( is_string($result) && (strpos($result, '[') !== false or strpos($result, '{') !== false)){
				// удалим возможные лишние запятые, проверим правильность открытия и закрытия скобок
				// т.к. мы можем получим JSON но с лишней запятой на конце нужно это учесть.
				$pat=array('/^({)/', '/(\,\])$/', '/(\}\,|\})$/');
				$rep=array('[{',']','}]');
				$result = preg_replace($pat, $rep, $result);
				// $result = $this->modx->fromJSON( $result );
				$result = json_decode($result,true);
			}
			// print_r($result);
			if (is_array($result)) {
				if ( $this->modx->getOption('awesomeVideos.video.topic.tplparse',null,null) ){

					$list=array();
	        foreach ($result as $object) {
          	$list[]=$this->prepareRow($object);
	        }
					$result=$list;
				}
				return $result;
			}
			else {
				$this->modx->error->addError("Не удалось распознать результат работы сниппета. Проверяйте ответ от сервера.");
				return false;
			}

		}else{
			$this->modx->error->addError("Не удалось загрузить сниппет: {$snippet}");
			return false;
		}
	}

	public function prepareQueryBeforeCount(xPDOQuery $c) {
		$where=array();
		if ( $where = $this->getProperty('where') ){
			if (strpos($where, '[') !== false or strpos($where, '{') !== false ) {
				$where = $this->modx->fromJSON($where);
			}
		}
		$c->where($where);
		return $c;
	}

	public function getProcessData($param = array(), $template = null)
	{
		$template=!$template?$this->getProperty('topicTpl'):$template;
		$this->modx->getParser();
		$this->modx->toPlaceholders($param);
	  $this->modx->parser->processElementTags('', $template, true, false, '[[', ']]', array(), $this->getProperty('maxIterations'));
	  $this->modx->parser->processElementTags('', $template, true, true, '[[', ']]', array(), $this->getProperty('maxIterations'));
		return $template;
	}

	// public function prepareRow(xPDOObject $object) {
	public function prepareRow($object) {
		// $data = gettype($object)=='object' ? $object->toArray() : $object;
		$data = $object instanceof xPDOObject ? $object->toArray() : $object;

		// if ($this->classKey == 'modResource' && !$this->getProperty('type')){
			return (array(
				'id'=>$this->getProcessData($data,$this->getProperty('topicTplId')),
				'topic'=>$this->getProcessData($data)
			));
		// }

		// return $comment;
	}

}

return 'awesomeVideosItemsGetTopicProcessor';