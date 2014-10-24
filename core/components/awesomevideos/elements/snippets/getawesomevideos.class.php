<?php
/** @var array $scriptProperties */
/** @var awesomeVideos $awesomeVideos */

error_reporting(E_ALL ^ E_NOTICE);ini_set('display_errors', true);
// $modx->setLogLevel(modX::LOG_LEVEL_INFO);
// $modx->setLogTarget('ECHO');

echo "<pre>";

// print_r(get_declared_classes());


// require_once '../../awesomevideoshelper.class.php';
require_once dirname(dirname(dirname(__FILE__))).'/model/awesomevideos/awesomevideoshelper.class.php';



// class getawesomevideos extends modProcessor{	// для этого нужно заранее его загрузить см. G:\Работа\sportsreda.ru\2014-05-28 проект SpoRT\project\core\components\modxsite\model\modxsite\modxsite.class.php.
class getawesomevideos extends awesomeVideosHelper{
	public $objectType = 'awesomeVideosPlaylist';
	public $classKey = 'awesomeVideosPlaylist';
	public $languageTopics = array('awesomevideos');
	//public $permission = 'save';



  /**
   * @param modX &$modx A reference to the modX object
   * @param array $config An array of configuration options
   */
	function __construct(modX &$modx, array $config = array()) {
		$this->modx =& $modx;

		$this->config = array_merge(array(
			'corePath' => $this->modx->getOption('awesomevideos_core_path', null, $this->modx->getOption('core_path') . 'components/awesomevideos/'),
      'log' => array(
      	'status'=>$this->modx->getOption('log', $config, true),
      	'log_placeholder'=>$this->modx->getOption('log_placeholder', $config, false),
      	'log_detail'=>$this->modx->getOption('log_detail', $config, false),
				// 'log_target' => $this->modx->getOption('log_target', $config, $this->modx->getOption('log_target', null) ),
				'log_target' => 'FILE',
        'log_level'=>$this->_getModxConst($this->modx->getOption('log_level', $config, 'LOG_LEVEL_ERROR')),  // INFO, WARN, ERROR, FATAL, DEBUG
      ),

		), $config);

    if ($this->config['log']['status']==true){
      $this->modx->message = null;
      // $this->modx->log(modX::LOG_LEVEL_INFO,'Установленный уровень отладки0: '.print_r($this->config['log'], true) );
      // $this->modx->log(modX::LOG_LEVEL_INFO,'Установленный уровень отладки1: '.$this->config['log']['log_level']);
      // $this->modx->log(modX::LOG_LEVEL_INFO,'Установленный уровень отладки: '.$this->modx->getLogLevel());
      $this->modx->setLogLevel( $this->_getModxConst($this->config['log']['log_level']) );
      // $this->modx->log(modX::LOG_LEVEL_ERROR,'ПОСЛЕ: '.$this->modx->getLogLevel());


      // var_dump($this->config['log']['log_target']);

      if ($this->config['log']['log_target']){
        $date = date('Y-m-d__H-i-s');  // использовать в выводе даты : - нельзя, иначе не создается лог в файл
        $logFileName="{$this->config['log']['log_filename']}_$date.log";
        $this->modx->setLogTarget(array(
           'target' => $this->config['log']['log_target'],
           'options' => array('filename' => $logFileName )
        ));
      }

      if ($this->config['log']['log_target']=="FILE"){
        $this->result['log_filename']=$logFileName;
        $this->result['log_fullPath']=$this->config['corePath']."cache/logs/{$logFileName}";
        $this->result['log_urlPath']=$this->config['siteUrl']."core/cache/logs/{$logFileName}";
      }

      $this->writeLog("ModX version:".$modx->getOption('settings_version'));


      if ($this->config['log']['log_detail']){
        $log_detail=debug_backtrace();  // этот вывод жрет ООООЧЕНЬ много памяти
                        // и при малом таймауте возможно даст 500-ю ошибку
        $this->writeLog("PHP version: ".PHP_VERSION);
        $this->writeLog("Server API: ".PHP_SAPI);
        $this->writeLog("Loaded modules: \n\n".print_r(get_loaded_extensions(),true)."\n");
        $this->writeLog("Run command: \n\n{$log_detail[2][object]->_tag}\n");
        $this->writeLog("Properties: \n".print_r($log_detail[2][object]->_properties,true));
        $this->writeLog("Loaded config: \n\n".print_r($this->config,true )."\n");
        unset($log_detail);
      }
    }

    // $this->setProperties($properties);

		// $this->modx->switchContext($this->_config['ctx']);

  }

	private function _setClassKey() {
		switch (strtolower($this->config['part'])){
			case 'item':
			case 'video':
				$classKey = 'awesomeVideosItem';
			break;
			default:
				$classKey=$this->classKey;
			break;
		}
		$this->classKey=$this->objectType=$classKey;
	}

	public function process() {
		if ( $this->loadClass() ) {
			$awesomeVideos=&$this->awesomeVideos;
			// $this->writeLog=&$this->awesomeVideos->writeLog;
			// $this->writeLog=call_user_func(array($awesomeVideos, 'writeLog'));
			// var_dump($this->awesomeVideos->writeLog());

			$this->writeLog('TEST: ', microtime(true) - $time, 'ERROR');

			$awesomeVideos->initialize();	// подгрузит JS и CSS по необходимости

			$this->_setClassKey();


			// $where = $this->createCriteria();
			// $instance->setConfig($config, true);
			$this->makeQuery();
			// $instance->addTVFilters();
			// $instance->addTVs();
			// $instance->addJoins();
			// $instance->addGrouping();
			// $instance->addSelects();
			$this->addWhere();
			// $instance->addSort();
			// $instance->prepareQuery();
			// делаем запрос getData() в котором проходимся по всем записям и делаем $rows = $this->prepareRows($rows);
			// оборачиваем все во wrap (или emptywrap)
			// закидываем в ph если так требует конфиг
			// формируем выдачу если это ajax то возвращаем json, иначе просто return $output.


			// $result=array(
			// 	"paging" : $this->awesomeVideos->createPageNavigation(); // если пагинация стандартная то нам надо будет ее запихать в
			// 																													 // Ph-r и когда будем парсить data она автоматом подставится
			// 																													 // во всех остальных случаях ее подстановкой будет заниматься js
			// 	"data" : $this->awesomeVideos->getObjects($part,$where);
			// );


			$result = $this->getData();
			// print_r($result['results']);


			echo "OK";
			// $this->awesomeVideos->importType = 'Playlist';
			// $this->awesomeVideos->import();
		}
		// return $this->success();
	}

	/**
	 * Create object with xPDOQuery
	 */
	public function makeQuery() {
		// $time = microtime(true);
		$this->query = $this->modx->newQuery($this->classKey);
		// $this->addTime('xPDO query object created', microtime(true) - $time);
	}

	/**
	 * Adds where and having conditions
	 */
	public function addWhere() {
		$time = microtime(true);
		$where = array();
		if (!empty($this->config['where'])) {
			$tmp = $this->config['where'];
			if (is_string($tmp) && ($tmp[0] == '{' || $tmp[0] == '[')) {
				$tmp = $this->modx->fromJSON($tmp);
			}
			if (!is_array($tmp)) {
				$tmp = array($tmp);
			}
			// $where = $this->replaceTVCondition($tmp);
		}
		// $where = $this->additionalConditions($where);
		if (!empty($where)) {
			$this->query->where($where);

			$this->writeLog('WHERE: '.print_r($where, true), microtime(true) - $time);

			$condition = array();
			foreach ($where as $k => $v) {
				if (is_array($v)) {
					if (isset($v[0])) {
						$condition[] = is_array($v) ? $k.'('.implode(',',$v).')' : $k.'='.$v;
					}
					else {
						foreach ($v as $k2 => $v2) {
							$condition[] = is_array($v2) ? $k2.'('.implode(',',$v2).')' : $k2.'='.$v2;
						}
					}
				}
				else {$condition[] = $k.'='.$v;}
			}
			$this->writeLog('Added where condition: <b>' .implode(', ',$condition).'</b>', microtime(true) - $time);
		}
		$time = microtime(true);
		if (!empty($this->config['having'])) {
			$tmp = $this->config['having'];
			if (is_string($tmp) && ($tmp[0] == '{' || $tmp[0] == '[')) {
				$tmp = $this->modx->fromJSON($tmp);
			}
			// $having = $this->replaceTVCondition($tmp,true);
			// $this->writeLog('Having: ' .print_r($having, true), microtime(true) - $time);
			// // $this->query->having($having);
			// $this->query->query['having']=$this->simpleParseConditions($having);
			// // $this->query->having(array('1'=>'1'));

			// $condition = array();
			// foreach ($having as $k => $v) {
			// 	if (is_array($v)) {$condition[] = $k.'('.implode(',',$v).')';}
			// 	else {$condition[] = $k.'='.$v;}
			// }
			// $this->writeLog('Added having condition: <b>' .implode(', ',$condition).'</b>', microtime(true) - $time);
		}
		$this->writeLog('WHERE2: '.print_r($this->query->query, true), microtime(true) - $time);
	}



    /**
     * Get the data of the query
     * @return array
     */
    public function getData() {
        $data = array();
        // $limit = intval($this->getProperty('limit'));
        // $start = intval($this->getProperty('start'));

        /* query for chunks */
        $c = $this->modx->newQuery($this->classKey);
        // $c = $this->prepareQueryBeforeCount($c);
        $data['total'] = $this->modx->getCount($this->classKey,$c);
        // $c = $this->prepareQueryAfterCount($c);

        // $sortClassKey = $this->getSortClassKey();
        // $sortKey = $this->modx->getSelectColumns($sortClassKey,$this->getProperty('sortAlias',$sortClassKey),'',array($this->getProperty('sort')));
        // if (empty($sortKey)) $sortKey = $this->getProperty('sort');
        // $c->sortby($sortKey,$this->getProperty('dir'));
        // if ($limit > 0) {
        //     $c->limit($limit,$start);
        // }

        // echo $this->classKey;

        $data['results'] = $this->modx->getCollection($this->classKey,$c);
        return $data;
    }

	/**
	 * Loads awesomeVideos class to processor
	 *
	 * @return bool
	 */
	public function loadClass() {

		$classPath=$this->config['corePath'].'model/awesomevideos/';
		if ( !$this->awesomeVideos = $this->modx->getService('awesomeVideos','awesomeVideos', $this->config['corePath'].'model/awesomevideos/', $this->config) ){
			return false;
		}

		// print_r($this->modx->map);
		// print_r($this->modx->packages);
		print_r($this->config);
		// print_r($this->awesomeVideos->config);

		// echo $classPath;

		// print_r(get_class_methods($this->awesomeVideos));

		return true;
	}

}