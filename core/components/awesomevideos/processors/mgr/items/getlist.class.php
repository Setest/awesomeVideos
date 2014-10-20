<?php
// set_time_limit(10);
// ini_set("max_execution_time", "600"); // включаем 10 минут на ограничение работы скрипта
// ini_set("max_input_time", "600"); // включаем 10 минут на ограничение работы скрипта
error_reporting(E_ALL ^ E_NOTICE);  ini_set('display_errors', true);

/**
 * Enable an Item
 */

require_once 'gettopic.class.php';

// class awesomeVideosItemsGetTopicOnlyProcessor extends awesomeVideosItemsGetTopicProcessor {

// }

class awesomeVideosItemsGetListProcessor extends modObjectGetListProcessor {
	public $objectType = 'awesomeVideosItem';
	public $classKey = 'awesomeVideosItem';
	public $languageTopics = array('awesomevideos');

  public $defaultSortField = 'rank';
  public $defaultSortDirection = 'DESC';

	//public $permission = 'save';


  public function process() {
  	$this->getTopic=new awesomeVideosItemsGetTopicProcessor($this->modx);
  	return parent::process();
  }

	public function prepareQueryAfterCount(xPDOQuery $c) {
		return $c;
	}
	public function prepareQueryBeforeCount(xPDOQuery $c) {

// var_dump($this->getProperty('sort'));

// 		if (!$this->getProperty('sort')) {
// 			$this->setProperty('dir', 'DESC');
// 			$this->setProperty('sort', 'rank');
// 		}

		if ($query = $this->getProperty('query')) {
			$c->where(array(
				 'name:LIKE' => "%$query%"
				,'OR:source_detail:LIKE' => "%$query%"
				,'OR:videoId:LIKE' => "%$query%"
				,'OR:channelId:LIKE' => "%$query%"
				,'OR:description:LIKE' => "%$query%"
				,'OR:keywords:LIKE' => "%$query%"
				,'OR:author:LIKE' => "%$query%"
			));
		}
		// можно добавить поиск по имени пользователя который сделал импорт или изменил запись, но тут надо втыкать join

	  // if (empty($sortKey)) $sortKey = $this->getProperty('sort');
	  // $c->sortby($sortKey,$this->getProperty('dir'));

		// $c->prepare();print "<br />". $c->toSQL();
		return $c;
	}

	public function prepareRow(xPDOObject $object) {
		$data = $object->toArray();

		if ($data['topic']){
			$data['topic_val']=($res=$this->getTopic->getTopicVal($data['topic']))?$res:'';
		}
		// $resources = & $this->resources;
		// if (!array_key_exists($comment['resource'], $resources)) {
		// 	if ($resource = $this->modx->getObject('modResource', $comment['resource'])) {
		// 		$resources[$comment['resource']] = array(
		// 			'resource_url' => $this->modx->makeUrl($comment['resource'],'','','full')
		// 			,'pagetitle' => $resource->get('pagetitle')
		// 		);
		// 	}
		// }
		// if (!empty($resources[$comment['resource']])) {
		// $comment['text'] = strip_tags(html_entity_decode($comment['text']));

		// $comment['created'] = $this->formatDate($comment['created']);
		// $comment['updated'] = $this->formatDate($comment['updated']);


		return $data;
	}


	public function formatDate($date = '') {
		if (empty($date) || $date == '0000-00-00 00:00:00') {
			return $this->modx->lexicon('no');
		}
		// return strftime('%d %b %Y %H:%M', strtotime($date));
		return strftime('%d %b %Y %H:%M', $date);
	}

}

return 'awesomeVideosItemsGetListProcessor';