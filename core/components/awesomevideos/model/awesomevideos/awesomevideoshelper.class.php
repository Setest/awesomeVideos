<?php

abstract class awesomeVideosHelper{

  public function runSnippet($name, $config = array()) {
    if (!$name) return false;

    if($snippet = $this->modx->getObject('modSnippet', array(
      'name' => $name,
    ))){
      $f = $snippet->getScriptName();
        if(!function_exists($f)){
          if ($snippet->loadScript()){
              $snippet->setCacheable(false);
          }
      }
    }else{
      exit($this->modx->toJSON(array('success' => false, 'message' => 'Snippet not found')));
    }

    $snippetProperties = $snippet->getProperties();
    $config = array_merge($snippetProperties,$config);
    return $f($config);
  }


  /**
   * Returns current base url for pagination
   *
   * @return string $url
   */
  public function getBaseUrl() {
    if ($this->modx->getOption('friendly_urls')) {
      $q_var = $this->modx->getOption('request_param_alias', null, 'q');
      $q_val = isset($_REQUEST[$q_var])
        ? $_REQUEST[$q_var]
        : '';
        // echo 999;
      $this->req_var = $q_var;

      $host = '';
      switch ($this->config['scheme']) {
        case 'full':
          $host = $this->modx->getOption('site_url');
          break;
        case 'abs':
        case 'absolute':
          $host = $this->modx->getOption('base_url');
          break;
        case 'https':
        case 'http':
          $host = $this->pdoTools->config['scheme'] . '://' . $this->modx->getOption('http_host') . $this->modx->getOption('base_url');
          break;
      }
      $url = $host . $q_val;
    }
    else {
      $id_var = $this->modx->getOption('request_param_id', null, 'id');
      $id_val = isset($_GET[$id_var])
        ? $_GET[$id_var]
        : $this->modx->getOption('site_start');
      $this->req_var = $id_var;
// echo 777;
      $url = $this->modx->makeUrl($id_val, '', '', $this->pdoTools->config['scheme']);
    }

    return $url;
  }


  /**
   * Returns templates link
   *
   * @param array $params
   * @param bool $rewriteRequest при true переписывает имеющиеся параметры GET, иначе создает параметры на основе текущего URL
   * @param string $url
   * @param string $tpl
   *
   * @return string $href
   */
  public function makeLink($params=array(), $rewriteRequest=true, $url = '', $tpl = '' ) {
    if (empty($url)) {
      $url = $this->getBaseUrl();
    }

    $href = $url;


    $request = (!empty($_GET)) ? $_GET : array();

    $href .= strpos($href, '?') !== false
      ? '&'
      : '?';

    // нужно удалить пустые значения из $params
    $params = array_filter($params, create_function('$a','return $a!="";'));

    if ($this->modx->getOption('friendly_urls') ){
      unset($request[$this->req_var]);
      unset($params[$this->req_var]);
    }

    if (!empty($request)) {
      $params = ($rewriteRequest) ? array_merge($request,$params) : $params;
    }
    $href .= http_build_query( $params );

    if (!empty($href) && $this->modx->getOption('xhtml_urls', null, false)) {
      $href = preg_replace("/&(?!amp;)/","&amp;", $href);
    }

    $data = array(
      'request' => $request,
      'params' => $params,
      'href' => $href,
      'baseUrl' => $url,
    );

    return !empty($tpl)
      ? $this->getChunk($tpl, $data)
      : $data;
  }


  public function getParser() {
    return $this->parser=&$this->modx->getParser();
    // return $this->modx->parser;
  }


  /**
   * Fast processing of MODX tags.
   *
   * @param string $content
   * @param bool $processUncacheable
   *
   * @return mixed
   */
  public function fastProcess($content) {
    // так как я уже использую pdoParser
    // то можно не париться и делать так
    $maxIterations= intval($this->modx->getOption('parser_max_iterations', $params, 10));
    $this->modx->getParser()->processElementTags('', $content, false, false, '[[', ']]', array(), $maxIterations);
    $this->modx->getParser()->processElementTags('', $content, true, true, '[[', ']]', array(), $maxIterations);
    return $content;
  }

  public function fastProcessOld($content, $processUncacheable = true, $deleteUnprocessed = true) {
    $matches = array();
    $hasLexicon = (strpos($content, '[[%')!==false)?true:false;

    $this->getParser()->collectElementTags($content, $matches);

    $unprocessed = $pl = $vl = array();
// print_r($matches);
    foreach ($matches as $tag) {
      $tmp = $this->parser->processTag($tag, $processUncacheable);
// die();

      if ($tmp === $tag[0]) {
        $unprocessed[] = $tmp;
      }
      else {
        // если использовался lexicon и в нем нет нужных плейсхолдеров, то они вернуться
        // не измененные, поэтому его нам нужно очистить прежде чем возвращать.
        if ( $hasLexicon && $deleteUnprocessed && strpos($tmp, '[[+')!==false ){
          $tmp = preg_replace('/(\[\[\+.*?\]\])/i', '', $tmp);
        }

        // это временное решение... его нужно заменить на то что в pdoParser
        // или как то еще
        if (strpos($tag[0], ':') !== false) {
          /** @var pdoTag $object */
          $tagf = new modFieldTag($this->modx);
          $tagf -> _content = $output;
          $tagf -> setTag($tag[0]);
          $tagf -> setToken($token);
          $tagf -> setContent(ltrim(rtrim($tag[0],']'), '[!'.$token));
          $tagf -> setCacheable(!$processUncacheable);
          $tagf -> process();
          $tmp =  $tagf->_output;
        }

        // echo($tag[0]);
        $pl[] = $tag[0];
        $vl[] = $tmp;
      }
    }
// print_r($vl);
// die();
    $content = str_replace($pl, $vl, $content);
    $content = str_replace($unprocessed, '', $content);

    return $content;
  }


  /**
   * Transform array to placeholders
   *
   * @param array $array
   * @param string $plPrefix
   * @param string $prefix
   * @param string $suffix
   * @param bool $uncacheable
   *
   * @return array
   */
  public function makePlaceholders(array $array = array(), $plPrefix = '', $prefix = '[[+', $suffix = ']]', $uncacheable = true) {
    $result = array('pl' => array(), 'vl' => array());

    $uncached_prefix = str_replace('[[', '[[!', $prefix);
    foreach ($array as $k => $v) {
      if (is_array($v)) {
        $result = array_merge_recursive($result, $this->makePlaceholders($v, $plPrefix . $k.'.', $prefix, $suffix, $uncacheable));
      }
      else if ( strpos($v, ':')===false ){
      // else {
        $pl = $plPrefix.$k;
        $result['pl'][$pl] = $prefix.$pl.$suffix;
        $result['vl'][$pl] = $v;
        if ($uncacheable) {
          $result['pl']['!'.$pl] = $uncached_prefix.$pl.$suffix;
          $result['vl']['!'.$pl] = $v;
        }
      }
    }

    return $result;
  }


  /**
   * Метод аналогичен работе с кукисами, за тем исключением что не выдаеет значение в браузер пользователя.
   *
   * @param [string] $name - имя переменной из которой берем данные
   * @param [string] $type - раздел хранения (data(default), snippet, chunk, без разницы)
   */
  public function getSessionStore($name, $type = 'data') {
    if (!$this->sessionStoreName){
      $this->writeLog('Имя сессии не указано','', 'ERROR');
      return false;
    }
    if ( $_SESSION[$this->sessionStoreName][$type][$name]
      &&
      (
        !$_SESSION[$this->sessionStoreName][$type][$name]['lifetime']
        ||
        ($_SESSION[$this->sessionStoreName][$type][$name]['time'] + $_SESSION[$this->sessionStoreName][$type][$name]['lifetime']) >= time()
      )
    ){
      return $_SESSION[$this->sessionStoreName][$type][$name]['data'];
    }else{
      unset($_SESSION[$this->sessionStoreName][$type][$name]);
    }
    return false;
  }

  /**
   * Метод аналогичен работе с кукисами, за тем исключением что не выдаеет значение в браузер пользователя.
   *
   * @param [string] $name - имя переменной в которой будем хранить данные
   * @param [mixed] $data - данные для хранения
   * @param [int] $lifetime - время жизни данных сессии в секундах
   * @param [string] $type - раздел хранения (data(default), snippet, chunk, без разницы)
   */
  public function setSessionStore($name, $data, $lifetime = 0 ,$type = 'data') {
    if (!$this->sessionStoreName){
      $this->writeLog('Имя сессии не указано','', 'ERROR');
      return false;
    }
    if (!$_SESSION[$this->sessionStoreName]) $_SESSION[$this->sessionStoreName] = array();
    // $_SESSION[$this->sessionStoreName][$type]
    // && $_SESSION[$this->sessionStoreName][$type][$name]
    // &&
    $_SESSION[$this->sessionStoreName][$type][$name]=array(
      'data' => $data,
      'time' => time(),
      'lifetime' => $lifetime,
    );
    // $this->writeLog('SAVED '.$this->sessionStoreName,'', 'ERROR');
    return true;
  }

  /**
   * удаляет данные из сессии
   *
   * @param [string] $name - имя переменной в которой будем хранить данные
   * @param [string] $type - раздел хранения (data(default), snippet, chunk, без разницы)
   */
  public function unsetSessionStore($name, $type = 'data') {
    if (!$this->sessionStoreName){
      $this->writeLog('Имя сессии не указано','', 'ERROR');
      return false;
    }
    unset( $_SESSION[$this->sessionStoreName][$type][$name] );
    return true;
  }

  /**
   * Возвращает набор параметров
   * @param  [string] $name имя набора
   * @return [array]
   */
  protected function getSetOfProperties($name) {
    $result=array();
    $this->writeLog("Ищю: $name",'', 'ERROR');
    if ($name && stripos($name, 'aw_')==0 ){
      $setOfParam = $this->modx->getObject('modPropertySet',array('name'=>$name));
      if ($setOfParam){
        $result = $setOfParam->getProperties();
        $this->writeLog("Получил набор параметров: ".print_r($result,true),'', 'ERROR');
      }else{
        $this->writeLog("Набор параметров: $name, не существует",'', 'ERROR');
      }
    }
    return $result;
  }

  /**
   * Возвращает параметры по-умолчанию у снипета
   * @param  [object|string|int] $name объект класса сниппет, или имя или id сниппета
   * @param  [string] $criteria регулярное выражение по которому производиться поиск параметра по имени группы
   * @param  [bool] $fullProp возвращает все свойства включая группы, тип, варианты и т.д.
   * @return [array]
   */
  protected function getSnippetProperties($name, $criteria = null, $fullProp = false, $getIfEmpty = false) {
    $result=array();
    $this->writeLog("Ищю: $name",'', 'ERROR');
    if ($name){
      $snippet = is_object($name) && $name instanceof modSnippet
        ? $name
        : $this->modx->getObject('modSnippet', array('id' => $name, 'OR:name:=' => $name))
      ;
      if ($snippet){
        $name = $snippet->get( 'name' );
        if ($fullProp) return $snippet->get( 'properties' );
        if (!$criteria){
          $result = $snippet->getProperties();
          // $result = $snippet->properties;
          $this->writeLog("Получили параметры по-умолчанию у снипета '{$name}': ".print_r($result,true),'', 'ERROR');
          return $result;
        }

        foreach ($snippet->get( 'properties' ) as $key => $value) {
          if ( preg_match($criteria,$value['area_trans']) && (
               $getIfEmpty || ( !$getIfEmpty && !empty($value['value']) )
            ) ){
            $result[$key] = $value['value'];
          }
        }
        $this->writeLog("Получили параметры у снипета '{$name}' с критерием группы '{$criteria}' : ".print_r($result,true),'', 'ERROR');
        // echo ('<pre>');
        // print_r($result);die();
      }else{
        $this->writeLog("Сниппета: {$name}, не существует",'', 'ERROR');
      }
    }
    return $result;
  }


  /**
   * Возвращает значение константы класса modX
   * @param  [string] $const имя константы
   * @return [int]  значение константы
   */
  protected function _getModxConst($const){
    // $this->modx->log(modX::LOG_LEVEL_INFO,'CONST: '.$const);
    // $res=(is_numeric($const))? $const : constant('modX::'.strtoupper($const));
    $const = (!is_numeric($const) && strrpos(strtoupper($const), 'LOG_LEVEL_')===false)?'LOG_LEVEL_'.$const : $const;
    // $this->modx->log(modX::LOG_LEVEL_INFO,'CONST RESULT: '.$const);
    return (is_numeric($const))? $const : constant('modX::'.strtoupper($const));
  }

  /**
   * Восстанавливает предыдущее состояние лога, чтобы другие сниппеты могли выдать свою инфу
   * @return [type]           [description]
   */
  public function logSetPrevState(){
    if (!$this->logOld || is_array($this->logOld)) return false;
    $this->modx->setLogLevel($this->logOld['log_level']);
    $this->modx->setLogTarget($this->logOld['log_target']);
    return true;
  }

  /**
   * Настраиваем будущее поведение лога
   * @param  [type] $message  [description]
   * @param  string $def      [description]
   * @param  [type] $logLevel [description]
   * @return [type]           [description]
   */
  public function logConfig( &$config=array() ){

    $config = ($config)?$config:array();

    // $this->modx->log(modX::LOG_LEVEL_ERROR,'TTTTARGET123: '.$config['log_target']);
    // $this->modx->log(modX::LOG_LEVEL_ERROR,'TTTTARGET123: '.print_r($config, true));
    // $this->modx->log(modX::LOG_LEVEL_ERROR,'TTTTARGET123: '.print_r($this->modx->getLogTarget(), true));
    // modFileRegister

    // print_r($config);
    // echo 7777;

    // $config1=array(
    //   'log_isstyled'=>777,
    // );

    if ( (is_string($config['log_status']) && $config['log_status']=='true') || $config['log_status']=='1' ){
      $config['log_status'] = true;
    }

    $config=array_merge(array(
      'log_filename'=>$this->classKey ? $this->classKey : 'log',
      'log_status'=>false,
      'log_isstyled'=>true,
      'log_placeholder'=>false,
      'log_detail'=>false,
      'log_target' => $this->modx->getOption('log_target', null, 'ECHO'),
      'log_level'=> $this->modx->getOption('log_level', null, 1)
    ),$config);

    $config['log_target'] = ( $config['log_target'] == 'SYSTEM' || $config['log_target'] == 'AUTO' )
      ? $this->modx->getOption('log_target', null, 'ECHO')
      : $config['log_target']
    ;

    // $config['log_level_val'] = $this->_getModxConst($config['log_level']);  // проверочное значение, для меня.
    $config['log_level'] = $this->_getModxConst($config['log_level']);  // проверочное значение, для меня.
 // is_object( log_target ) modFileRegister

    if ($config['log_status']==true){
      $this->modx->message = null;


      // $this->modx->log(modX::LOG_LEVEL_ERROR,'Установленный уровень отладки0: '.print_r($this->config['log'], true) );
      // $this->modx->log(modX::LOG_LEVEL_INFO,'Установленный уровень отладки1: '.$this->config['log']['log_level']);
      // $this->modx->log(modX::LOG_LEVEL_INFO,'Установленный уровень отладки: '.$this->modx->getLogLevel());
      // $this->modx->log(modX::LOG_LEVEL_ERROR,'ПОСЛЕ: '.$this->modx->getLogLevel());
      //

      $defOptions=array();
      $date = date('Y-m-d__H-i-s');

      if ($config['log_target']=="FILE"){
        $config['log_filename'] = $defOptions['filename'] = "{$config['log_filename']}_{$date}.log";
        $config['log_fullPath']=$this->config['corePath']."cache/logs/{$config['log_filename']}";
        $config['log_urlPath']= $this->modx->getOption('site_url', null, '')."core/cache/logs/{$config['log_filename']}";
      }else{
        unset($config['log_filename']);
      }

      // $defTarget = $this->modx->getLogTarget();
      $defTarget = ( !is_object($this->config['log']['log_target']) ) ? 'FILE' : $this->config['log']['log_target'] ;


      $this->logOld=array(
        'log_target' => $this->modx->setLogTarget(array(
          // 'target' => $this->config['log']['log_target'],
          'target' => $defTarget,
          'options' => $defOptions
        )),
        'log_level' => $this->modx->setLogLevel( $this->_getModxConst($config['log_level']) )
      );
      // $curTarget=&$this->modx->getLogTarget();

   // echo ($curTarget."<br/>");

      // if ($curTarget['target']=='HTML' && $config['log_isstyled']){
      if ( ($config['log_target']=='HTML' || $config['log_target']=='PLACEHOLDER') && $config['log_isstyled']){
        // $this->modx->regClientCSS('assets/css/123.css');
        $this->modx->regClientCSS("
          <style>
            div[class^='wrap_log_'] {
              background: #FFFCE6;
              margin: 10px 0;
              padding: 5px 10px;
            }
            div[class^='wrap_log_'] h5{
              background: #D5E5FF;
              font-weight: bold;
              margin: 0;
              text-decoration: underline;
            }
            .wrap_log_error h5 { color: #EF2727; }
            .wrap_log_warn  h5 { color: blue; }
            .wrap_log_info  h5 { color: #01BB17; }
            .wrap_log_debug h5 { color: #AEAEAE; }
            .wrap_log_fatal h5 { font-size: 1.3em; background: red !important; }

            div[class^='wrap_log_'] div[class^='log_'] {
              word-break: break-word;
              word-wrap: break-word;
            }
            .log_error { color: #EF2727; font-weight: bold; }
            .log_warn { color: blue; }
            .log_info { color: #01BB17; }
            .log_debug { color: #AEAEAE; }
          </style>
        ");
      }


      if ($this->config['log']['log_detail']){
        $this->writeLog("ModX version:".$this->modx->getOption('settings_version'));
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
  }

    /**
     * Gets a logging level as a string representation.
     *
     * @param integer $level The logging level to retrieve a string for.
     * @return string The string representation of a valid logging level.
     */
    protected function _getLogLevel($level) {
        switch ($level) {
            case modX::LOG_LEVEL_DEBUG :
                $levelText= 'DEBUG';
                break;
            case modX::LOG_LEVEL_INFO :
                $levelText= 'INFO';
                break;
            case modX::LOG_LEVEL_WARN :
                $levelText= 'WARN';
                break;
            case modX::LOG_LEVEL_ERROR :
                $levelText= 'ERROR';
                break;
            default :
                $levelText= 'FATAL';
        }
        return $levelText;
    }

  /**
   * Записывает лог, в случае если тот установлен в конфиге
   * @param  [string, array] $message  сообщения для лога
   * @param  [string] $def      уровень различия, можно послать любое значение, оно отразиться в строке лога как префикс, можно передавать например номер строки __LINE__
   * @param  [string] $logLevel уровень сообщения, установлен по-умолчанию в конфиге, но можно послать: INFO, WARN, ERROR, FATAL, DEBUG
   * @return [bool]   в зависимости от совершения записи в лог
   */
  public function writeLog( $message, $def='', $logLevel = 'INFO' ){
    // $this->modx->log(modX::LOG_LEVEL_WARN,"XXX: ".print_r($this->config['log'],true ));
    if (!$this->config['log']['log_status']){return false;}

    if (is_array($message)) $message=print_r($message,true);
    // $curTarget=&$this->modx->getLogTarget();
    // $curTarget = [];
    $curTarget['target']=$this->config['log']['log_target'];
    $clearMessage = $message;

    // print_r ($this->config['log']);

    // strftime('%Y-%m-%d %H:%M:%S')
    // $microtime=$this->modx->getMicroTime();
    // $time= sprintf( "%2.4f s", $this->modx->getMicroTime() );
    $time = date("Y-m-d H:i:s");
    $logDef = ($def)?"# $def # ":'';
    $delim = ($this->config['log']['log_isstyled']) ? "\r\n" : '<br/>';

    // $logLevel = (!isset($logLevel)) ? $this->config['log']['log_level'] : $logLevel;
    $logLevel=$this->_getModxConst($logLevel);
    $logLevelStr=$this->_getLogLevel($logLevel);

    // if ($curTarget['target']=='ECHO' || is_object($curTarget['target'])) $message="<pre>$message</pre>";
    if ($curTarget['target']=='HTML' || is_object($curTarget['target'])) $message="<pre>$message</pre>";

    if ($curTarget['target']=='HTML'){
      // $status=array(
      //   0 => array('style'=>'' ,'class'=>'', 'bcg'=>''), // FATAL
      //   1 => array('style'=>'' ,'css'=>'', 'bcg'=>''), // ERROR
      //   2 => array('style'=>'' ,'css'=>'', 'bcg'=>''), // WARN
      //   3 => array('style'=>'' ,'css'=>'', 'bcg'=>''), // INFO
      //   4 => array('style'=>'' ,'css'=>'', 'bcg'=>''), // DEBUG
      // );
      $message="<div class='log_".strtolower($logLevelStr)."'>{$message}</div>";
    }


    // $delim=($curTarget['target']=='HTML')?'<br/>':'\n\r';
    // echo $this->config['log']['log_placeholder'];
    if(!$this->config['log']['log_placeholder'] && $curTarget && is_object($curTarget['target'])){
      // этот вариант возможен только при отладки в консоли
      // иначе при использовании метода в качестве вывода данных для консоли, в циклах данные не выводятся
      flush();
      usleep(100);  // или 1000?
    }


    // if ($curTarget['target']=='HTML' && $this->$config['log']['log_isstyled']){
    if ($this->config['log']['log_isstyled'] && $curTarget['target']!=='ECHO'){
      // echo 111;
      $message = '<div class="wrap_log_'.strtolower($logLevelStr).'"><h5>[' . strftime('%Y-%m-%d %H:%M:%S') . '] (' . $logLevelStr .' '. $logDef . ')</h5><pre>' . $message . '</pre></div>' . "\n";
      $this->_logContent[]=$message;
    }else{
      // $this->_logContent.="#{$time}# ".$clearMessage;
      $this->_logContent[]="{$logLevelStr} [{$time}] $logDef $clearMessage";
    }

    if ( $this->config['log']['log_placeholder'] && $curTarget['target']=='PLACEHOLDER' ){
      // echo $this->config['log']['log_placeholder'];
      $this->modx->setPlaceholder($this->config['log']['log_placeholder'], implode($delim, $this->_logContent));
    // }else if($curTarget['target']=='HTML'){
    }else if( is_object($curTarget['target']) ){
      $this->modx->log($logLevel, $message, '', $def);
    }else{
      echo $message;
    }

    return false;
  }


  /**
   * Проверка на существование удаленного файла
   */
  protected function _remote_file_exists($url){
      // return(bool)preg_match('~HTTP/1\.\d\s+200\s+OK~', @current(get_headers($url)));
    $headers = get_headers($url);
    $result = (bool)preg_match( '~HTTP/1\.\d\s+200\s+OK~', @current($headers) );

    $this->writeLog('FILESIZE: '.print_r($headers, true),'','ERROR');

    if ( $fileExist && (bool)preg_match('/^Content-Length: *+\K\d++$/im', implode("\n", $headers), $fileSize ))
    {
      $this->writeLog('FILESIZE: '.print_r($fileSize, true),'','ERROR');
      $result['filesize']=(int)$fileSize[0];
    }
    return $result;
  }

  /**
   * Получим размер файла (как локального так и удаленного)
   */
  protected function _remote_filesize($url) {
      static $regex = '/^Content-Length: *+\K\d++$/im';
      if (!$fp = @fopen($url, 'rb')) {
          return false;
      }
      if (
          isset($http_response_header) &&
          preg_match($regex, implode("\n", $http_response_header), $matches)
      ) {
          return (int)$matches[0];
      }
      return strlen(stream_get_contents($fp));
  }


  /**
  * Копирование файла с проверкой на существование исходного файла
  * причем если рузультирующий файл есть он будет перезаписан
  *
  * @param $from источник
  * @param $to получатель
  * @return bool статус копирования
  */
  protected function copyFile($from, $to){
      $flag = false;
      if($this->_remote_file_exists($from) && copy($from, $to)){
          // var_dump(file_exists(https://i.ytimg.com/vi/afXPtUw2Knk/hqdefault.jpg));
          chmod($to, octdec($this->config['new_file_permissions']));
          $flag = true;
      }
      return $flag;
  }


  protected function _remoteFileData($f) {
      $h = get_headers($f, 1);
      $result=array();
      if (stristr($h[0], '200')) {
          foreach($h as $k=>$v) {
              $result[strtolower(trim($k))]=$v;
              // if(strtolower(trim($k))=="last-modified") {
                  // return strtotime($v);
              // }
          }
      }
      return $result;
  }


}