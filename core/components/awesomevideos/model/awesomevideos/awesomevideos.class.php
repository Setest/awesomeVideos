<?php
// set_time_limit(10);
// ini_set("max_execution_time", "600"); // включаем 10 минут на ограничение работы скрипта
// ini_set("max_input_time", "600"); // включаем 10 минут на ограничение работы скрипта

// error_reporting(E_ALL ^ E_NOTICE); ini_set('display_errors', true);

/**
 * The base class for awesomeVideos.
 */

require_once 'awesomevideoshelper.class.php';

// class awesomeVideosItemsGetTopicOnlyProcessor extends awesomeVideosItemsGetTopicProcessor {

// }

class awesomeVideos extends awesomeVideosHelper {
	/* @var modX $modx */
	public $modx;
  protected $itemsList = array();
  protected $importList = array();  // массив документов для импорта
  public $cacheDir = ''; // итоговый путь папки кеша
  public $importType = 'Item'; // тип данных для импорта, есть Item и Playlist

  public $classKey = 'awesomeVideosItem';
  protected $synchronize = false; // при true - идет процесс синхронизации

  /** @var array все настройки класса */
  // protected $config = array();
  // protected $_settings = array();

  /**
   * @param modX &$modx A reference to the modX object
   * @param array $config An array of configuration options
   */
	function __construct(modX &$modx, array $config = array()) {
    // print_r($config);
		$this->modx =& $modx;

    $corePath = $this->modx->getOption('awesomevideos_core_path', $config, $this->modx->getOption('core_path') . 'components/awesomevideos/');
		$basePath = $this->modx->getOption('awesomevideos_base_path', $config, $this->modx->getOption('base_path'));
		// $corePath =dirname(__FILE__);
		$assetsUrl = $this->modx->getOption('awesomevideos_assets_url', $config, $this->modx->getOption('assets_url') . 'components/awesomevideos/');
		$connectorUrl = $assetsUrl . 'connector.php';

		$this->config = array_merge(array(
			'assetsUrl' => $assetsUrl,
			'cssUrl' => $assetsUrl . 'css/',
      'jsUrl' => $assetsUrl . 'js/',
			// 'jsUrl' => ltrim($assetsUrl . 'js/', '/'),
			'imagesUrl' => $assetsUrl . 'images/',
			'connectorUrl' => $connectorUrl,

      'actionUrl' => $assetsUrl . 'action.php', // тот что вызывается при ajax
      'corePath' => $corePath,
      'basePath' => $basePath,
      'sitePath' => MODX_BASE_PATH, // используется в админке
      'processorsPath' => $corePath . 'processors/',
      'modelPath' => $corePath . 'model/',
      'chunksPath' => $corePath . 'elements/chunks/',
      'templatesPath' => $corePath . 'elements/templates/',
      'chunkSuffix' => '.chunk.tpl',
      'snippetsPath' => $corePath . 'elements/snippets/',

      'cacheTime' => 1800,  // кеш работает и в менеджере и у клиента

      'restHost' => 'http://gdata.youtube.com/',
      'restPath' => 'feeds/api/users/username/uploads',
      'restMethod' => 'get',
      //Права на только что созданный файл
      'new_file_permissions' => (int)$this->modx->getOption('awesomeVideos.video.newFilePermissions',null,'0664'),

      'direct' => false,  // может быть true только при прямом вызове например из ajax

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
        'log_status'=>$this->modx->getOption('awesomeVideos.main.log', null, true),

        // 'log_status'=>$this->modx->getOption('log_status', $config, true),
        // 'isstyled'=>$this->modx->getOption('log_isstyled', $config, true),

      	'log_placeholder'=>$this->modx->getOption('log_placeholder', null, false), //getImgLog
      	'log_detail'=>$this->modx->getOption('log_detail', null, false),
				// 'log_target'=>( 1==1 )
					// ? $this->modx->getLogTarget()
					// ? $this->modx->getLogTarget()->subscriptions[0]	// это на случай вывода в консоль
					// : false,
				// 'log_target' => $this->modx->getOption('awesomeVideos.main.log_target', null, 'ECHO'),	// FILE, HTML, ECHO

				'log_target' => ($config['log_target'])?$config['log_target']:'ECHO',	// FILE, HTML, ECHO

        // 'log_target' => 'FILE',

        'log_level'=>$this->_getModxConst($this->modx->getOption('log_level', $config, 'LOG_LEVEL_INFO')),  // INFO, WARN, ERROR, FATAL, DEBUG
        // 'log_level'=>$this->_getModxConst(3), // INFO, WARN, ERROR, FATAL, DEBUG
      	// 'log_level'=>123,	// INFO, WARN, ERROR, FATAL, DEBUG
      ),

		), $config);



    $this->logConfig($config['log']);

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
            'imageSourceFullBasePath'=>$this->config['basePath'].$msproperties['basePath']['value'],
            'imageSourceBasePath'=>$msproperties['basePath']['value'],
            'imageSourceBaseUrl'=>$msproperties['baseUrl']['value']
        ));
    }
    $this->config['imageNoPhoto']=(!empty($this->config['imageNoPhoto']))?$this->config['imageNoPhoto']:$assetsUrl.'img/noimage.jpg';

    $this->cacheDir=$this->config['imageSourceFullBasePath'].$this->config['imageCachePath'];

    // $this->writeLog("LOG LEVEL:".$this->modx->getOption('log_level', $config, 'LOG_LEVEL_WARN'));
    // $this->writeLog("LOG LEVEL2:".$this->_getModxConst($this->modx->getOption('log_level', $config, 'LOG_LEVEL_WARN')));
    // $this->writeLog("LOG LEVEL3:".$this->_getModxConst('LOG_LEVEL_WARN'));


    if (!isset($this->config['cacheKey'])){
      $this->config['cacheKey']=$this->getCacheKey();
      $this->modx->cacheManager->set('awesomevideos/prep_' . $this->config['cacheKey'], $this->config, $this->config['cacheTime']);
    }

    $this->modx->addPackage('awesomevideos', $this->config['modelPath']);
    $this->modx->lexicon->load('awesomevideos:default');
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
            // проверяем было ли загружено ранее, т.к. делать это можно только один раз
            // и не из ajax запроса.

            if ( !$this->config['direct'] && ( !defined('MODX_API_MODE') || !MODX_API_MODE ) ) {
              // $initializing = !empty($this->modx->loadedjscripts[$this->config['jsUrl'] . 'web/default.js']);

              // $this->config = array_merge($this->config, $scriptProperties);
              $config = $this->makePlaceholders($this->config);

              $jsPath = $this->config['basePath'].$this->config['jsUrl'] . 'web/default.js';



              if ($content = @file_get_contents($jsPath)){
                $this->writeLog('Loaded JS: '. $jsPath);
                // $this->writeLog($this->config);
                $content = str_replace($config['pl'], $config['vl'], trim($content));
                $content = $this->fastProcess($content, true);
                $this->modx->regClientStartupScript(preg_replace(array('/^\n/', '/\t{7}/'), '', "<script type='text/javascript'>{$content}</script>"), true);
                // $this->modx->regClientStartupScript($this->config['jsUrl'].'web/lib/url.min.js');
                $this->modx->regClientStartupScript($this->config['jsUrl'].'web/lib/URI.min.js');
                // if ( $this->config['pagination'] == 'carousel' ){
                  $this->modx->regClientCSS($this->config['cssUrl'].'web/owl.carousel.css');
                  $this->modx->regClientCSS($this->config['cssUrl'].'web/default.css');
                  $this->modx->regClientStartupScript($this->config['jsUrl'].'web/lib/owl.carousel.min.js');
                  $this->modx->regClientStartupScript($this->config['jsUrl'].'web/lib/jquery.waitforimages.min.js');
                // }
                // https://github.com/websanova/js-url
                // https://github.com/allmarkedup/purl - хорошая вещь, нудо будет заюзать
                // http://medialize.github.io/URI.js/ - хорошая вещь, нужно будет заюзать
              }else{
                $this->writeLog('Cant load JS: '. $this->config['jsUrl'] . 'web/default.js', '', 'ERROR');
              }

              if ($js = trim($this->modx->getOption('awesomeVideos.frontend.js'))) {
                  // if (!empty($js) && preg_match('/\.js/i', $js)) {
                      $this->modx->regClientStartupScript(preg_replace(array('/^\n/', '/\t{7}/'), '', '
					            <script type="text/javascript">
					                if(typeof jQuery == "undefined") {
                              document.write("<script src=\"'.$this->config['jsUrl'].'web/lib/jquery.min.js\" type=\"text/javascript\"><\/script>");
					                    document.write("<script src=\"'.$this->config['jsUrl'].'web/lib/jquery-migrate.min.js\" type=\"text/javascript\"><\/script>");
					                }
					            </script>
					            '), true);
					            // $this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));
					        // }
					    }

              if ($css = trim($this->modx->getOption('awesomeVideos.frontend.css'))) {
                $this->modx->regClientCSS(str_replace($config['pl'], $config['vl'], $css));
              }

						}
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


    /**
     * Импорт видеороликов
     * @param  array  $properties [description]
     * @return [type]             [description]
     */
    public function import($properties = array()) {
      $this->classKey='awesomeVideos'.$this->importType;
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
     * Синхронизация плейлистов и видеороликов
     * @param  array  $pls массив ID плейлистов
     * @param  array  $params доп параметры для отправки запроса на youtube
     * @return [type]             [description]
     */
    public function synchronize($pls = array(), $params = array()) {
      $flag=true;
      $this->classKey='awesomeVideos'.$this->importType;
      $this->synchronize=true;
    	$this->writeLog("Запускаю парсер плейлистов...");
      $this->writeLog("Текущий classKey: {$this->classKey}");

      // создадим массив всех текущих видеороликов у которых не указаны плейлисты
      $this->create_video_list(array('playlist'=>0));

      // $this->writeLog("Есть видео: ".print_r($this->itemsList, true),'','WARN');

      $params = array_merge(array(
          'mainPart'=>'playlistItems',
          'part'=>'id,snippet'
      ),$params);

      $count=0;
      foreach ($pls as $key => $value) {
        $this->writeLog("Обрабатывается плейлист: ".$value['playlistId'],'','WARN');

        $params['playlistId']=$value['playlistId'];
        if ($this->createYoutubeQuery($params)){ // заполнили $this->importList [ $value['playlistId'] ]
          $this->writeLog("Текущий лист содержит: ".print_r($this->importList[ $value['playlistId'] ], true),'','WARN');
          // сформируем запрос на получени нужных id и произведем массовую замену.
          $c = $this->modx->newQuery($this->classKey);
          $c->command('update');
          $c->where(array(
             'videoId:IN' => $this->importList [ $value['playlistId'] ]
            ,'active' => 1
            // ,'playlist' => 0
          ));
          $c->set(array('playlist' => $value['id']));

          // $c->select('id,videoId,name,playlist');
          // $c->prepare(); $this->writeLog("XXX: ".$c->toSQL(),'','WARN');
          if ($c->prepare() && $itog=$c->stmt->execute() ) {
            $count=+$c->stmt->rowCount();
            $this->writeLog("Плейлист {$value['playlistId']} cинхронизирован успешно",'','WARN');
          }else{
            $flag=false;
            $this->writeLog("Произошла ошибка при синхронизации плейлиста: {$value['playlistId']}",'','ERROR');
          }

        }
      }

      $this->writeLog("Всего синхронизированно <b>{$count}</b> видеозаписей.",'','WARN');

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


    public function _createCacheFile($f,$videoId) {
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
                  chmod($newFileName, octdec($this->config['new_file_permissions']));
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
        /*$video = $modx->newObject($this->classKey);
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

    public function timeToSeconds($time) {
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
        $c = $this->modx->newQuery($this->classKey);
        $c -> sortby('rank','DESC');
        $c -> limit(1);
        $lastObj = $this->modx->getObject($this->classKey, $c);
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

            if ($this->importType == 'Playlist'){
              // для плейлиста импорт немного другой
              $importData=array(
                  'rank' =>  $rank,
                  'active' => (int) $this->config['import']['active'],
                  'image'  =>  $resultImageName,
                  'channel' => $video['channel'],
                  'channelId' => $video['channelId'],
                  'user' => ($video['author'])?$video['author']:$video['channelTitle'],    // как такового тоже нет, надо брать отдельным запросом
                  // 'user' => $video['user'],    // как такового тоже нет, надо брать отдельным запросом
                  'created'  =>  $video['created'],
                  'playlist' => $video['title'],
                  'playlistId' =>  $videoId,
                  'description' => $video['description'],
                  // 'author' => $video['author'],    // как такового тоже нет, надо брать отдельным запросом
                  // 'keywords' => $media->group->keywords,
                  // 'duration' => $this->timeToSeconds($video['contentDetails']['duration']),
              );

            }else{
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
            }

            $videoObj = $this->modx->newObject($this->classKey);
            $videoObj->fromArray($importData);

            // continue; // прерываем для тестирования
            if ($videoObj->save() == false) {          // сохраняем
              $this->writeLog("<p>НЕ УДАЛОСЬ сохранить документ с ID = {$videoId}</p>",'','ERROR');
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
      if ($this->importType == 'Playlist') return $this;
      if (!empty($this->importList)) {
        $this->writeLog("<p>Формирование списка законченно!</p>",'','WARN');
        $this->writeLog("<b>Получим подробные данные по всем новым записям.</br>Всего новых записей для импорта:".count($this->importList)."</b>");

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
    public function getInfoByIdsFromYouTube($params=array(),$type='videos') {
      $this->writeLog("Получим видео".print_r($params, true),'','ERROR');
      if ( empty($params['id']) && empty($params['ids']) ) return false;

      // $params=array_merge(array1)

      switch ($type) {
        case 'videos':
          $part='id,snippet,contentDetails,status,statistics,recordingDetails';
          break;
        case 'playlists':
          $part='id,snippet';
          break;
        default:
          $part='id,snippet';
          break;
      }

      $params = array_merge(array(
          'part'=>$part,
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
      $query="https://www.googleapis.com/youtube/v3/{$type}?".urldecode(http_build_query($params));

      // echo $query;

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

        $this->writeLog('Параметры ДО: <p style="font-size:9px;">'.print_r($params, true).'</p>');
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

        // $this->writeLog('TEST: '.date('h:i:s'));
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
                    // if ($params['channelId']){
                      $curVideoId=$value['id'];
                      $curSourceType="p";
                      // если получили список плейлистов для канала
                      // $this->createYoutubeQuery($newParams,$defSourceType,$prevMainAttr);
                    // }
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

                    if ($this->importType == 'Playlist'){
                      $prevMainAttr['channel']=$value['snippet']['title'];
                      $newParams['mainPart']='playlists';
                      // $curVideoId=$value['id'];
                      // $curSourceType="p";
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
                // if (in_array($curVideoId,$this->itemsList) || array_key_exists($curVideoId,$this->importList)) continue;

                if ($this->synchronize){
                  // идет процесс синхронизации, нам не нужны только ID видеороликов
                  $this->importList[ $dependenceId ][] = $curVideoId;
                  continue;
                }
                elseif (in_array($curVideoId,$this->itemsList)) continue;


                $this->writeLog("Получаем картинку для документа ~{$curVideoId}~ <i>{$value[snippet][title]}</i>");
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
                  if ($mainPart=="playlists") {
                    $value['snippet']['channel'] = $value['snippet']['channelTitle'];
                    $value['snippet']['channelTitle'] = ''; // тут надо получать данных из канала - о том какое имя пользователя
                  }

                  $this->importList[ $curVideoId ]=array_merge($prevMainAttr,$value['snippet'],array(
                      'created'=>strtotime($value['snippet']['publishedAt'])
                  ));
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

    protected function create_video_list($criteria = array()) {

        $fieldName = ($this->importType == 'Playlist') ? 'playlistId' : 'videoId';

        $c = $this->modx->newQuery($this->classKey,$criteria);
        $c->setClassAlias($this->classKey);
        $c->select(array(
            // $this->modx->getSelectColumns($this->classKey, $c->getAlias(), '')
            $this->modx->getSelectColumns($this->classKey, $c->getAlias(), '', array('id', $fieldName))
            // 'id', $fieldName
            // ,'topic_val' => $modx->getSelectColumns('modResource', 'topic', '', array('pagetitle'))
        ));
        // $c->where(array('source' => 'youtube'));
        // $c->where(array('deleted' => '1'));

        // $c->prepare();
        // $this->modx->log(modX::LOG_LEVEL_INFO,$c->toSQL());

        $count = $this->modx->getCount($this->classKey,$c);

        $this->writeLog("На текущий момент в базе: {$count} записей",'','WARN');

        if ($count){
            // $videos = $this->modx->getCollection($this->classKey, $c);
            $videos = $this->modx->getIterator($this->classKey, $c);  // быстрее чем getcollection, жрет меньше памяти и нельзя в нем использовать другие связи типа getmany
            foreach ($videos as $video) {
                // $video = $video->toArray();
                $this->itemsList[$video->id]=$video->$fieldName;
                // $this->modx->log(modX::LOG_LEVEL_INFO,"запись:".print_r($video,true));
            }
            $this->itemsList = array_unique($this->itemsList);
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
        $sources = $this->modx->getOption('awesomeVideos.video.source_detail',null,false);
        $this->writeLog('Запускаю парсер источника: '.$sources);

        // $sources = '[{"c":"UCAFY-PK5UIckMd065VEdsYg"},{"p":"PLIclYYqEg9iOKDeaEcfs7UomiN3FHJEHI"},{"c":"UCADP9yUrK4kVoeIHbZlty2Q"},{"u":"paxpaxru"}]';  // много видео больше 200-ста
        // $sources = '[{"p":"PLIclYYqEg9iOKDeaEcfs7UomiN3FHJEHI"},{"c":"UCADP9yUrK4kVoeIHbZlty2Q"},{"u":"paxpaxru"}]';  // много видео больше 200-ста

        // $sources = '[{"c":"UCsjEcIIR9nVFI1RYE9rawfg"}]';  // makarmagoon, тут нет плейлистов
        // $sources = '[{"u":"sportrt"}]';
        // $sources = '[{"p":"PLRmjK6gsZMUv0QRpMF71EYp64iIUymghM"}]';  // большой плейлист
        // $sources = '[{"p":"PLK2K6UAy2uj8iTbSMEAmG2dcoW7vwhkcD"}]';  // sport  - спортивная афиша
        // $sources = '[{"u":"MrSetest"}]';
        // $sources = '[{"c":"UCtsDl3hsddpyDzHSdrU2OOg"}]';  // канал backbone с несколькими видео и плейлистами
        // $sources = '[{"u":"sportrt"},{"c":"UCtsDl3hsddpyDzHSdrU2OOg"},{"p":"PLRmjK6gsZMUv0QRpMF71EYp64iIUymghM"}]';
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
                    if ($this->importType == 'Playlist'){
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

                    if ($this->importType == 'Playlist'){
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