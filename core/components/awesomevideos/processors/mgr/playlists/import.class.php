<?php
// set_time_limit(10);
// ini_set("max_execution_time", "600"); // включаем 10 минут на ограничение работы скрипта
// ini_set("max_input_time", "600"); // включаем 10 минут на ограничение работы скрипта


/**
 * Enable an Item
 */
class awesomeVideosPlaylistsImportProcessor extends modObjectProcessor {
	public $objectType = 'awesomeVideosPlaylist';
	public $classKey = 'awesomeVideosPlaylist';
	public $languageTopics = array('awesomevideos');
	//public $permission = 'save';


	/**
	 * @return array|string
	 */
	public function process() {
		// сбрасываем весь лог, чтоб в след раз точно ничего не появилось лишнего в консоли
		// $LogTarget = $this->modx->getLogTarget();	// /awesomeVideosimport/
		$logTarget = $this->modx->getLogTarget();
		$topic = $logTarget->subscriptions[0];

		if ( is_object($logTarget) && isset($topic) ){
			$cachePath = $this->modx->getCachePath() . 'registry/mgr';	//  /home/l/ltdsis/copy.sportsreda.ru/public_html/core/cache/
			$topic = $logTarget->subscriptions[0];
			// echo $topic;
			$cachePath.=$topic;
			$connected = $this->modx->cacheManager->deleteTree($cachePath, array('extensions' => array('.msg.php')) );


// второй способ - наверное более правильный:
/*
//clear cache
$paths = array(
    'config.cache.php',
    'sitePublishing.idx.php',
    'registry/mgr/workspace/',
    'lexicon/',
);
$contexts = $modx->getCollection('modContext');
foreach ($contexts as $context) {
    $paths[] = $context->get('key') . '/';
}

$options = array(
    'publishing' => 1,
    'extensions' => array('.cache.php', '.msg.php', '.tpl.php'),
);
if ($modx->getOption('cache_db')) $options['objects'] = '*';
$results= $modx->cacheManager->clearCache($paths, $options);

return $modx->error->success();
 */


			$this->modx->log(modX::LOG_LEVEL_INFO, date('h:i:s').'<br/>Консоль очищена...<br/>');
		}

		if ( $this->loadClass() ) {
			$this->awesomeVideos->importType = 'Playlist';
			$this->awesomeVideos->import();
		}

		$this->modx->log(modX::LOG_LEVEL_INFO, $this->modx->lexicon('awesomeVideos_console_finish'));
		flush();	// нужно освободить поток
		sleep(3);	// и если реакция очень быстрая дать задержку чтобы отобразить ответ в консоли от другого скрипта
		$this->modx->log(modX::LOG_LEVEL_INFO,'COMPLETED');	// эту строку обязательно надо передать в самом конце, так в rtfm написано

		return $this->success();
	}


	/**
	 * Loads awesomeVideos class to processor
	 *
	 * @return bool
	 */
	public function loadClass() {
		$cacheKey=$this->getProperty('cacheKey');
		// $this->modx->log(modX::LOG_LEVEL_INFO,'Time limit: ' . print_r(set_time_limit(100)) );
		$this->modx->log(modX::LOG_LEVEL_INFO,'Cache key: '.$cacheKey);

		$this->modx->log(modX::LOG_LEVEL_INFO,'Loading class');

		if (!empty($this->modx->awesomeVideos) && $this->modx->awesomeVideos instanceof awesomeVideos) {
			$this->awesomeVideos = & $this->modx->awesomeVideos;
		}
		// не понятно... можно обойтись только getService, т.к. класс уже загружен через контроллер, но походу нужно передавать
		// в контроллере параметры логирования. Т.к. второй раз через getService мы получаем только ссылку на имеющийся объект.
		// elseif (!$this->awesomeVideos = & $this->modx->getService('awesomevideos', 'awesomeVideos', $this->modx->getOption('awesomevideos_core_path', null, $this->modx->getOption('core_path') . 'components/awesomevideos/') . 'model/awesomevideos/')){
		else {
			$this->modx->log(modX::LOG_LEVEL_WARN,'Class is not already loaded');
			if ($cacheKey && $config = $this->modx->cacheManager->get('awesomevideos/prep_' . $cacheKey)) {
				$this->modx->log(modX::LOG_LEVEL_INFO,'Loading class with cacheKey');
				if (!class_exists('awesomeVideos')) {require_once $config['modelPath'].'awesomeVideos/awesomeVideos.class.php';}
				$config['log']['log_target']=$this->modx->getLogTarget();	// иначе в консоль ничего не попадет
				$config['log']['log_level']='LOG_LEVEL_INFO';
				$this->awesomeVideos = new awesomeVideos($this->modx, $config);
			}elseif( $classPath= MODX_CORE_PATH.'components/awesomeVideos/model/awesomeVideos/awesomeVideos.class.php' && file_exists($classPath) ){
				if (!class_exists('awesomeVideos')) {require_once $classPath;}
				$this->awesomeVideos = new awesomeVideos($this->modx, array());
			}else{
				$classPath= MODX_CORE_PATH.'components/awesomeVideos/model/awesomeVideos/awesomeVideos.class.php';
				$this->modx->log(modX::LOG_LEVEL_ERROR,'Can`t load main class2 '.$classPath);
				return false;
			}

		}
		$this->modx->log(modX::LOG_LEVEL_INFO,'Class is loaded');

		return $this->awesomeVideos instanceof awesomeVideos;
	}

}

return 'awesomeVideosPlaylistsImportProcessor';