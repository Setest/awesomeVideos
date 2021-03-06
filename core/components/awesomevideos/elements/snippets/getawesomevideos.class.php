<?php

error_reporting(E_ALL ^ E_NOTICE);ini_set('display_errors', true);

require_once dirname(dirname(dirname(__FILE__))).'/model/awesomevideos/awesomevideoshelper.class.php';

// class getawesomevideos extends modProcessor{	// для этого нужно заранее его загрузить см. G:\Работа\sportsreda.ru\2014-05-28 проект SpoRT\project\core\components\modxsite\model\modxsite\modxsite.class.php.
class getawesomevideos extends awesomeVideosHelper{
	public $req_var = 'q';
	private $isParent = false;	// обозначает родительский сниппет, все дочерние будут иметь false
	public $sessionStoreName = 'awesomeVideos';
	public $objectType = 'awesomeVideosPlaylist';
	public $classKey = 'awesomeVideosPlaylist';
	public $languageTopics = array('awesomevideos');


  /**
   * @param modX &$modx A reference to the modX object
   * @param array $config An array of configuration options
   */
	function __construct(modX &$modx, array $config = array(), $function_name = 'getAwesomeVideos') {
		$this->modx =& $modx;

// var_dump(htmlspecialchars($config['topic']));

		// получаем имя или id сниппета из которого вызвали текущий класс
		$this->snippet_name = str_replace('elements_modsnippet_', '', $function_name);

		$this->config = array_merge(array(
			'corePath' => $this->modx->getOption('awesomevideos_core_path', null, $this->modx->getOption('core_path') . 'components/awesomevideos/'),
      'log' => array(
      	'log_status'      => isset($config['log_status'])   ? $config['log_status']      : true,
      	'log_isstyled'    => isset($config['log_isstyled']) ? $config['log_isstyled']    : true,
      	'log_placeholder' => $config['log_placeholder']     ? $config['log_placeholder'] : 'aw_log',
      	'log_detail'      => isset($config['log_detail'])   ? $config['log_detail']      : false,
      	'log_target'      => $config['log_target']          ? $config['log_target']      : 'HTML',  // HTML, ECHO, FILE, PLACEHOLDER, SYSTEM || AUTO
      	'log_level'       => $config['log_level']           ? $config['log_level']       : 'INFO',  // INFO, WARN, ERROR, FATAL, DEBUG
      ),

      'ajax'=> !isset($config['ajax'])
      				|| (isset($config['ajax']) && ($config['ajax']==1 || $config['ajax']==true || strtolower($config['ajax'])=='true')) ? true : false,	// позволяет переходить по ссылкам посредством ajax без обновления страницы
      'bindHistory'=>isset($config['bindHistory'])?$config['bindHistory']:true,

      'fastMode'=>isset($config['fastMode'])?!$config['fastMode']:false,

      'addDataToUrl'=>$config['addDataToUrl'] ? $config['addDataToUrl'] : false, // доп. параметры в виде JSON которые идут в URL

      'page'=> $config['page'] ? (int)$config['page'] : 1,

      'where'=> $config['where'] ? $config['where'] : '',
      'topic'=> isset($config['topic']) && strlen($config['topic'])>0 ? $config['topic'] : false,
      // 'topic'=> false,
      'part'=> $config['part'] ? $config['part'] : '',
      // 'return_type'=> $config['return_type'] ? $config['return_type'] : '',

			'direct' => $config['direct'] ? true : false,	// может быть true только при прямом вызове например из ajax

			'limit' => 2,
			'sortby' => '',
			'sortdir' => '',
			'groupby' => '',
			'totalVar' => 'total',
			'offsetVar' => 'offset',
			'sqlQuery' => 'sqlQuery',
			'tpl' => '',
			'return' => 'chunk',	// chunk, data, ids, json

			'select' => '',
			'leftJoin' => '',
			'rightJoin' => '',
			'innerJoin' => '',

			'includeTVs' => '',
			'tvPrefix' => '',
			'tvsJoin' => array(),
			'tvsSelect' => array(),

		));
    $this->logConfig($this->config['log']);
    // unset ($config['topic']);

		// unset($config['addDataToUrl'],$config['fastMode'],$config['log_status'],$config['log_isstyled'],$config['log_placeholder'],$config['log_detail'],$config['log_target'],$config['log_level']);
		// $this->config = array_merge($this->config, $config);

		// unset($config['log_status'],$config['log_isstyled'],$config['log_placeholder'],$config['log_detail'],$config['log_target'],$config['log_level']);
		// unset($config['log'],$config['log_isstyled'],$config['log_placeholder'],$config['log_detail'],$config['log_target'],$config['log_level']);

// echo "<pre>";

		$key = md5(uniqid(mt_rand(), true));
		// echo rand(1,1000);

		// не знаю есть ли в этом нужда по идее modx уже проинициализирован и быть может уже прогнал все что нужно.
		// $this->modx->sanitize($this->config);
		$this->modx->sanitize($_GET);
		$this->modx->sanitize($_REQUEST);

		$getProp = array();
		if ( !$this->modx->getPlaceholder('aw_isParent') ){
			if ( !$config['direct'] ){

				// сливаем с параметрами $_GET сработает только у сниппета являющемся родителем. Т.е. если есть вложенные сниппеты
				// getawesomevideos в них параметры GET не передадутся.
// echo "echo PARENT";
				// $allowed = array('id','ids','parentIds','part','page', 'limit', 'where', 'offset', 'setOfProperties');
				$allowed = array('id','ids','parentIds','part','page', 'limit', 'offset', 'setOfProperties');
				if ($getProp = array_intersect_key($_GET, array_flip($allowed))){
// echo "<pre>".print_r($getProp,true)."</pre>";
					$config = array_merge($config, $getProp);
				}
			}

			$this->isParent = true;
			$this->modx->setPlaceholder('aw_isParent', true);
		}


		// получаем набор параметров
		$setOfProperties=$this->getSetOfProperties($config['setOfProperties']);
		// исключаем из конфига, который пришел к нам в качестве scriptProperties, данные имеющиеся в наборе параметров
		// при условии, что их нет в переданных параметрах.

		if (!$snippetProperties=$this->getSessionStore($this->snippet_name)){
			$snippetProperties=$this->getSnippetProperties($this->snippet_name);
			$this->setSessionStore($this->snippet_name, $snippetProperties, 60 );
		}

		// вытащим параметры которые были переданы напрямую в сниппет
		$snippetStartProperties =  array_diff_key($config,$snippetProperties);
		$this->config['snippetStartProperties'] = $snippetStartProperties;

// echo "<pre>";print_r($config);die();
		 $this->config = !$config['direct']
		 	? array_merge($this->config, $config, $setOfProperties, $snippetStartProperties, $getProp)
		  : array_merge($this->config, $snippetProperties, $setOfProperties, $config)
		;

		if ( $config['direct'] ) {

			if ( $config['key'] && $tmp=$this->getSessionStore($config['key'], 'config') ){
				$key = $config['key'];
		    $this->config = array_merge($tmp, $config);
			}

			if ($resourceId=$this->getSessionStore('resource-id')){
			  $this->modx->resource = $this->modx->getObject('modResource', $resourceId);
			}

		}else{

		  if ( !isset($resourceId) && $this->modx->resource ){
		  	$resourceId = $this->modx->resource->get('id');
			  $this->setSessionStore('resource-id', $resourceId, 0 ); // иначе getPage не сработает
			}
		}
    $this->config['part'] = strtolower($this->config['part']);

		$this->config['key'] = $key;

	  $this->req_var = $this->modx->getOption('request_param_alias', null, 'q');


		// $this->unsetSessionStore('baseLink');
		if (!$this->config['baseLink']=$this->getSessionStore('baseLink')){
			$this->config['baseLink']=$this->makeLink();
			$this->setSessionStore('baseLink', $this->config['baseLink'], 0 );
		}

		$add = array();
		if ($this->config['parentIds'] && in_array($this->config['part'], array('video', 'item')) ) {
			$add = array(
				'parentIds' => (is_array($this->config['parentIds']))?implode(',', $this->config['parentIds']):$this->config['parentIds']
			);
		}

		// строим основной URI
		$this->config['mainUri'] = $this->makeLink( array_merge($add, array(
			'part'=>$this->config['part'],
			'id'=>$this->config['id'],
			'setOfProperties'=>$this->config['setOfProperties'],
			'where'=>$this->config['where'],
		)), 1, $this->config['baseLink']['baseUrl']);	// был href

		// if ( $this->config['pagination'] == 'snippet' || $this->config['pagination'] == 'scroll' ){
		if ( $this->config['pagination'] == 'snippet' ){
			$this->config['offset'] = $this->config['limit'] * ( ($this->config['page']<=1)?0:$this->config['page']-1 );
			// $this->config['offset'] = $this->config['limit'] * ( ($this->config['page']<=1)?0:$this->config['page'] );
			// unset($_REQUEST['page']);
		}

		$this->idx = !empty($this->config['offset'])
			? (integer) $this->config['offset'] + 1
			: 1;

		// unset($this->config['addDataToUrl'],$this->config['fastMode'],$this->config['log_status'],$this->config['log_isstyled'],$this->config['log_placeholder'],$this->config['log_detail'],$this->config['log_target'],$this->config['log_level']);
		unset($this->config['log_status'],$this->config['log_isstyled'],$this->config['log_placeholder'],$this->config['log_detail'],$this->config['log_target'],$this->config['log_level']);

		// продлеваем данные в сессии
		$this->setSessionStore($this->config['key'], $this->config, 60, 'config' );

// if ($this->config['setOfProperties']=='aw_videos'){
// 	print_r($this->config);
// 	die();
// }

  }

	private function _setClassKey() {
		switch ($this->config['part']){
			case 'item':
			case 'video':
				$classKey = 'awesomeVideosItem';
			break;
			default:
				$classKey=$this->classKey;
			break;
		}
		$pk = $this->modx->getPK($classKey);
		$this->pk = is_array($pk)
			? implode(',', $pk)
			: $pk;
		return $this->classKey=$this->objectType=$classKey;
	}

	public function process() {
		$time = microtime(true);
		if ( $this->loadClass() ) {
			$awesomeVideos=&$this->awesomeVideos;

			$awesomeVideos->initialize();	// подгрузит JS и CSS по необходимости

			$this->config['class'] = $this->_setClassKey();

			// $instance->setConfig($config, true);
			$this->makeQuery();
			// $instance->addTVFilters();
			// $instance->addTVs();
			$this->addJoins();
			$this->addSelects();
			$this->addWhere();
			$this->addSort();
			$this->addGrouping();
			$this->prepareQuery();
			// делаем запрос getData() в котором проходимся по всем записям и делаем $rows = $this->prepareRows($rows);
			// оборачиваем все во wrap (или emptywrap)
			// закидываем в ph если так требует конфиг
			// формируем выдачу если это ajax то возвращаем json, иначе просто return $output.

			$result = $this->getData();
			$this->logSetPrevState();

			if ($this->isParent){
				$this->modx->unsetPlaceholder('aw_isParent');
			}
		}
		return $result;
	}

	/**
	 * Create object with xPDOQuery
	 */
	public function makeQuery() {
		$this->query = $this->modx->newQuery($this->classKey);
		$this->writeLog('xPDO query object created');
	}

	/**
	 * Group query by given field
	 */
	public function addGrouping() {
		if (!empty($this->config['groupby'])) {
			$time = microtime(true);
			$groupby = $this->config['groupby'];
			$this->query->groupby($groupby);
			$this->writeLog('Grouped by <b>'.$groupby.'</b>', microtime(true) - $time);
		}
	}

	/**
	 * Add sort to query
	 */
	public function addSort() {
		$time = microtime(true);
		$tmp = $this->config['sortby'];
		if (empty($tmp) || strtolower($tmp) == 'resources' || strtolower($tmp) == 'ids') {
			$resources = $this->config['class'].'.'.$this->pk.':IN';
			if (!empty($this->config['where'][$resources])) {
				$tmp = array(
					'FIELD(`'.$this->config['class'].'`.`'.$this->pk.'`,\''.implode('\',\'', $this->config['where'][$resources]).'\')' => ''
				);
			}
			else {
				$tmp = array(
					$this->config['class'].'.'.$this->pk => !empty($this->config['sortdir'])
						? $this->config['sortdir']
						: 'ASC'
				);
			}
		}
		else {
			$tmp = (is_string($tmp) && ($tmp[0] == '{' || $tmp[0] == '['))
				? $this->modx->fromJSON($this->config['sortby'])
				: array($this->config['sortby'] => $this->config['sortdir']);
		}
		if (!is_array($tmp)) {$tmp = array();}
		if (!empty($this->config['sortbyTV']) && !array_key_exists($this->config['sortbyTV'], $tmp['sortby'])) {
			$tmp2[$this->config['sortbyTV']] = !empty($this->config['sortdirTV'])
				? $this->config['sortdirTV']
				: 'ASC';
			$tmp = array_merge($tmp2, $tmp);
		}

		$fields = $this->modx->getFields($this->config['class']);

		// с этим нужно будет разобраться
		$sorts = $this->replaceTVCondition($tmp);

		if (is_array($sorts)) {
			while (list($sortby, $sortdir) = each($sorts)) {
				if (preg_match_all('/TV(.*?)[`|.]/', $sortby, $matches)) {
					foreach ($matches[1] as $tv) {
						if (array_key_exists($tv,$this->config['tvsJoin'])) {
							$params = $this->config['tvsJoin'][$tv]['tv'];
							switch ($params['type']) {
								case 'number':
									$sortby = preg_replace('/(TV'.$tv.'\.value|`TV'.$tv.'`\.`value`)/', 'CAST($1 AS DECIMAL(13,3))', $sortby);
									break;
								case 'date':
									$sortby = preg_replace('/(TV'.$tv.'\.value|`TV'.$tv.'`\.`value`)/', 'CAST($1 AS DATETIME)', $sortby);
									break;
							}
						}
					}
				}
				elseif (array_key_exists($sortby, $fields)) {
					$sortby = $this->config['class'].'.'.$sortby;
				}
				$this->query->sortby($sortby, $sortdir);

				$this->writeLog('Sorted by <b>'.$sortby.'</b>, <b>'.$sortdir.'</b>','','WARN');
				$time = microtime(true);
			}
		}
	}


	/**
	 * Add tables join to query
	 */
	public function addJoins() {

		$time = microtime(true);
		// left join is always needed because of TVs
		if (empty($this->config['leftJoin'])) {
			$this->config['leftJoin'] = '[]';
		}

		// привязываем обязательные JOIN - это плейлисты и типики.
		if ( in_array($this->config['part'], array('video', 'item')) ){

			if (is_string($this->config['leftJoin']) && ($this->config['leftJoin'][0] == '{' || $this->config['leftJoin'][0] == '[')) {
				$this->config['leftJoin'] = $this->modx->fromJSON($this->config['leftJoin']);
			}

			$this->config['leftJoin']=array_merge($this->config['leftJoin'],array(
				'playlist' => array(
					'class' => 'awesomeVideosPlaylist',
					'on' => 'awesomeVideosItem.playlist = playlist.id',
			)));
			// $this->config['select']=array('awesomeVideosItem'=>'*','playlist'=>'playlist.* as playlist');
			$this->config['select']=(!$this->config['select']) ? array('awesomeVideosItem'=>'*','playlist'=>'*') : $this->config['select'];
		}

		foreach (array('innerJoin','leftJoin','rightJoin') as $join) {
			if (!empty($this->config[$join])) {
				$tmp = $this->config[$join];
				// $this->writeLog($join, microtime(true) - $time,'ERROR');
				// $this->writeLog($tmp, microtime(true) - $time,'ERROR');
				if (is_string($tmp) && ($tmp[0] == '{' || $tmp[0] == '[')) {
					$tmp = $this->modx->fromJSON($tmp);
				}
				if ($join == 'leftJoin' && !empty($this->config['tvsJoin'])) {
					$tmp = array_merge($tmp, $this->config['tvsJoin']);
				}


				foreach ($tmp as $k => $v) {
					$class = !empty($v['class']) ? $v['class'] : $k;
					$alias = !empty($v['alias']) ? $v['alias'] : $k;
					if (!is_numeric($alias) && !is_numeric($class)) {
						$this->query->$join($class, $alias, $v['on']);
						$this->writeLog($join.'ed <i>'.$class.'</i> as <b>'.$alias.'</b>', microtime(true) - $time);
						$this->aliases[$alias] = $class;
					}
					else {
						$this->writeLog('Could not '.$join.' <i>'.$class.'</i> as <b>'.$alias.'</b>', microtime(true) - $time,'ERROR');
					}
					$time = microtime(true);
				}
			}
		}
	}


	/**
	 * Add select of fields
	 */
	public function addSelects() {
		$time = microtime(true);

		if ($this->config['return'] == 'ids') {
			$this->query->select('
				SQL_CALC_FOUND_ROWS `'.$this->config['class'].'`.`'.$this->pk.'`
			');
			$this->writeLog('Parameter "return" set to "ids", so we select only primary key', microtime(true) - $time);
		}
		elseif ($tmp = $this->config['select']) {
			if (!is_array($tmp)) {
				$tmp = (!empty($tmp) && $tmp[0] == '{' || $tmp[0] == '[')
					? $this->modx->fromJSON($tmp)
					: array($this->config['class'] => $tmp);
			}
			if (!is_array($tmp)) {$tmp = array();}
			$tmp = array_merge($tmp, $this->config['tvsSelect']);
			$this->writeLog('tvsSelect: '.print_r( $this->config['tvsSelect'], true), microtime(true) - $time);
			$i = 0;
			// print_r($this->config['select']); die();
			foreach ($tmp as $class => $fields) {
				if (is_numeric($class)) {
					$class = $alias = $this->config['class'];
				}
				elseif (isset($this->aliases[$class])) {
					$alias = $class;
					$class = $this->aliases[$alias];
				}
				else {
					$alias = $class;
				}

				if (is_string($fields) && !preg_match('/\b'.$alias.'\b|\bAS\b|\(|`/i', $fields) && isset($this->modx->map[$class])) {
					if ($fields == 'all' || $fields == '*' || empty($fields)) {

						$prefix = ($this->config['class'] == $alias) ? '' : $alias.'.';
						$fields = $this->modx->getSelectColumns($class, $alias, $prefix);

					}
					else {
						// print_r($fields); die;
						// $c->getSelectColumns('objectName','objectAlias','',array('fieldName:AS'=>'fieldAlias'));
						$fields = $this->modx->getSelectColumns($class, $alias, '', array_map('trim', explode(',', $fields)));
					}
				}

				$this->writeLog('getSelectColumns: '.print_r($fields, true).';'.$class.','.$alias, microtime(true) - $time);

				if ($i == 0) {
					$fields = 'SQL_CALC_FOUND_ROWS '.$fields;
				}
				$this->query->select($fields);
				$i++;

				if (is_array($fields)) {
					$fields = current($fields) . ' AS ' . current(array_flip($fields));
				}
				$this->writeLog('Added selection of <b>'.$class.'</b>: <small>' . str_replace('`'.$alias.'`.', '', $fields) . '</small>', microtime(true) - $time);
				$time = microtime(true);
			}
		}
		else {
			$columns = array_keys($this->modx->getFieldMeta($this->config['class']));
			if (isset($this->config['includeContent']) && empty($this->config['includeContent'])) {
				$key = array_search('content', $columns);
				unset($columns[$key]);
			}
			$this->config['select'] = array($this->config['class'] => implode(',', $columns));
			$this->addSelects();
		}
	}

	/**
	 * Replaces tv fields to full name format.
	 * For example, field "test" will be replaced with "TVtest.value", if template variable "test" was joined in query.
	 *
	 * @param array $array Array for replacement
	 * @param bool  $having True if for "having" part.
	 *
	 * @return array $sorts Array with replaced conditions
	 */
	public function replaceTVCondition(array $array, $having = false ) {
		if (empty($this->config['tvsJoin'])) {return $array;}

		$time = microtime(true);
		$tvs = implode('|', array_keys($this->config['tvsJoin']));

		$tvPrefix = !empty($this->config['tvPrefix']) ?
			trim($this->config['tvPrefix'])
			: '';

		$sorts = array();
		foreach ($array as $k => $v) {
			$tmp_fun=(!$having)? 'return \'`TV\'.strtolower($matches[1]).\'`.`value`\';' : 'return \''.$tvPrefix.'\'.strtolower($matches[1]);';
			$callback = create_function('$matches', $tmp_fun);

			if (is_numeric($k) && is_string($v)) {
				$tmp = preg_replace_callback('/\b('.$tvs.')\b/i', $callback, $v);
				$this->writeLog('HAV1: '.$tmp, microtime(true) - $time);
				$sorts[$k] = $tmp;
			}
			else {
				$tmp = preg_replace_callback('/\b('.$tvs.')\b/i', $callback, $k);
				$this->writeLog('HAV2: '.$tmp, microtime(true) - $time);
				$sorts[$tmp] = $v;
			}
		}

		$this->writeLog('Replaced TV conditions', microtime(true) - $time);
		return $sorts;
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
			$where = $this->replaceTVCondition($tmp);
		}


		// добавляем критерии из статичных параметров
		if ($this->config['id'])  $where[]=array( "id:=" => (int) $this->config['id'] );
		if ($this->config['ids']) $where[]=array( "id:IN" => (is_string($this->config['ids']))?explode(',', $this->config['ids']):$this->config['ids']);
		if ($this->config['parentIds'] && in_array($this->config['part'], array('video', 'item')) ) {
			$where[]=array("playlist:IN"=>(is_string($this->config['parentIds']))?explode(',', $this->config['parentIds']):$this->config['parentIds']);
		}

		if ( $this->config['topic']!==false && in_array($this->config['part'], array('video', 'item')) &&
				 $tmpTopic=$this->fastProcess($this->config['topic'], true)
			 ) {
			// echo $this->fastProcess($this->config['topic'], true);die;
			// $where[]=array("topic:=" => $this->modx->sanitizeString($this->config['topic']) );
			$where[]=array("topic:=" => $tmpTopic );
		}

		// $where = $this->additionalConditions($where);
		if (!empty($where)) {
			$this->query->where($where);

			$this->writeLog('WHERE: '.print_r($where, true));

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
			$this->writeLog('Added where condition: <b>' .implode(', ',$condition).'</b>');
		}
		// $time = microtime(true);
		if (!empty($this->config['having'])) {
			$tmp = $this->config['having'];
			if (is_string($tmp) && ($tmp[0] == '{' || $tmp[0] == '[')) {
				$tmp = $this->modx->fromJSON($tmp);
			}
			$having = $this->replaceTVCondition($tmp,true);
			$this->writeLog('Having: ' .print_r($having, true), microtime(true) - $time);
			// $this->query->having($having);
			$this->query->query['having']=$this->simpleParseConditions($having);
			// $this->query->having(array('1'=>'1'));

			$condition = array();
			foreach ($having as $k => $v) {
				if (is_array($v)) {$condition[] = $k.'('.implode(',',$v).')';}
				else {$condition[] = $k.'='.$v;}
			}
			$this->writeLog('Added having condition: <b>' .implode(', ',$condition).'</b>', microtime(true) - $time);
		}
		$this->writeLog('WHERE with HAVING: '.print_r($this->query->query, true));
	}

	public function simpleParseConditions( $array) {
		$time = microtime(true);

		 	/*$array=array(
		 		"zzz = 555"
		    "xxx:=:OR"=>123,
		    "xxx2:="=>"123",
		    "test:IN"=>array(1,2,3),
		    "test2:IN"=>123
		  );*/

    foreach ($array as $key => $val) {

        $key_operator= explode(':', $key);
        if ($key_operator && count($key_operator) === 2) {
            $key= $key_operator[0];
            $operator= $key_operator[1];
        }
        elseif ($key_operator && count($key_operator) === 3) {
            $conj= $key_operator[0];
            $key= $key_operator[1];
            $operator= $key_operator[2];
        }

        $operator=$operator ? $operator : "=";


        if (isset($key)) {

					if (is_numeric($key))	$key="";

          if ($val === null) {
              if (!in_array($operator, array('IS', 'IS NOT'))) {
                  $operator= $operator === '!=' ? 'IS NOT' : 'IS';
              }
          }

          $type= PDO::PARAM_STR;
          if ( !empty($key) && in_array(strtoupper($operator), array('IN', 'NOT IN')) && is_array($val)) {
            $vals = array();
            foreach ($val as $v) {
                if ($v === null) {
                    $vals[] = null;
                } else {
                    switch (gettype($v)) {
                        case 'integer':
                            $vals[] = (integer) $v;
                            break;
                        case 'string':
                            $vals[] = $this->modx->quote($v);
                            break;
                        default:
                            $vals[] = $v;
                            break;
                    }
                }
            }
            $val = "(" . implode(',', $vals) . ")";
            $sql = trim("{$this->modx->escape($key)} {$operator} {$val}");
            $result[]= new xPDOQueryCondition(array('sql' => $sql, 'binding' => null, 'conjunction' => $conj));
            continue;
          }
          $field= array ();
          if (!empty($key)) {
	          $field['sql']= trim($this->modx->escape($key) . ' ' . $operator . ' ?');
	          $field['binding']= array (
	              'value' => $val,
	              'type' => $type,
	              'length' => 0
	          );
	          $field['conjunction']= $conj;
	        }else{
	          $field['sql']= trim($val);
	        }

          $result[]= new xPDOQueryCondition($field);
        } else {
            throw new xPDOException("Invalid query expression");
        }
    }

 		$this->writeLog('Having query detail: ' .print_r($result, true), microtime(true) - $time);

    return $result;
	}

	/**
	 * Set parameters and prepare query
	 *
	 * @return PDOStatement
	 */
	public function prepareQuery() {
		if (!empty($this->config['limit'])) {

			$this->query->limit($this->config['limit'], $this->config['offset']);
			$this->writeLog('Limited to <b>'.$this->config['limit'].'</b>, offset <b>'.$this->config['offset'].'</b>');
		}

		return $this->query->prepare();
	}

	/**
	 * Set "total" placeholder for pagination
	 */
	public function setTotal() {
		if ($this->config['return'] != 'sql') {
			$time = microtime(true);

			$q = $this->modx->prepare("SELECT FOUND_ROWS();");

			$tstart = microtime(true);
			$q->execute();
			$this->modx->queryTime += microtime(true) - $tstart;
			$this->modx->executedQueries++;

			$total = $q->fetch(PDO::FETCH_COLUMN);
			$this->modx->setPlaceholder($this->config['totalVar'], $total);
			$this->config['total'] = $total;
			$this->writeLog('Total rows: <b>'.$total.'</b>', microtime(true) - $time,'WARN');
		}
	}


  /**
   * Get the data of the query
   * @return array
   */
  public function getData() {

      $data = array();
			$output = '';
			$this->writeLog('SQL prepared: <br/><small>'.$this->query->toSql().'</small>','','WARN');
			// echo $this->query->toSql();
			$this->modx->setPlaceholder($this->config['sqlQuery'], $this->query->toSql());
			$tstart = microtime(true);
			if ($this->query->stmt->execute()) {
				$this->modx->queryTime += microtime(true) - $tstart;
				$this->modx->executedQueries++;
				$this->writeLog('SQL executed', microtime(true) - $tstart);
				$this->setTotal();

				$rows = $this->query->stmt->fetchAll(PDO::FETCH_ASSOC);
				$this->writeLog('Rows fetched');
				// $rows = $this->checkPermissions($rows);	// мне это не нужно в том виде в котором оно есть
				$this->count = count($rows);

				switch (strtolower($this->config['return'])) {
					case 'ids':
						$ids = array();
						foreach ($rows as $row) {
							$ids[] = $row['id'];
						}
						$output = implode(',', $ids);
						break;

					case 'data':
						$rows = $this->prepareRows($rows);	// именно в этом методе мы парсим все TV, обрабатываем image, и ....
						$this->writeLog('Returning raw data');
						// $output = & $rows;
						$output = '<pre>'.print_r($rows, true).'</pre>';
						break;

					case 'json':
						$rows = $this->prepareRows($rows);	// именно в этом методе мы парсим все TV, обрабатываем image, и ....
						$output=$this->modx->toJSON($rows);
						break;

					case 'chunk':
					default:
						$rows = $this->prepareRows($rows);	// именно в этом методе мы парсим все TV, обрабатываем image, и ....
						$time = microtime(true);
						$this->idx = $this->config['offset'] + 1;
						foreach ($rows as $row) {
							// print_r($row);
							if (!empty($this->config['additionalPlaceholders'])) {
								$row = array_merge($this->config['additionalPlaceholders'], $row);
							}
							$row['idx'] = $this->idx++;

// if ( $this->config['topic']!==false ) {
	// unset ($row['topic']);
							// $row['topic']=false;
// }

							$addDataToUrlArr = array();

							if ($this->config['addDataToUrl']){
								$addDataToUrl = $this->getChunk('@INLINE:'.$this->config['addDataToUrl'], $row);
								$addDataToUrlArr = ($addDataToUrl)
									? $this->modx->fromJSON($addDataToUrl)
									: array()
								;
							}

							$uri=$this->makeLink($uriParams=array_merge(array(
								'part'=>$this->config['part'],
								'id'=>$row['id'],
								'setOfProperties'=>$this->config['setOfProperties'],
							),$addDataToUrlArr), 0, $this->config['baseLink']['baseUrl'] );

							$uriParams=$this->modx->toJSON($uri['params']);

							$row['uriParams'] = $uriParams;
							$row['uri'] = $uri['href'];

							$tpl = $this->defineChunk($row);
							// print_r($row);
							if (empty($tpl)) {
							// if (empty($tpl) && !$this->config['direct']) {
								$output[] = 'Template for row not found: <pre>'.$this->getChunk('', $row).'</pre>';
							// } elseif(empty($tpl) && $this->config['direct']){
								// $output[] = $row;
							}
							else {
								$output[] = $this->getChunk($tpl, $row);
								// $this->modx->unsetPlaceholder('topic');
							}
						}

						// $offset = $this->config['limit'] + $this->config['offset'];
						$this->modx->setPlaceholder($this->config['offsetVar'], $this->idx--);
						$this->config['offset'] = $this->idx--;

						$this->writeLog('Returning processed chunks', microtime(true) - $time);

						if (!empty($this->config['toSeparatePlaceholders'])) {
							$this->modx->setPlaceholders($output, $this->config['toSeparatePlaceholders']);
							$output = '';
						}
						else{
							if ($output) $output = implode($this->config['outputSeparator'], $output);

							$balance = (int)$this->config['total'] - (int)$this->config['offset'];
							// $limit = ($this->config['limit'] > $balance) ? $balance : $this->config['limit'];
							$limit = ($this->config['pagination']!=='snippet' && $this->config['limit'] > $balance) ? $balance : $this->config['limit'];

							$config = array(
								'ajax' => $this->config['ajax'],
								'bindHistory' => $this->config['bindHistory'],
								'log_status'=>$this->config['log']['log_status'],
								// 'limit' => 8,
								'limit' => $limit,
								// 'offset' => $this->config['limit'] + $this->config['offset'],
								// 'offset' => $this->modx->getPlaceholder($this->config['offsetVar']),
								'offset' => $this->config['offset'],
								// 'offset' => $this->config['offset'] - $this->config['limit'],
								'total' => $this->config['total'],
								'balance' => $balance,
								'part' => $this->config['part'],
								// 'where' => $this->config['where'],	// не стоит показывать, все равно он не используется в get и request
								'key' => $this->config['key'],
								'setOfProperties' => $this->config['setOfProperties'],
								'pagination' => $this->config['pagination'],
								// 'page' => $this->config['page'],
								// 'page' => 1,
							);

							if ($this->config['pagination']=='snippet') $config['page'] = $this->config['page'];

							$paging = $this->getPaging($config);
							// $config['limit'] = ($this->config['limit'] > $balance && $balance) ? $balance : $this->config['limit'];
							// $config['offset'] = $this->config['offset'];

							// if ( $this->config['log']['log_placeholder'] && !$this->config['log']['log_target']=='PLACEHOLDER' ){
							if ( !$this->config['log']['log_status'] && $this->config['log']['log_placeholder'] ){
								// т.к. у нас одноименный сниппет может быть вложенным, а wrapper является общим, то
								// плейсхолдер продублируется, предотвратим это.
								$this->modx->unsetPlaceholder($this->config['log']['log_placeholder']);
							}

							// оборачиваем все в главную обертку
							if (!$this->config['direct'] && $this->config['tplWrapper']){

								// нужно создать массив содержащий РАЗРЕШЕННЫЕ стартовые параметры, это на случай если клиент в браузере
								// возвратиться на первую страницу. Без начальных параметров он может получить не правильное смещение offset

								// $snippetStartProperties = array_intersect_key($this->config['snippetStartProperties'], $config);
								// $snippetStartProperties = $this->config['snippetStartProperties'];
								// if (empty($snippetStartProperties['offset']))
								// $snippetStartProperties = $config;
								// $denied = array('offset','balance','total','addDataToUrl');
								// $snippetStartProperties = array_merge( $config, $this->config['snippetStartProperties'] );
								// $snippetStartProperties = array_diff_key($snippetStartProperties, array_flip($denied) );
								// $snippetStartProperties=array('key'=>$this->config['key']);
								$result_ph=array(
									'config' => $this->modx->toJSON($config),
									// 'mainUriHref' => $this->config['mainUri']['href'],
									'part' => $this->config['part'],
									'key' => $this->config['key'],
									'output' => $output,
									'paging' => $paging,
									'paginationType' => $this->config['pagination'],
									// 'configStart' => $this->modx->toJSON( $snippetStartProperties ),
									// $this->config['snippetStartProperties']
									// 'log' => $this->modx->getPlaceholder($this->config['totalVar']),
									// 'total' => $this->modx->getPlaceholder($this->config['totalVar']),
									'total' => $this->config['total'],
								);
								if ( $this->count || (!$this->count && $this->config['wrapIfEmpty']) ){
									$tplWrapName = (!$this->count && $this->config['wrapIfEmpty'] === '-1') ? 'tplWrapperEmpty' : 'tplWrapper';
									$output = $this->getChunk($this->config[$tplWrapName], $result_ph);
								}
							}else if($this->config['direct']){
								$config['data'] = $output;
								$config['paging'] = $paging;
								$output = $config;
							}
					}
					break;
				}

			}
			else {
				// $this->modx->log(modX::LOG_LEVEL_INFO, '[pdoTools] '.$this->query->toSql());
				$errors = $this->query->stmt->errorInfo();
				$this->writeLog('Error '.$errors[0].': '.$errors[2], '','ERROR');
				$this->writeLog('Could not process query, error #'.$errors[1].': ' .$errors[2],'','ERROR');
				$output = $this->getChunk($this->config['tplWrapper']);
			}

      return $output;
  }

  public function getPaging($config){
  	$this->writeLog('Параметры пагинации', '','ERROR');
  	$this->writeLog($config, '','ERROR');

  	$tplEmpty=(!$config['balance'])?'Empty':false;
  	$result=$tpl='';
  	switch ($config['pagination']) {
			case 'button':
				$tpl = ($tplEmpty && $this->config['tplPagingButton'.$tplEmpty])
					? $this->config['tplPagingButton'.$tplEmpty]
					: $this->config['tplPagingButton'];
				$result = $this->getChunk($tpl, $config);
				break;

			case 'snippet':
			// case 'scroll':
				if (!$this->config['paginationSnippet']){
					$this->writeLog('Не указано имя сниппета для создания пагинации', '','ERROR');
					return false;
				}

				$paginationSnippet = $this->config['paginationSnippet'];
				$paginationSnippetRegExp = '(getPage|pdoPage)';
				if (!$snippetProperties=$this->getSessionStore($paginationSnippet.'_shortProperties')){
					$snippetProperties=$this->getSnippetProperties($this->snippet_name, '/('.$paginationSnippetRegExp.')/i');
					$this->setSessionStore($paginationSnippet.'_shortProperties', $snippetProperties, 60 );
				}

				// получам только нужные параметры
				$allowed = array('page', 'limit', 'total', 'offset');
				$config = array_intersect_key($config, array_flip($allowed));
				$snippetProperties=array_merge($snippetProperties, $config, array ('element'=>null,'totalVar'=>$this->config['totalVar']));
// echo 123;

				$this->modx->setPlaceholder($this->config['totalVar'], $snippetProperties['total']);
				$prevRequest=$_REQUEST;
				$prevGet=$_GET;

				// if (!$this->config['direct'] || ($this->config['direct'] && $_REQUEST['page']<=1)){

		  	$this->writeLog('Параметры пагинации перед передачей в сниппет', '','ERROR');
		  	$this->writeLog($snippetProperties, '','ERROR');

		  	if ($this->modx->getOption('friendly_urls') ){
					$_REQUEST[$this->req_var] = $this->config['mainUri']['baseUrl'];
					$_GET = array_merge($_GET,$this->config['mainUri']['params']);
		  	}

				if ( $_REQUEST['page']<=1 ){
					unset($_REQUEST['page']);
				}
				if (!$this->config['direct'] && $this->isParent && isset($_GET['page']) && $_GET['page']>1 ){
					$_REQUEST['page'] = $_GET['page'];
				}elseif ( !$this->isParent ){
					// вырубаем зависимость пагинации от запроса у вложенных сниппетов
					unset($_GET['page']);
					unset($_REQUEST['page']);
				}

		  	$_REQUEST = array_merge($_REQUEST, array(
		  		'limit'=>$snippetProperties['limit'],
		  		'offset'=>$snippetProperties['offset'],
		  		// 'page'=>$GET[],
		  		'total'=>$snippetProperties['total'],
		  	));

		  	$this->writeLog('Параметры Q1='.$_REQUEST[$q_var], '','ERROR');
		  	$this->writeLog('Параметры $_GET', '','ERROR');
		  	$this->writeLog($_GET, '','ERROR');

				// echo '<pre>';	print_r($_REQUEST); die();
				$output = $this->runSnippet($paginationSnippet, $snippetProperties);
				$_REQUEST = $prevRequest;
				$_GET = $prevGet;

				// print_r($this->makeLink($this->config['mainUri']));
				// print_r($this->config['mainUri']['href']);
				// echo '</pre> ';
				// print_r($snippetProperties);
				$result=$this->modx->getplaceholder('page.nav');

				// снипет getPage ведет себя кривовато и выводит часть плейсхолдеров в чистом виде, поэтому нужно на всякий
				// случай дополнительно проходиться по парсером.
				// $chunk=$this->modx->newObject('modChunk');
				// $chunk->setCacheable(false);
				// $result = $chunk->process(array(),$result);

				$result = $this->fastProcess($result, true);

		    // $maxIterations= intval($this->modx->getOption('parser_max_iterations', $params, 10));
		    // $this->modx->parser->processElementTags('', $result, true, false, '[[', ']]', array(), $maxIterations);
		    // $this->modx->parser->processElementTags('', $result, true, true, '[[', ']]', array(), $maxIterations);

				// так как вызываемый сниппет может быть вложенным, и у него может быть пагинация
				// то нам нужно изменить URL в пагинации данного раздела таким образом, чтобы при отключенном
				// аяксе ссылки были также рабочие

				// print_r($this->config['mainUri']); die();

				$mainUri = (strpos($this->config['mainUri']['href'], '?')!==false) ? $this->config['mainUri']['href'].'&' : $this->config['mainUri']['href'];
				// $result = str_replace(array('/?',$this->config['baseLink']['href']), $mainUri, $result);

				// $result = str_replace(array($this->config['baseLink']['href']), $mainUri, $result);

				return $result;
				break;
		}
		return $result;
  }

	/**
	 * Method for define name of a chunk serving as resource template
	 * This algorithm taken from snippet getResources by opengeek
	 *
	 * @param array $properties Resource fields
	 *
	 * @return mixed
	 */
	public function defineChunk($properties = array()) {
		$idx = isset($properties['idx']) ? (integer) $properties['idx'] : $this->idx++;
		$idx -= $this->config['offset'];

		$first = empty($this->config['first']) ? ($this->config['offset'] + 1) : (integer) $this->config['first'];
		$last = empty($this->config['last']) ? ($this->count + $this->config['offset']) : (integer) $this->config['last'];

		$odd = !($idx & 1);
		$resourceTpl = '';
		if ($idx == $first && !empty($this->config['tplFirst'])) {
			$resourceTpl = $this->config['tplFirst'];
		}
		else if ($idx == $last && !empty($this->config['tplLast'])) {
			$resourceTpl = $this->config['tplLast'];
		}
		else if (!empty($this->config['tpl_' . $idx])) {
			$resourceTpl = $this->config['tpl_' . $idx];
		}
		else if ($idx > 1) {
			$divisors = array();
			for ($i = $idx; $i > 1; $i--) {
				if (($idx % $i) === 0) {
					$divisors[] = $i;
				}
			}
			if (!empty($divisors)) {
				foreach ($divisors as $divisor) {
					if (!empty($this->config['tpl_n' . $divisor])) {
						$resourceTpl = $this->config['tpl_n' . $divisor];
						break;
					}
				}
			}
		}

		if (empty($resourceTpl) && $odd && !empty($this->config['tplOdd'])) {
			$resourceTpl = $this->config['tplOdd'];
		}
		else if (empty($resourceTpl) && !empty($this->config['tplCondition']) && !empty($this->config['conditionalTpls'])) {
			$conTpls = $this->modx->fromJSON($this->config['conditionalTpls']);
			if (isset($properties[$this->config['tplCondition']])) {
				$subject = $properties[$this->config['tplCondition']];
				$tplOperator = !empty($this->config['tplOperator']) ? strtolower($this->config['tplOperator']) : '=';
				$tplCon = '';
				foreach ($conTpls as $operand => $conditionalTpl) {
					switch ($tplOperator) {
						case '!=': case 'neq': case 'not': case 'isnot': case 'isnt': case 'unequal': case 'notequal':
							$tplCon = (($subject != $operand) ? $conditionalTpl : $tplCon);
							break;
						case '<': case 'lt': case 'less': case 'lessthan':
							$tplCon = (($subject < $operand) ? $conditionalTpl : $tplCon);
							break;
						case '>': case 'gt': case 'greater': case 'greaterthan':
							$tplCon = (($subject > $operand) ? $conditionalTpl : $tplCon);
							break;
						case '<=': case 'lte': case 'lessthanequals': case 'lessthanorequalto':
							$tplCon = (($subject <= $operand) ? $conditionalTpl : $tplCon);
							break;
						case '>=': case 'gte': case 'greaterthanequals': case 'greaterthanequalto':
							$tplCon = (($subject >= $operand) ? $conditionalTpl : $tplCon);
							break;
						case 'isempty': case 'empty':
							$tplCon = empty($subject) ? $conditionalTpl : $tplCon;
							break;
						case '!empty': case 'notempty': case 'isnotempty':
							$tplCon = !empty($subject) && $subject != '' ? $conditionalTpl : $tplCon;
							break;
						case 'isnull': case 'null':
							$tplCon = $subject == null || strtolower($subject) == 'null' ? $conditionalTpl : $tplCon;
							break;
						case 'inarray': case 'in_array': case 'ia':
							$operand = explode(',', $operand);
							$tplCon = in_array($subject, $operand) ? $conditionalTpl : $tplCon;
							break;
						case 'between': case 'range': case '>=<': case '><':
							$operand = explode(',', $operand);
							$tplCon = ($subject >= min($operand) && $subject <= max($operand)) ? $conditionalTpl : $tplCon;
							break;
						case '==': case '=': case 'eq': case 'is': case 'equal': case 'equals': case 'equalto':
						default:
							$tplCon = (($subject == $operand) ? $conditionalTpl : $tplCon);
							break;
					}
				}
			}
			if (!empty($tplCon)) {
				$resourceTpl = $tplCon;
			}
		}

		if (empty($resourceTpl) && !empty($this->config['tpl'])) {
			$resourceTpl = $this->config['tpl'];
		}

		return $resourceTpl;
	}


	/**
	 * Loads and returns chunk by various methods.
	 *
	 * @param string $name Name or binding
	 * @param array $row Current row with results being processed
	 *
	 * @return array
	 */
	protected function _loadChunk($name, $row = array()) {


		$binding = $content = '';

		$name = trim($name);
		if (preg_match('/^@([A-Z]+)/', $name, $matches)) {
			$binding = $matches[1];
			$content = substr($name, strlen($binding) + 1);
		}
		$content = ltrim($content, ' :');
		$content = str_replace(array('{{','}}'), array('[[',']]'), $content);

		// $this->writeLog($matches,'','ERROR');

		// Change name for empty TEMPLATE binding so will be used template of given row
		if ($binding == 'TEMPLATE' && empty($content) && isset($row['template'])) {
			$name = '@TEMPLATE '.$row['template'];
			$content = $row['template'];
		}

		// Load from cache
		$cache_name = (strpos($name, '@') === 0) ? md5($name) : $name;

		// if ($chunk = $this->getSessionStore($cache_name, 'chunk') ) {
		if ($chunk = $this->getStore($cache_name, 'chunk')) {
			return $chunk;
		}

		/** @var modChunk $element */
		switch ($binding) {
			case 'CODE':
			case 'INLINE':
				// $element = $this->modx->newObject('modChunk', array('name' => md5($name)));
				// $element->setContent($content);
				$this->writeLog('Created inline chunk');
				break;
			case 'FILE':
				// echo ($this->config['tplPath']."xxx");
				$path = !empty($this->config['tplPath'])
					? $this->config['tplPath'] . '/'
					: MODX_ASSETS_PATH . 'elements/chunks/';
// echo $path;
				$path = (strpos($content, MODX_BASE_PATH) === false)
					? MODX_BASE_PATH . ltrim($path, '/') . $content
					: $content;
// echo "<br/>".$path;

				$path = preg_replace('#/+#', '/', $path);

				$this->writeLog("Chunk is FILE, full path: {$path}",'','WARN');

				if (!preg_match('/(.html|.tpl)$/i', $path)) {
					$this->writeLog('Allowed extensions for @FILE chunks is "html" and "tpl"','','ERROR');
				}else{
					$content = file_get_contents($path);
					// $element = $this->modx->newObject('modChunk', array('name' => md5($name)));
					// $element->setContent($content);
					// $this->writeLog('ZZZZ "'.$name.'"');
					$this->writeLog('Loaded chunk from: "'.str_replace(MODX_BASE_PATH, '', $path).'"');
				}
				break;
			case 'TEMPLATE':
				/** @var modTemplate $template */
				if ($template = $this->modx->getObject('modTemplate', array('id' => $content, 'OR:templatename:=' => $content))) {
					$content = $template->getContent();
					$this->writeLog('Created chunk from template: "'.$template->templatename.'"');
				}
				break;
			case 'CHUNK':
				$name = $content;
				if ($element = $this->modx->getObject('modChunk', array('name' => $name))) {
					$content = $element->getContent();
					$this->writeLog('Loaded chunk: "'.$name.'"');
				}
				break;
			default:
				if ($element = $this->modx->getObject('modChunk', array('name' => $name))) {
					$content = $element->getContent();
					$this->writeLog('Loaded chunk as default: "'.$name.'"');
				}
		}

		if (!$content) {
			$this->writeLog('Could not load chunk or chunk is empty!"'.$name.'".','','ERROR');
			return false;
		}

		// Preparing special tags
		// удаляем все плейсхолдеры которые находятся в заккоментированном тексте разметки html
		preg_match_all('/\<!--'.$this->config['nestedChunkPrefix'].'(.*?)[\s|\n|\r\n](.*?)-->/s', $content, $matches);
		$src = $dst = array();
		foreach ($matches[1] as $k => $v) {
			$src[] = $matches[0][$k];
			$dst[] = '';
		}
		if (!empty($src) && !empty($dst)) {
			$content = str_replace($src, $dst, $content);
		}

		$chunk = array(
			// 'object' => $element
			'content' => $content
		);

		// $this->setStore($cache_name, $chunk, 'chunk');
		$this->setSessionStore($cache_name, $chunk, 60, 'chunk' );
		return $chunk;
	}


	/**
	 * Set data to cache
	 *
	 * @param $name
	 * @param $object
	 * @param string $type
	 */
	public function setStore($name, $object, $type = 'data') {
		// return;
		$this->store[$type][$name] = $object;
	}


	/**
	 * Get data from cache
	 *
	 * @param $name
	 * @param string $type
	 *
	 * @return mixed|null
	 */
	public function getStore($name, $type = 'data') {
		return isset($this->store[$type][$name])
			? $this->store[$type][$name]
			: null;
	}


	/**
	 * Process and return the output from a Chunk by name.
	 *
	 * @param string $name The name of the chunk.
	 * @param array $properties An associative array of properties to process the Chunk with, treated as placeholders within the scope of the Element.
	 *
	 * @return mixed The processed output of the Chunk.
	 */
	public function getChunk($name = '', array $properties = array()) {
		$properties = $this->prepareRow($properties);
		$name = trim($name);

		/* @var $chunk modChunk[] */
		if (!empty($name)) {
			$chunk = $this->_loadChunk($name, $properties);
		}
		if (empty($name) || empty($chunk)) {
			return !empty($properties)
				? str_replace(array('[',']','`'), array('&#91;','&#93;','&#96;'), htmlentities(print_r($properties, true), ENT_QUOTES, 'UTF-8'))
				// ? ''
				// ? $name
				: '';
		}
		$content = $chunk['content'];
		// echo (htmlspecialchars(print_r($properties, true)));
		$this->modx->toPlaceholders($properties);
		// Processing given placeholders
		if (!empty($properties)) {
			// тут мы заменяем все плейсхолдеры [[+]], и не трогаем остальные
			// echo "<pre>AAA";print_r($properties, true);echo "QQQ</pre>";
			// echo "<pre>";
			// echo (htmlspecialchars(print_r($pl, true)));
			// echo "<pre>ZZZ";print_r($pl, true);echo "XXX</pre>";
			// die();
			// echo "</pre>";
			// $content = preg_replace($pl['pl'], $pl['vl'], $content);

			// отключил потом разберусь с парсером
			// $pl = $this->makePlaceholders($properties);
			// $content = str_replace($pl['pl'], $pl['vl'], $content);

		}
		// echo ('<br/>before:<br/>');
		// echo (htmlspecialchars($content));
		// echo ('<br/><br/>after:<br/>');

		// Processing other placeholders

		// $maxIterations= (integer) $this->modx->getOption('parser_max_iterations', null, 10);
		// $this->getParser()->processElementTags('', $content, false, false, '[[', ']]', array(), $maxIterations);
		// $this->getParser()->processElementTags('', $content, true, true, '[[', ']]', array(), $maxIterations);

		// print (htmlspecialchars($content));
		$content = $this->fastProcess($content, $this->config['fastMode']);

		// т.к. у нас наследование мы должны освободить переданные плейсхолдеры, чтобы сниппеты из которого
		// вызывался текущий сниппет при новой иттерации не смог использовать данные плейсхолдеры.
		$this->modx->unsetPlaceholders(array_keys($properties));
		// $this->modx->unsetPlaceholder('topic');

		// $content = $this->fastProcessOld($content, $this->config['fastMode']);
		// die (htmlspecialchars($content));


		return $content;
	}


	/**
	 * Allow user to prepare single row by custom snippet before render chunk
	 * This method was developed in cooperation with Agel_Nash
	 *
	 * @param array $row
	 *
	 * @return array
	 */
	public function prepareRow($row = array()) {
		if ($this->preparing) {return $row;}

		if (!empty($this->config['prepareSnippet'])) {
			$this->preparing = true;
			$name = trim($this->config['prepareSnippet']);

			/** @var modSnippet $snippet */
			if (!$snippet = $this->getStore($name, 'snippet')) {
				if ($snippet = $this->modx->getObject('modSnippet', array('name' => $name))) {
					$this->setStore($name, $snippet, 'snippet');
				}
				else {
					$this->writeLog('Could not load snippet "'.$name.'" for preparation of row.');
					return '';
				}
			}
			$snippet->_cacheable = false;
			$snippet->_processed = false;

			$tmp = $snippet->process(array(
				// 'pdoTools' => $this,
				// 'pdoFetch' => $this,
				'row' => $row,
			));

			$tmp = ($tmp[0] == '[' || $tmp[0] == '{')
				? $this->modx->fromJSON($tmp, 1)
				: unserialize($tmp);

			if (!is_array($tmp)) {
				$this->writeLog('Preparation snippet must return an array, instead of "'.gettype($tmp).'"');
			}
			else {
				$row = array_merge($row, $tmp);
			}
			$this->preparing = false;
		}

		return $row;
	}



	public function generateThumb(&$row, $rowName, $varName) {
		if (!$row || !$varName || !$rowName || !$this->config['thumbSnippet']) return false;

		$result=array();

		$result["{$varName}.thumb"]=$this->modx->runSnippet($this->config['thumbSnippet'],array(
			'input'=>$row[$varName],
			'options'=>$this->config['thumbProperties']
		));


		if ( $row['source'] && strtolower($row['source']) == 'youtube' && $row[$rowName]=='videoId' ) {
			// не хочу получать картинку с сервера, т.к. это лишние обращения, при большой посещаемости
			// доступ к сервису могут заблокировать после превышения лимита. Хотя если сменятся адреса,
			// то тоже ничего хорошего в этом нет. Получить, таким образом, картинку плейлиста или канала
			// не представляется возможным, поэтому ограничимся только видеороликом.

			$tmp = array (
				"https://i.ytimg.com/vi/{$row[$rowName]}/maxresdefault.jpg",
				"http://img.youtube.com/vi/UCfQfRkl4w4vwtb9C2ndx1_Q/0.jpg",
			);

			// https://i.ytimg.com/vi/5RyWvQze3ag/default.jpg
			// [id] => PLK2K6UAy2uj-uidRBkSASBDo9nN_Kd_hU
			//  [channelId] => UCfQfRkl4w4vwtb9C2ndx1_Q
			//   [url] => https://yt3.ggpht.com/-NsewhUSduCQ/AAAAAAAAAAI/AAAAAAAAAAA/wWMuVRw508Q/s240-c-k-no/photo.jpg

			foreach ($tmp as $value) {
				// размер не найденного изображения на сервере на 2014-11-26 == 1097 байт, возьмем с запасом
				if ( $fileHeaders = $this ->_remoteFileData($value) && (int)$fileHeaders['content-length'] > 2000  ) {
					$result["{$varName}.youtube"] = $value;
					break;
				}
			}
		}

		// print_r( $result ); die;
		if (!empty($result)) $row = array_merge($row,$result);
    return $row;
	}



	/**
	 * Prepares fetched rows and process template variables
	 *
	 * @param array $rows
	 *
	 * @return array
	 */
	public function prepareRows(array $rows = array()) {
		$time = microtime(true);
		$prepare = $process = $prepareTypes = array();
		if (!empty($this->config['includeTVs']) && (!empty($this->config['prepareTVs']) || !empty($this->config['processTVs']))) {
			$tvs = array_map('trim', explode(',', $this->config['includeTVs']));
			$prepare = ($this->config['prepareTVs'] == 1)
				? $tvs
				: array_map('trim', explode(',', $this->config['prepareTVs']));
			$prepareTypes = array_map('trim', explode(',', $this->modx->getOption('manipulatable_url_tv_output_types',null,'image,file')));
			$process = ($this->config['processTVs'] == 1)
				? $tvs
				: array_map('trim', explode(',', $this->config['processTVs']));

			$prepare = array_flip($prepare);
			$prepareTypes = array_flip($prepareTypes);
			$process = array_flip($process);
		}

		foreach ($rows as & $row) {
			// Extract JSON fields
			if ($this->config['decodeJSON']) {
				foreach ($row as $k => $v) {
					if (!empty($v) && is_string($v) && strlen($v) >= 2 && (($v[0] == '{' && $v[1] == '"') || ($v[0] == '[' && $v[1] != '['))) {
						$tmp = $this->modx->fromJSON($v);
						if ($tmp !== null) {
							$row[$k] = $tmp;
						}
					}
				}
			}

      if ($this->config['parseDate']){
          if ($row['created']) $row['created']=date($this->config['parseDate'],$row['created']);
          if ($row['createdon']) $row['createdon']=date($this->config['parseDate'],$row['createdon']);
          if ($row['editedon']) $row['editedon']=date($this->config['parseDate'],$row['editedon']);
      }

			// отрабатываем изображениt видео
			$row['image']=trim($row['image']);
			if ($row['image'] && !preg_match('/^(http\:|https\:)/i', $row['image'])) {
				$row['image']=$this->awesomeVideos->config['imageSourceBasePath'].ltrim($row['image'], '/');
			}
			// $this->config['part']
			$this->generateThumb($row, 'videoId', 'image');

			// отрабатываем изображение плейлиста
			if ($this->config['part']!=='playlist' &&  $row['playlist.image']=trim($row['playlist.image']) && !preg_match('/^(http\:|https\:)/i', $row['playlist.image'])) {
				$row['playlist.image']=$this->awesomeVideos->config['imageSourceBasePath'].ltrim($row['playlist.image'], '/');
			}
			// $this->generateThumb($row, 'playlistId', 'playlist');
			// $this->generateThumb($row, 'channelId', 'channel');

/*			// Prepare and process TVs
			if (!empty($tvs)) {
				foreach ($tvs as $tv) {
					if (!isset($process[$tv]) && !isset($prepare[$tv])) {continue;}

					// @var modTemplateVar $templateVar
					if (!$templateVar = $this->getStore($tv, 'tv')) {
						if ($templateVar = $this->modx->getObject('modTemplateVar', array('name' => $tv))) {
							$sourceCache = isset($prepareTypes[$templateVar->type])
								? $templateVar->getSourceCache($this->modx->context->get('key'))
								: null;
							$templateVar->set('sourceCache', $sourceCache);
							$this->setStore($tv, $templateVar, 'tv');
						}
						else {
							$this->writeLog('Could not process or prepare TV "'.$tv.'"');
							continue;
						}
					}

					$tvPrefix = !empty($this->config['tvPrefix']) ?
						trim($this->config['tvPrefix'])
						: '';
					$key = $tvPrefix . $templateVar->name;
					if (isset($process[$tv])) {
						$row[$key] = $templateVar->renderOutput($row['id']);
					}
					elseif (isset($prepare[$tv]) && is_string($row[$key]) && strpos($row[$key],'://') === false && method_exists($templateVar, 'prepareOutput')) {
						if ($source = $templateVar->sourceCache) {
							if ($source['class_key'] == 'modFileMediaSource') {
								if (!empty($source['baseUrl']) && !empty($row[$key])) {
									$row[$key] = $source['baseUrl'].$row[$key];
									if (isset($source['baseUrlRelative']) && !empty($source['baseUrlRelative'])) {
										$row[$key] = $this->modx->context->getOption('base_url',null,MODX_BASE_URL).$row[$key];
									}
								}
							}
							else {
								$row[$key] = $templateVar->prepareOutput($row[$key]);
							}
						}
					}
				}
			}*/
		}

		if (!empty($tvs)) {
			$this->writeLog('Prepared and processed TVs', microtime(true) - $time);
		}

		return $rows;
	}


	/**
	 * Loads awesomeVideos class to processor
	 *
	 * @return bool
	 */
	public function loadClass() {

		$classPath=$this->config['corePath'].'model/awesomevideos/';
		// if ( !$this->awesomeVideos = $this->modx->getService('awesomeVideos','awesomeVideos', $this->config['corePath'].'model/awesomevideos/', $this->config) ){
		if ( !$this->awesomeVideos = $this->modx->getService('awesomeVideos','awesomeVideos'
			, $this->config['corePath'].'model/awesomevideos/'
			, array(
				'log'=>array( 'log_status' => false ),
				'pagination' => $this->config['pagination']
			)
		) ){
			return false;
		}


		$this->writeLog('Config:');
		$this->writeLog($this->config);

		$this->writeLog($this->awesomeVideos->config,'Config: AwesomeVideos','WARN');

		return true;
	}

}