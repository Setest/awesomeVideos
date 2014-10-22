<?php
// set_time_limit(10);
// ini_set("max_execution_time", "600"); // включаем 10 минут на ограничение работы скрипта
// ini_set("max_input_time", "600"); // включаем 10 минут на ограничение работы скрипта
error_reporting(E_ALL ^ E_NOTICE);  ini_set('display_errors', true);

/**
 * Enable an Item
 */

class awesomeVideosPlaylistGetListProcessor extends modObjectGetListProcessor {
	public $objectType = 'awesomeVideosPlaylist';
	public $classKey = 'awesomeVideosPlaylist';
	public $languageTopics = array('awesomevideos');

  public $defaultSortField = 'rank';
  public $defaultSortDirection = 'DESC';

	//public $permission = 'save';


  public function process() {
  	// $this->getTopic=new awesomeVideosItemsGetTopicProcessor($this->modx);
  	return parent::process();
  }

	public function prepareQueryAfterCount(xPDOQuery $c) {
		return $c;
	}

	public function prepareQueryBeforeCount(xPDOQuery $c) {
		if ($query = $this->getProperty('query')) {
			$c->where(array(
				 'playlist:LIKE' => "%$query%"
				,'OR:playlistId:LIKE' => "%$query%"
				,'OR:channel:LIKE' => "%$query%"
				,'OR:channelId:LIKE' => "%$query%"
				,'OR:description:LIKE' => "%$query%"
				,'OR:user:LIKE' => "%$query%"
			));
		}
		// можно добавить поиск по имени пользователя который сделал импорт или изменил запись, но тут надо втыкать join
		// $c->prepare();print "<br />". $c->toSQL();
		return $c;
	}

	public function prepareRow(xPDOObject $object) {
		$data = $object->toArray();

		if ( $this->getProperty('shortInfo') ){

			$ch=($data['channel'])
			? "<span class='awesomevideos-shortInfo'>(Ch: {$data['channel']})</span>"
			: '';

			$data = array(
				'id'=>$data['id'],
				'playlist'=>$data['playlist'].$ch,
			);
		}

		// if ($data['topic']){
			// $data['topic_val']=($res=$this->getTopic->getTopicVal($data['topic']))?$res:'';
		// }
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

}

return 'awesomeVideosPlaylistGetListProcessor';