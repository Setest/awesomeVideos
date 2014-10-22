<?php
// set_time_limit(10);
// ini_set("max_execution_time", "600"); // включаем 10 минут на ограничение работы скрипта
// ini_set("max_input_time", "600"); // включаем 10 минут на ограничение работы скрипта

// error_reporting(E_ALL ^ E_NOTICE); ini_set('display_errors', true);

/**
 * The base class for awesomeVideos.
 */
class awesomeVideos {
	/* @var modX $modx */
	public $modx;
  protected $vidList = array();
  protected $importList = array();  // массив документов для импорта
  public $cacheDir = ''; // итоговый путь папки кеша
  public $importType = 'Videos'; // тип данных для импорта, есть Videos и Playlists

  public $classKey = 'awesomeVideosItem';

  /** @var array все настройки класса */
  // protected $config = array();
  // protected $_settings = array();
  // private $_logContent = '';	// хранит весь лог который потом передастся через плейсхолдер

  /**
   * @param modX &$modx A reference to the modX object
   * @param array $config An array of configuration options
   */
	function __construct(modX &$modx, array $config = array()) {
		$this->modx =& $modx;

		$corePath = $this->modx->getOption('awesomevideos_core_path', $config, $this->modx->getOption('core_path') . 'components/awesomevideos/');
		// $corePath =dirname(__FILE__);
		$assetsUrl = $this->modx->getOption('awesomevideos_assets_url', $config, $this->modx->getOption('assets_url') . 'components/awesomevideos/');
		$connectorUrl = $assetsUrl . 'connector.php';

		$this->config = array_merge(array(
			'assetsUrl' => $assetsUrl,
			'cssUrl' => $assetsUrl . 'css/',
			'jsUrl' => $assetsUrl . 'js/',
			'imagesUrl' => $assetsUrl . 'images/',
			'connectorUrl' => $connectorUrl,

			'corePath' => $corePath,
			'basePath' => $corePath,
			'processorsPath' => $corePath . 'processors/',
			'modelPath' => $corePath . 'model/',
			'chunksPath' => $corePath . 'elements/chunks/',
			'templatesPath' => $corePath . 'elements/templates/',
			'chunkSuffix' => '.chunk.tpl',
			'snippetsPath' => $corePath . 'elements/snippets/',

			'cacheTime' => 1800,	// кеш работает и в менеджере и у клиента

			'restHost' => 'http://gdata.youtube.com/',
      'restPath' => 'feeds/api/users/username/uploads',
      'restMethod' => 'get',
      'sitePath' => MODX_BASE_PATH,
      //Права на только что созданный файл
      'new_file_permissions' => (int)$this->modx->getOption('awesomeVideos.video.newFilePermissions',null,'0664'),

      'imageSourceId' => $this->modx->getOption('awesomeVideos.video.imageSourceId'),
      'imageNoPhoto' => $this->modx->getOption('awesomeVideos.video.imageNoPhoto',null, $assetsUrl.'img/noimage.jpg'),
      'imageCachePath' => $this->modx->getOption('awesomeVideos.video.imageCachePath',null, $assetsPath.'images/'),
      'import' => array (
          'apiKey'=>$this->modx->getOption('awesomeVideos.youtube.apikey'),
          'active'=>$this->modx->getOption('awesomeVideos.youtube.active',null,true), //make imported videos inactive by default
          'maxResults'=>50    // максимальное кол-во записей которое мы можем получить за один раз, 50 - это ограничение API youtube.
      ),
      'ctx' => $this->modx->getOption('ctx', $config, $this->modx->context->key),

      'topicSource' => $this->modx->getOption('awesomeVideos.video.topic.source', null, false),

      'log' => array(
      	'log_filename'=>'awesomeVideos',
      	'status'=>$this->modx->getOption('awesomeVideos.main.log', null, true),
      	'log_placeholder'=>$this->modx->getOption('log_placeholder', null, false), //getImgLog
      	'log_detail'=>$this->modx->getOption('log_detail', null, false),
				// 'log_target'=>( 1==1 )
					// ? $this->modx->getLogTarget()
					// ? $this->modx->getLogTarget()->subscriptions[0]	// это на случай вывода в консоль
					// : false,
				// 'log_target' => $this->modx->getOption('awesomeVideos.main.log_target', null, 'ECHO'),	// FILE, HTML, ECHO
				'log_target' => !is_null($this->modx->getOption('log_target', $config, null))
					? $this->modx->getOption('log_target', $config['log'])
					: $this->modx->getOption('log_target', null, 'ECHO'),	// FILE, HTML, ECHO
        'log_level'=>$this->_getModxConst($this->modx->getOption('log_level', $config, 'LOG_LEVEL_INFO')),  // INFO, WARN, ERROR, FATAL, DEBUG
        // 'log_level'=>$this->_getModxConst(3), // INFO, WARN, ERROR, FATAL, DEBUG
      	// 'log_level'=>123,	// INFO, WARN, ERROR, FATAL, DEBUG
      ),

		), $config);

    if (strpos($this->config['topicSource'], '[') === false ) {
      // это только для быстрой выборки статичных данных внутри грида.
      $this->config['topicSource']=false;
    }

    $this->config['imageCachePath']=ltrim($this->config['imageCachePath'],'/');
    $this->config['imageCachePath']=rtrim($this->config['imageCachePath'],'/').'/';

    if (isset($this->config['imageSourceId'])){
        $MediaSource = $this->modx->getObject('modMediaSource', array('id' => $this->config['imageSourceId']));
        $msproperties = $MediaSource->getProperties(true);
        $this->config = array_merge($this->config, array(
            'imageSourceFullBasePath'=>$this->config['sitePath'].$msproperties['basePath']['value'],
            'imageSourceBasePath'=>$msproperties['basePath']['value'],
            'imageSourceBaseUrl'=>$msproperties['baseUrl']['value']
        ));
    }
    $this->config['imageNoPhoto']=(!empty($this->config['imageNoPhoto']))?$this->config['imageNoPhoto']:$assetsUrl.'img/noimage.jpg';

    $this->cacheDir=$this->config['imageSourceFullBasePath'].$this->config['imageCachePath'];

    // $this->writeLog("LOG LEVEL:".$this->modx->getOption('log_level', $config, 'LOG_LEVEL_WARN'));
    // $this->writeLog("LOG LEVEL2:".$this->_getModxConst($this->modx->getOption('log_level', $config, 'LOG_LEVEL_WARN')));
    // $this->writeLog("LOG LEVEL3:".$this->_getModxConst('LOG_LEVEL_WARN'));


    if ($this->config['log']['status']==true){
      $this->modx->message = null;
      // $this->modx->log(modX::LOG_LEVEL_INFO,'Установленный уровень отладки0: '.print_r($this->config['log'], true) );
      // $this->modx->log(modX::LOG_LEVEL_INFO,'Установленный уровень отладки1: '.$this->config['log']['log_level']);
      // $this->modx->log(modX::LOG_LEVEL_INFO,'Установленный уровень отладки: '.$this->modx->getLogLevel());
      $this->modx->setLogLevel( $this->_getModxConst($this->config['log']['log_level']) );
      // $this->modx->log(modX::LOG_LEVEL_ERROR,'ПОСЛЕ: '.$this->modx->getLogLevel());

      $date = date('Y-m-d__H-i-s');  // использовать в выводе даты : - нельзя, иначе не создается лог в файл
      $logFileName="{$this->config['log']['log_filename']}_$date.log";

      if ($this->config['log']['log_target']){
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



    if (!isset($this->config['cacheKey'])){
      $this->config['cacheKey']=$this->getCacheKey();
      $this->modx->cacheManager->set('awesomevideos/prep_' . $this->config['cacheKey'], $this->config, $this->config['cacheTime']);
    }

    $this->modx->addPackage('awesomevideos', $this->config['modelPath']);
    $this->modx->lexicon->load('awesomevideos:default');
  }

  /**
   * Возвращает значение константы класса modX
   * @param  [string] $const имя константы
   * @return [mixed]  значение константы
   */
  private function _getModxConst($const){
    // $this->modx->log(modX::LOG_LEVEL_INFO,'CONST: '.$const);
    // $res=(is_numeric($const))? $const : constant('modX::'.strtoupper($const));
    // $this->modx->log(modX::LOG_LEVEL_INFO,'CONST RESULT: '.$res);
    return (is_numeric($const))? $const : constant('modX::'.strtoupper($const));
  }

	/**
	 * Записывает лог, в случае если тот установлен в конфиге
	 * @param  [string, array] $message  сообщения для лога
	 * @param  [string] $def      уровень различия, можно послать любое значение, оно отразиться в строке лога как префикс
	 * @param  [string] $logLevel уровень сообщения, установлен по-умолчанию в конфиге, но можно послать: INFO, WARN, ERROR, FATAL, DEBUG
	 * @return [bool]   в зависимости от совершения записи в лог
	 */
	public function writeLog( $message, $def='', $logLevel = null ){
		// $this->modx->log(modX::LOG_LEVEL_WARN,"XXX: ".print_r($this->config['log'],true ));
		if (!$this->config['log']['status']){return false;}

		if (!isset($logLevel)){
			$logLevel = $this->config['log']['log_level'];
		}else if (is_string($logLevel)) {
			$logLevel=$this->_getModxConst("LOG_LEVEL_".$logLevel);
		}

		if (is_array($message)) $message=print_r($message,true);

		if ($this->config['log']['log_target']=='ECHO') $message="<pre>$message</pre>";

		// перезапишем данные в плейсхолдере
		// strftime('%Y-%m-%d %H:%M:%S')
		$time= sprintf( "%2.4f s", $this->modx->getMicroTime() );
		$this->_logContent.="#{$time}# ".$message;

		if ($this->config['log']['log_placeholder']){
			$this->modx->setPlaceholder($this->config['log']['log_placeholder'],$this->_logContent);
		}else{
			flush();	// иначе в циклах херня происходит
      usleep(1000); //
      $this->modx->log($logLevel, '<pre>'.$message.'</pre>', '', $def);
      // $this->modx->log(1, '<pre>'.$message.'</pre>', '', $def);
      // $this->modx->log(modX::LOG_LEVEL_WARN,'Class is not already loaded');
		}
		return false;
	}

  /**
   * Initializes the class into the proper context
   *
   * @access public
   * @param string $ctx
   */
  public function initialize($ctx = 'web', $scriptProperties = array()) {
      $this->config = array_merge($this->config, $scriptProperties);
      $this->config['ctx'] = $ctx;
      if (!empty($this->initialized[$ctx])) {
          return true;
      }

      switch ($ctx) {
          case 'mgr':
            if (!$this->modx->loadClass('awesomeVideos.request.awesomeVideosControllerRequest',$this->config['modelPath'],true,true)) {
                return 'Could not load controller request handler.';
            }
            $this->request = new awesomeVideosControllerRequest($this);
            return $this->request->handleRequest();
          break;
          default:
          	// подгружаем все для клиента
						/*$this->config = array_merge($this->config, $scriptProperties);
						$this->config['ctx'] = $ctx;
						//$initializing = !empty($this->modx->loadedjscripts[$this->config['jsUrl'] . 'web/config.js']);

						if (!defined('MODX_API_MODE') || !MODX_API_MODE) {

					    // if ($css = $this->modx->getOption('awesomeVideos.video.frontend_css')) {
					    //     $this->modx->regClientCSS($this->config['cssUrl'].$css);
					    // }

					    // if ($js = trim($this->modx->getOption('awesomeVideos.video.frontend_js'))) {
					    //     if (!empty($js) && preg_match('/\.js/i', $js)) {
					    //         $this->modx->regClientScript(preg_replace(array('/^\n/', '/\t{7}/'), '', '
					    //         <script type="text/javascript">
					    //             if(typeof jQuery == "undefined") {
					    //                 document.write("<script src=\"'.$this->config['jsUrl'].'web/lib/jquery.min.js\" type=\"text/javascript\"><\/script>");
					    //             }
					    //         </script>
					    //         '), true);
					    //         $this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));
					    //     }
					    // }

							$config = $this->makePlaceholders($this->config);
							if ($css = trim($this->modx->getOption('mse2_frontend_css'))) {
								$this->modx->regClientCSS(str_replace($config['pl'], $config['vl'], $css));
							}
							if ($js = trim($this->modx->getOption('mse2_frontend_js'))) {
								$this->modx->regClientScript(preg_replace(array('/^\n/', '/\t{7}/'), '', '
								<script type="text/javascript">
								if(typeof jQuery == "undefined") {
									document.write("<script src=\"'.$this->config['jsUrl'].'web/lib/jquery.min.js\" type=\"text/javascript\"><\/script>");
								}
								</script>
								'), true);
								$this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));
							}
						}*/
          break;
      }
      return true;
	  }

		/**
		 * Returns key for cache of specified options
		 *
		 * @var mixed $options
		 *
		 * @return bool|string
		 */
		protected function getCacheKey($options = array()) {
			if (empty($options)) {$options = $this->config;}

			if (is_array($options)) {
				$options['cache_user'] = isset($options['cache_user'])
					? (integer) $options['cache_user']
					: $this->modx->user->id;
			}

			return sha1(serialize($options));
		}

    /**
     * Gets a Chunk and caches it; also falls back to file-based templates
     * for easier debugging.
     *
     * @access public
     * @param string $name The name of the Chunk
     * @param array $properties The properties for the Chunk
     * @return string The processed content of the Chunk
     */
    public function getChunk($name,$properties = array()) {
        $chunk = null;
        if (!isset($this->chunks[$name])) {
            $chunk = $this->_getTplChunk($name);
            if (empty($chunk)) {
                $chunk = $this->modx->getObject('modChunk',array('name' => $name));
                if ($chunk == false) return false;
            }
            $this->chunks[$name] = $chunk->getContent();
        } else {
            $o = $this->chunks[$name];
            $chunk = $this->modx->newObject('modChunk');
            $chunk->setContent($o);
        }
        $chunk->setCacheable(false);
        return $chunk->process($properties);
    }
    /**
     * Returns a modChunk object from a template file.
     *
     * @access protected
     * @param string $name The name of the Chunk. Will parse to name.$postfix
     * @param string $postfix The default postfix to search for chunks at.
     * @return modChunk/boolean Returns the modChunk object if found, otherwise
     * false.
     */
    protected function _getTplChunk($name,$postfix = '.chunk.tpl') {
        $chunk = false;
        $f = $this->config['chunksPath'].strtolower($name).$postfix;
        if (file_exists($f)) {
            $o = file_get_contents($f);
            $chunk = $this->modx->newObject('modChunk');
            $chunk->set('name',$name);
            $chunk->setContent($o);
        }
        return $chunk;
    }



    // public function getData($properties = array()) {
    //     $this->modx->log(modX::LOG_LEVEL_INFO,'Запускаю импорт');
    //     if ($this->parse_sources()){
    //         $this->modx->log(modX::LOG_LEVEL_WARN,'Завершено успешно');
    //     }else{
    //         $this->modx->log(modX::LOG_LEVEL_ERROR,'Есть ошибки.');
    //     }
    // }

    /**
     * Импорт плейлистов
     * @param  array  $properties [description]
     * @return [type]             [description]
     */
/*    public function importPlaylists($properties = array()) {
        $this->writeLog("Запускаю импорт...");
        $this->importType = 'Playlists';
        if ($this->parse_sources()){
            $this->writeLog("Завершено успешно",'','WARN');
        }else{
            $this->writeLog("Импорт завершен с ошибками",'','ERROR');
        }
    }*/


    /**
     * Импорт видеороликов
     * @param  array  $properties [description]
     * @return [type]             [description]
     */
    public function import($properties = array()) {
        $this->classKey='awesome'.{$this->importType}.'Item';
        $this->writeLog("Запускаю импорт...");
	    	$this->writeLog("Текущий classKey: {$this->classKey}");
        // $this->modx->log(modX::LOG_LEVEL_INFO,'Запускаю импорт...');
        if ($this->parse_sources()){
			    	$this->writeLog("Завершено успешно",'','WARN');
        }else{
			    	$this->writeLog("Импорт завершен с ошибками",'','ERROR');
        }
    }


    /**
     * Проверка на существование удаленного файла
     */
    private function _remote_file_exists($url){
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
    private function _remote_filesize($url) {
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

    /**
     * Создание папки
     *
     * @param $dir полный путь к папке которую необходимо создать
     * @return bool
     */
    protected function makeDir( $dir ){
        $flag = $this;
        $dir=isset($dir)?$dir:$this->cacheDir;
        if(!file_exists($dir)){
            $this->modx->getService('fileHandler','modFileHandler');
            $dirObj = $this->modx->fileHandler->make($dir, array(),'modDirectory');
            if(!is_object($dirObj) || !($dirObj instanceof modDirectory)) {
                $flag = false;
                $this->writeLog('Could not get class modDirectory','','ERROR');
            }
            if($flag){
              if(!(is_dir($dir) || $dirObj->create())){
                $flag = false;
                $this->writeLog( "Could not create cache dir: <i>{$dir}</i>",'','ERROR');
              }
            }else{
              $this->writeLog("Создали папку кеша: <i>{$dir}</i>",'','WARN');
            }
        }else{
            $this->writeLog("Папка кеша существует: <i>{$dir}</i>",'','WARN');
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

    protected function _fileExt($contentType){
        $map = array(
            // 'application/pdf'   => '.pdf',
            // 'application/zip'   => '.zip',
            // 'text/css'          => '.css',
            // 'text/html'         => '.html',
            // 'text/javascript'   => '.js',
            // 'text/plain'        => '.txt',
            // 'text/xml'          => '.xml',
            'image/gif'           => '.gif',
            'image/jpeg'          => '.jpg',
            'image/png'           => '.png',
            'image/x-windows-bmp' => '.bmp',
            'image/x-icon'        => '.ico',
        );
        if (isset($map[$contentType])){
            return $map[$contentType];
        }else{
            return '';
        }
    }


    protected function _createCacheFile($f,$videoId) {
      $flag = false;
      // проверяем существует ли папка кеша для изображений, если нет создаем ее
      // $cacheDir=$this->config['imageSourceFullBasePath'].$this->config['imageCachePath'];
      // if ($this->makeDir($cacheDir)) {
          // копируем файлик и если успешно передаем значение обратно и сохраняем в БД
          // $rrr=$this->_remote_file_exists($f);
          $fileHeaders=$this->_remoteFileData($f);
          // $this->writeLog( "<p>ИНФА</p>".print_r($fileHeaders, true) );

          $getExt=$this->_fileExt($fileHeaders['content-type']);
          if (!empty($getExt)){
              $newFileName=$this->cacheDir.$videoId.$getExt;
              // if ($this->copyFile($f,$newFileName)){
                  // записываем имя созданного файла в БД
                  // $flag=$this->config['imageCachePath'].$videoId.$getExt;
              // }
              $flag=$this->config['imageCachePath'].$videoId.$getExt;
              if (!file_exists($newFileName)
                  || ( file_exists($newFileName) && ( filesize ($newFileName) <> $fileHeaders['content-length'] ) )
              ){
                if(copy($f, $newFileName)){
                  chmod($to, octdec($this->config['new_file_permissions']));
                  $this->writeLog("<p>Скопировали файл thumb ~<a href='{$f}'>{$f}</a>~ <br/>в кеш <i>{$newFileName}</i></p>");
                }else{
                  $flag = false;
                  $this->writeLog("<p>Не удалось скопировать файл thumb ~<a href='{$f}'>{$f}</a>~ <br/>в кеш <i>{$newFileName}</i></p>",'','ERROR');
                }
              }else{
                $this->writeLog("<p>Файл thumb <i>{$newFileName}</i></p> уже существует!",'','WARN');
              }
          }
      // }
      return $flag;
    }


    /**
     * Создает запись в БД и возвращает последний Id
     * @param  array  $param [description]
     * @return [type]        [description]
     */
    protected function createRecord($param=array()) {
        /*$video = $modx->newObject('awesomeVideosItem');
        $video->fromArray(array(
            'active' => (int) $this->config['import']['active'],
            'created' => strtotime($xmlvideo->published),
            'updated' => strtotime($xmlvideo->updated),
            'source' => 'youtube',
            'videoId' =>  str_replace('http://gdata.youtube.com/feeds/api/videos/', '', $xmlvideo->id),
            'name' => $xmlvideo->title,
            'description' => $xmlvideo->content,
            'author' => $xmlvideo->author->name,
            'keywords' => $media->group->keywords,
            'duration' => $yt->duration->attributes()->seconds,
            'jsondata' => array(
                //'flashUrl' => (string)$media->group->content[0]->attributes()->url,
                //'3gppUrl' => (string)$media->group->content[1]->attributes()->url
                'flashUrl' => $flashUrl,
                '3gppUrl' => $gppUrl
            )
        ));*/
    }

    protected function timeToSeconds($time) {
        if (empty($time)) return 0;
        $duration = new DateInterval($time);
        return ((60 * 60 * $duration->h) + (60 * $duration->i) + $duration->s);
    }

    /**
     * Функция сравнения массивов, используется совместно с uasort
     * @param  array  $a
     * @param  array  $b
     * @return [int]
     */
    private static function sortByCreateDate ($a, $b) {
      $a_cmp=(int)$a['created'];
      $b_cmp=(int)$b['created'];

      if ($a_cmp == $b_cmp) {
          return 0;
      }
      return ($a_cmp > $b_cmp) ? (1) : (-1);
    }

    protected function insertVideo() {
        $this->writeLog("Новых материалов на сервере: ".count($this->importList),'','WARN');

        // получим последний документ из таблицы
        // получим у него rank
        // и будем всем остальным прибавлять rank

        $rank=0;
        $c = $this->modx->newQuery('awesomeVideosItem');
        $c -> sortby('rank','DESC');
        $c -> limit(1);
        $lastObj = $this->modx->getObject('awesomeVideosItem', $c);
        if ($lastObj){
          $rank=$lastObj->get('rank');
          $rank++;
        }
        // $this->writeLog("<p>RANK = {$rank}</p>",'','ERROR');

        // отсортируем записи по дате создания
        if ( !uasort($this->importList, array($this, 'sortByCreateDate') ) ){
          $this->writeLog("Произошла ошибка при сортировке данных",'','ERROR');
        }


        foreach ($this->importList as $videoId => $video) {

            $this->writeLog("<p>Итоговые данные!</p>".print_r($video,true),'','WARN');

            // меняем значение если файлик скопировался:\
            $resultImageName = "";
            if ($resultImageName=$this->_createCacheFile($video['image'],$videoId)){
              $this->writeLog("ResultName: ".$resultImageName);
            }

            $importData=array(
                'active' => (int) $this->config['import']['active'],
                'image'  =>  $resultImageName,
                'created'  =>  $video['created'],
                // 'created' => strtotime($video['publishedAt']),  // [publishedAt] => 2013-09-13T11:42:30.000Z
                // 'created' => date('%d %b %Y %H:%M', strtotime($video['publishedAt']),  // [publishedAt] => 2013-09-13T11:42:30.000Z
                // 'updated' => strtotime($video['publishedAt']),  // даты обновления больше нет
                'source' => 'youtube',
                'source_detail' => $video['source_detail'],
                'videoId' =>  $videoId,
                'channelId' => $video['channelId'],
                'rank' =>  $rank,
                'name' => $video['title'],
                'description' => $video['description'],
                'source_detail' => $video['source_detail'],
                // 'author' => $video['channelTitle'],    // как такового тоже нет, надо брать отдельным запросом
                'author' => $video['author'],    // как такового тоже нет, надо брать отдельным запросом
                // 'keywords' => $media->group->keywords,
                'duration' => $this->timeToSeconds($video['contentDetails']['duration']),
            );

            $videoObj = $this->modx->newObject('awesomeVideosItem');
            $videoObj->fromArray($importData);
            if ($videoObj->save() == false) {          // сохраняем
              $this->writeLog("<p>НЕ УДАЛОСЬ сохранить видео с ID = {$videoId}</p>",'','ERROR');
            }else{
              $rank++;
            }



            // $this->modx->log(modX::LOG_LEVEL_WARN,$video["title"]);
        }
    }

    /**
     * Генерирует запрос к серверу за доп инфой по видео, из тех что мы вытащили отдельно из каналов.
     *
     * @param  [type]  $params        [description]
     * @return [type]                 [description]
     */
    protected function getVideosById($params=array()) {
      if (!empty($this->importList)) {
        $this->writeLog("<p>Формирование списка роликов законченно!</p>",'','WARN');
        $this->writeLog("<b>Получим данные по всем новым видеороликам.</br>Всего новых записей для импорта:".count($this->importList)."</b>");

        $params = array_merge(array(
            'baseUrl'=>"https://www.googleapis.com/youtube/v3/",
            'mainPart'=>'videos',
            'part'=>'id,snippet,contentDetails,status,statistics,recordingDetails',
            'key'=>$this->config['import']['apiKey'],
            'order'=>'date', // по-умолчанию сортируется по relevance - релевантность видео по запросу, https://developers.google.com/youtube/v3/docs/search/list
            // 'maxResults'=>$this->config['import']['maxResults'],
            // 'pageToken'=>''
        ),$params);

        // т.к. при запросе videos мы не можем воспользоваться maxResults
        // то реализуем это вручную
        // вытащим все ключи массива и разобъем их по группам (можно в string)
        $groupedVideoList=(array_chunk($this->importList, $this->config['import']['maxResults'], true));
        foreach ($groupedVideoList as $key => $group) {
            $ids=array_keys($group);
            $ids_str=implode(",", $ids);
            // print $ids_str;
            $params['id']=$ids_str;
            $this->createYoutubeQuery($params);
        }
      }
      return $this;
    }

    /**
     * Генерирует запрос к серверу за инфой по видео, из тех что мы вытащили отдельно из каналов.
     *
     * @param  [type]  $params        [description]
     * @return [type]                 [description]
     */
    public function getVideoByIdsFromYouTube($params=array()) {
      $this->writeLog("Получим видео".print_r($params, true),'','ERROR');
      if ( empty($params['id']) && empty($params['ids']) ) return false;

      // $params=array_merge(array1)

      $params = array_merge(array(
          'part'=>'id,snippet,contentDetails,status,statistics,recordingDetails',
          'key'=>$this->config['import']['apiKey'],
          'order'=>'date', // по-умолчанию сортируется по relevance - релевантность видео по запросу, https://developers.google.com/youtube/v3/docs/search/list
          'id'=>array(),
          'ids'=>array()
      ),$params);


      $resIds=implode(',',array_unique( array_merge(
        (is_string($params['ids'])) ? explode(',', $params['ids']) : $params['ids'],
        (is_string($params['id'])) ? explode(',', $params['id']) : $params['id']
      )));

      unset($params['ids']);

      $params['id']=$resIds;

      // print_r ($params);

      // строим запрос
      $query="https://www.googleapis.com/youtube/v3/videos?".urldecode(http_build_query($params));

      $json = file_get_contents($query);
      // $this->modx->log(modX::LOG_LEVEL_INFO,"<pre>".print_r($http_response_header, true)."</pre>");
      if(!strpos($http_response_header[0], "200")){
          // $modx->log(modX::LOG_LEVEL_ERROR, 'Ошибка: '.$json);|
          $this->writeLog('Ошибка запроса'.$query,'','ERROR');
          return false;
      }

      return $this->modx->fromJSON($json);
    }

    /**
     * description
     *
     * @param  [type]  $params        [description]
     * @param  boolean $defSourceType [description]
     * @param  [array] $prevMainAttr [Глобальные данные которые передаются по наследству в каждое видео, быть может уберу потом.]
     * @return [type]                 [description]
     */
    protected function createYoutubeQuery($params=array(), $defSourceType=false, $prevMainAttr=array() ) {
        if (empty($params)) return false;
        $breakInsert=false;

        $params = array_merge(array(
            'baseUrl'=>"https://www.googleapis.com/youtube/v3/",
            'mainPart'=>'search',
            'part'=>'id,snippet',
            'key'=>$this->config['import']['apiKey'],
            'maxResults'=>$this->config['import']['maxResults'],
            // 'pageToken'=>''
        ),$params);

        $mainPart=$params['mainPart'];
        $baseUrl=$params['baseUrl'].$params['mainPart'];
        unset($params['baseUrl'],$params['mainPart']);
        // $baseUrl=$params['baseUrl'];
        // unset($params['baseUrl']);


        // строим запрос
        $query=$baseUrl."?".urldecode(http_build_query($params));

        $this->writeLog('Параметры запроса: <p style="font-size:9px;">'.print_r($params, true).'</p>');
        $this->writeLog('Запрос на youtube: '.$query);


        $json = file_get_contents($query);
        // $this->modx->log(modX::LOG_LEVEL_INFO,"<pre>".print_r($http_response_header, true)."</pre>");
        if(!strpos($http_response_header[0], "200")){
            // $modx->log(modX::LOG_LEVEL_ERROR, 'Ошибка: '.$json);|
            $this->writeLog('Ошибка запроса'.$query,'','ERROR');
            return false;
        }
        $request = $this->modx->fromJSON($json);
        $this->writeLog("<b>Тип полученных данных: {$request[kind]} </b>");
        $this->writeLog('Всего данных в ответе: '.$request[pageInfo][totalResults] );
        $this->writeLog('Подробные данные ответа: <p style="font-size:9px;">'.print_r($request, true).'</p>' );

        if (!count($request["items"])) return true;
        $count=0;

        // echo ($query."\n\r");
        // echo ($mainPart."\n\r");
        // echo ($value['kind']."\n\r");

        foreach ($request["items"] as $key => $value) {

		        // $this->writeLog('item: '.RAND());
            // switch ($value['kind']) {
            $dependenceId=$value['snippet']['channelId'];
            switch ($mainPart) {
                // case 'youtube#video':
                case 'videos':
                    // получает инфу по конкретному видео ролику.
                    // echo (77777);
                    $curVideoId=$value['id'];
                    $curSourceType="v";
                    break;
                // case 'youtube#playlistItem':
                case 'playlists':
                    if ($params['channelId']){
                      // если получили список плейлистов для канала
                      // $this->createYoutubeQuery($newParams,$defSourceType,$prevMainAttr);
                    }
                    break;
                case 'playlistItems':
                    // echo ("77777777\n\r");
                    $curVideoId=$value['snippet']['resourceId']['videoId'];
                    $curSourceType="p";
                    $dependenceId=$value['snippet']['playlistId'];
                    break;
                // case 'youtube#searchResult':
                case 'search':
                    $curVideoId=$value['id']['videoId'];
                    $curSourceType="c";
                    break;
                // case 'youtube#channel':
                case 'channels':
                    // срабатывает когда указали пользователя, т.е. мы получили массив со списком каналов
                    // пользователя, и теперь нужно пройтись по всем каналам
                    // $curVideoId=$value['id']['videoId'];
                    // $newParams=$params;
                    $newParams=array_merge(array(
                        'mainPart'=>'search',
                        'type'=>'video',
                        'channelId'=>$value['id']
                        // 'channelDetail'=>$value
                    ),$params);
                    $this->channels[$value['id']] = $value['snippet'];
                    // $prevMainAttr['channelDetail']=array_merge(array('id'=>$value['id']), $value['snippet']);
                    // $this->writeLog("CHD:".print_r($prevMainAttr, true));

                    if ($defSourceType && array_key_exists('u',$defSourceType)){
                        $prevMainAttr['author']=$newParams['forUsername'];
                        $this->writeLog("Получили канал ~{$value[id]}~ пользователя: ~{$newParams['forUsername']}~");
                        $curSourceType="u";
                        $breakInsert=true;
                        unset($newParams['forUsername']);
                    }else{
                        $this->writeLog("Получили канал2 ~{$value[id]}~ ???: ~{$newParams['forUsername']}~");
                        $curSourceType="c";
                    }
                    $this->writeLog('<b>Сформировали запроса на получение списка видео канала</b>');
                    $this->createYoutubeQuery($newParams,$defSourceType,$prevMainAttr);
                    continue 2;
                    break;
                default:
                    $curVideoId=$value['id']['videoId'];
                    $curSourceType="c";
                    break;
            }

            // if ($breakInsert) return;


            // $this->writeLog('<b>Тип источника</b>: '.$curSourceType);
            // if ($curSourceType!=="v"){
                // if (in_array($curVideoId,$this->vidList) || array_key_exists($curVideoId,$this->importList)) continue;
                if (in_array($curVideoId,$this->vidList)) continue;

                $this->writeLog("Получаем картинку для видео ~{$curVideoId}~ <i>{$value[snippet][title]}</i>");
                $thumbs=array_values($value['snippet']['thumbnails']);
                $this->writeLog('Имеющиеся thumbnails: <p style="font-size:9px;">'.print_r($thumbs, true)."</p>" );

                $bestThumb=(array_key_exists('maxres',$thumbs)) ? $thumbs['maxres']['url'] : current(end($thumbs));

                // if ($this->_remote_file_exists('https://i.ytimg.com/vi/'.$curVideoId.'/maxresdefault.jpg')){
                    // $bestThumb='https://i.ytimg.com/vi/'.$curVideoId.'/maxresdefault.jpg';
                // }

                $this->writeLog('Выбрал thumbnail: '.$bestThumb );
                $this->writeLog( "<a target='_blank' href='{$bestThumb}' title='{$value[snippet][title]}'><img src=\"{$bestThumb}\" width='200' /></a>" );
                // $this->writeLog('<span qtip="<img src=\''.$bestThumb.'\' />" >0002.JPG</span>');
                // можно сделать увеличение картинки по наводке через quickTips
                // http://try.sencha.com/extjs/4.0.7/examples/qtips/qtips/viewer.html
                // https://github.com/jpdevries/modx-manager-theme/blob/master/root/browser/index.tpl
                // как в дереве файлов, для минимума кода лучше разобрать modx-browser-rte

                // $this->writeLog('Выбрал thumbnails: '.print_r($bestThumb, true),'','WARN');
                // $this->modx->log(modX::LOG_LEVEL_WARN,"Выбрал thumbnails: ".print_r($bestThumb, true) );

                $value['snippet']['image']=(!empty($bestThumb)) ? $bestThumb : false;

                $tempSourceType=array($curSourceType => $dependenceId);
                // $tempSourceType=array($curSourceType => $value['snippet']['channelId']);
                if ($defSourceType){
                    $tempSourceType=array_merge($tempSourceType,$defSourceType);
                }
                $value['snippet']['source_detail']=$this->modx->toJson($tempSourceType);


                if ($curSourceType=="v"){
                  $this->writeLog('Добавляем подробные данные о ролике');
                  if (empty($this->importList[ $curVideoId ]['author'])){
                    $this->importList[ $curVideoId ]['author']=$value['snippet']['channelTitle'];
                  }
                  $this->importList[ $curVideoId ] = array_merge($this->importList[ $curVideoId ],array(
                      'created'=>strtotime($value['snippet']['publishedAt']),
                      'contentDetails'=>$value['contentDetails'],
                      'statistics'=>$value['statistics'],
                      'recordingDetails'=>$value['recordingDetails']
                  ));
                }else{
                  $this->importList[ $curVideoId ]=array_merge($prevMainAttr,$value['snippet']);
                }

                $count++;
            // }else{
                // это уже второй проход для получения детальной информации о видео, поэтому мы скомбинируем
                // имеющиеся данные с вновь полученными, в детальной инфе ссылка на картинку
                // print_r($this->importList[ $curVideoId ]);
            // }


        }

        // в этом месте нужно сделать рукурсию!!!
        if (isset($request['nextPageToken'])) {
		        $this->writeLog('След страница: '.$request['nextPageToken']);
            $params['mainPart']=$mainPart;
            $params['pageToken']=$request['nextPageToken'];
            $this->createYoutubeQuery($params,$defSourceType,$prevMainAttr);
        }
        // $this->modx->log(modX::LOG_LEVEL_INFO,"Новых материалов на сервере: ".$count);

        return true;
    }

    protected function create_video_list() {
        $c = $this->modx->newQuery('awesomeVideosItem');
        $c->setClassAlias('awesomeVideosItem');
        $c->select(array(
            // $this->modx->getSelectColumns('awesomeVideosItem', $c->getAlias(), '')
            $this->modx->getSelectColumns('awesomeVideosItem', $c->getAlias(), '', array('id', 'videoId'))
            // 'id', 'videoId'
            // ,'topic_val' => $modx->getSelectColumns('modResource', 'topic', '', array('pagetitle'))
        ));
        $c->where(array('source' => 'youtube'));
        // $c->where(array('deleted' => '1'));

        // $c->prepare();
        // $this->modx->log(modX::LOG_LEVEL_INFO,$c->toSQL());

        $count = $this->modx->getCount('awesomeVideosItem',$c);

        $this->writeLog("На текущий момент в базе: {$count} записей",'','WARN');

        if ($count){
            // $videos = $this->modx->getCollection('awesomeVideosItem', $c);
            $videos = $this->modx->getIterator('awesomeVideosItem', $c);  // быстрее чем getcollection, жрет меньше памяти и нельзя в нем использовать другие связи типа getmany
            foreach ($videos as $video) {
                // $video = $video->toArray();
                $this->vidList[$video->id]=$video->videoId;
                // $this->modx->log(modX::LOG_LEVEL_INFO,"запись:".print_r($video,true));
            }
            $this->vidList = array_unique($this->vidList);
        }
        return true;
    }
    /**
     * Отвечает за импрт
     * @param  string $type       [description]
     * @param  array  $properties [description]
     * @return [type]             [description]
     */
    protected function parse_sources($properties = array()) {
        // $sources = $this->modx->getOption('awesomeVideos.video.source_detail',null,false);
        $this->writeLog('Запускаю парсер источника: '.$sources);
        // $sources = '[{"c":"UCsjEcIIR9nVFI1RYE9rawfg"}]';  // makarmagoon
        // $sources = '[{"u":"sportrt"}]';
        // $sources = '[{"u":"MrSetest"}]';
        $sources = '[{"c":"UCtsDl3hsddpyDzHSdrU2OOg"}]';  // канал с несколькими видео
        // $sources = '[{"c":"UCsjEcIIR9nVFI1RYE9rawfg"},{"u":"MrSetest"},{"p":"PLK2K6UAy2uj8iTbSMEAmG2dcoW7vwhkcD"}]';
        // $sources = '[{"c":"UCtsDl3hsddpyDzHSdrU2OOg"},{"c":"UCtsDl3hsddpyDzHSdrU2OOg2222fd"},{"u":"MrSetest"},{"p":"PLK2K6UAy2uj8iTbSMEAmG2dcoW7vwhkcD"}]';
        // $sources = '[{"c":"UCfQfRkl4w4vwtb9C2ndx1_Q"},{"u":"MrSetest"},{"p":"PLK2K6UAy2uj8iTbSMEAmG2dcoW7vwhkcD"},{"c":"UCtsDl3hsddpyDzHSdrU2OOg"}]';
        $sources=json_decode($sources,true);
        if (!$sources) return false;

        // создадим массив всех текущих записей
        $this->create_video_list();

        foreach ($sources as $key => $value) {
            $defSourceType=false;
            if (!current($value)) return false;
            // $this->modx->log(modX::LOG_LEVEL_INFO,key($value));
            // $this->modx->log(modX::LOG_LEVEL_INFO,current($value));
            switch (key($value)) {
                case 'c':
                    // $channelUrl = "https://www.googleapis.com/youtube/v3/playlists?part=id,snippet&channelId=$channel&key=$apiKey";
                    if ($this->importType == 'Playlists'){
                  		$this->writeLog('Получим плейлисты с канала: '.current($value));
                      $params=array(
                          'mainPart'=>'playlists',
                          'channelId'=>current($value)
                      );
                    }else{
                      $this->writeLog('Импорт с канала: '.current($value));
                      $params=array(
                          'mainPart'=>'search',
                          'type'=>'video',
                          'channelId'=>current($value)
                      );
                    }

                    break;
                case 'u':
                    $this->writeLog("Импорт по имени пользователя ~".current($value)."~, запрашиваем список каналов");
                    // получает список каналов пользователя, только после этого проходимся по всем каналам и получаем списки видео
                    // $channelUrl = "https://www.googleapis.com/youtube/v3/channels?part=snippet&forUsername=$user&key=$apiKey";
                    $params=array(
                        'mainPart'=>'channels',
                        'forUsername'=>current($value)
                    );
                    $defSourceType=array('u'=>current($value));
                    break;
                case 'p':

                    if ($this->importType == 'Playlists'){
                      $this->writeLog('Получим данные плейлиста: '.current($value));
                      $params=array(
                          'mainPart'=>'playlists',
                          'id'=>current($value)
                      );
                    }else{
                      $this->writeLog('Импорт из плейлиста: '.current($value));
                      // $channelUrl = "https://www.googleapis.com/youtube/v3/playlistItems?part=id,snippet&playlistId=LLfQfRkl4w4vwtb9C2ndx1_Q&maxResults=$limit&key=$apiKey";
                      $params=array(
                          'mainPart'=>'playlistItems',
                          'playlistId'=>current($value)
                      );
                    }
                    break;
            }
            $this->createYoutubeQuery($params,$defSourceType);
        }
        // $this->writeLog('Закончили процесс');
        // if (!empty($this->importList)) {
        $this->getVideosById()
             ->makeDir()
             ->insertVideo()
        ;
        // }

        // $this->modx->log(modX::LOG_LEVEL_INFO,"<pre>".print_r($sources, true)."</pre>");
        // $this->modx->log(modX::LOG_LEVEL_INFO,"<pre>".print_r($this->importList, true)."</pre>");


         return true;
    }
}

// return 'awesomeVideos';