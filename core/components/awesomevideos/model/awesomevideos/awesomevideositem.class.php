<?php

/**
 * @package awesomevideos
 */
class awesomeVideosItem extends xPDOSimpleObject {
  /**
   * The array of properties being passed to this processor
   * @var array $properties
   */
  public $properties = array();


	public function save($cacheFlag= null) {
	  if ($this->isNew() && !$this->get('createdon')) {
	      // $this->set('createdon', strftime('%Y-%m-%d %H:%M:%S'));
	      // $this->set('editedon', strftime('%Y-%m-%d %H:%M:%S'));
	      $this->set('createdon', strtotime("now"));
	      $this->set('editedon', strtotime("now"));
	  }

	  if ($this->isNew() && !$this->get('createdby')) {
	      if (!empty($this->xpdo->user) && $this->xpdo->user instanceof modUser) {
	          if ($this->xpdo->user->isAuthenticated($this->xpdo->context->key)) {
	              $this->set('createdby',$this->xpdo->user->get('id'));
						    $this->set('editedby',$this->xpdo->user->get('id'));
	          }
	      }
	  }
	  $saved= parent :: save($cacheFlag);
	  if ($saved) {
	      // if ($this->xpdo->getCacheManager()) {
	      //     $this->xpdo->cacheManager->delete('gallery/item/list/');
	      // }
	  }
	  return $saved;
	}


  public function remove (array $ancestors = array()) {
  	$cacheDir = $this->xpdo->awesomevideos->cacheDir;
    $path = $cacheDir.$this->get('videoId').'.jpg';
    // $this->xpdo->error->addError($path);	// вот так можно зафиксировать ошибку
    if ($cacheDir && file_exists($path)) {
    		// удаляем видео только 100% попавшие в папку cache при импорте,
    		// т.к. остальные могут использоваться и в других местах.
    		// недостаток: вероятно будет сохраняться мусор в папке кеша, если туда будут пихать свои файлы.
        @chmod($path,0777);
        fclose(fopen($path,'a'));
        unlink($path);
    }
    return parent::remove($ancestors);
  }

  public function duration()
 	{
      $seconds_count = $this->get('duration');
      if (!isset($seconds_count)) return array();

      $seconds = $seconds_count % 60;
      $minutes = floor($seconds_count/60);
      $hours   = floor($seconds_count/3600);

      $seconds = str_pad($seconds, 2, "0", STR_PAD_LEFT);
      $minutes = str_pad($minutes, 2, "0", STR_PAD_LEFT);
      $hours   = str_pad($hours,   2, "0", STR_PAD_LEFT);

      return array(
          'hh' => $hours,
          'mm' => $minutes,
          'ss' => $seconds,
          'seconds' => $seconds_count
      );
 	}

  public function getTVs($tvdata=null)
  {
      $result=array();

      $tvlist = (!empty($tvdata))?$tvdata:$this->get('tvdata');
      // $tvlist = $this->get('tvdata');
      if (empty($tvlist)) return false;

      $tvIdList=array_keys($tvlist);
      if (empty($tvIdList)) return false;

      $c = $this->xpdo->newQuery('modTemplateVar');
      $c->where(array(
        // 'id:IN' => $tvIdList,
        'name:IN' => $tvIdList,
      ));

      $total_tvs = $this->xpdo->getCount('modTemplateVar',$c);
      // print "всего ".$total_tvs;
      if (empty($total_tvs)) return false;
      // print_r ($tvIdList);

      $tvs = $this->xpdo->getCollection('modTemplateVar', $c);
      foreach ($tvs as $tv) {
          // print "CurTV: ".$tv->get('id');
          // $tvKey = 'tv'.$tv->get('id');
          $tvId = $tv->get('id');
          $tvKey = $tv->get('name');
          // $value= $tvlist[$tvId];
          $value= $tvlist[$tvKey];
          $tv->set('value',$value);

          // $params = $tv->get('input_properties');
          $params = $tv->get('output_properties');

          /* process any TV commands in value */
          $value = $tv->processBindings($value, 0);

          /* run prepareOutput to allow for custom overriding */
          $value = $tv->prepareOutput($value);

          $outputRenderPaths = $tv->getRenderDirectories('OnTVOutputRenderList','output');
          $output = $tv->getRender($params,$value,$outputRenderPaths,'output',0,$tv->get('display'));
          // $output2 = $tv->renderOutput();  // этот метод должен заменять все что выше, но он не работает
          // поэтому делаем вручную

          $result[$tvKey]=array(
              'id'=>$tvId,
              'value'=>$value,
              'type'=>$tv->get('type'),
              'params'=>$params,
              'result'=>(!empty($output))?$output:$value,
              'display'=>$tv->get('display'),
              // 'result'=>$output,
              // '$outputRenderPaths'=>$outputRenderPaths,
          );

          // print_r($result);
      }
      return $result;
  }

  /**
   * Вспомогательные классы
   */
  public function getProperty($k,$default = null) {
      return array_key_exists($k,$this->properties) ? $this->properties[$k] : $default;
  }

  /**
   * Set the runtime properties for the processor
   * @param array $properties The properties, in array and key-value form, to run on this processor
   * @return void
   */
  public function setProperties($properties) {
      unset($properties['HTTP_MODAUTH']);
      $this->properties = array_merge($this->properties,$properties);
  }

  /**
   * Completely unset a property from the properties array
   * @param string $key
   * @return void
   */
  public function unsetProperty($key) {
      unset($this->properties[$key]);
  }

  /**
   * Set a property value
   *
   * @param string $k
   * @param mixed $v
   * @return void
   */
  public function setProperty($k,$v) {
      $this->properties[$k] = $v;
  }

  /**
   * метод возвращает массив TV параметров в том виде в котором он храниться в БД
   * т.к. различные типы данных имеют свое хранение, то будем соблюдать стандарт, к сожалению
   * мы не можем (или я не смог) использовать оригинальный метод saveTemplateVariables
   * ксласс modObjectUpdateProcessor процессора resource/update, поэтому будем использовать
   * его начинку.
   */

  public function parseTVS($tvlist = array()) {
      // получим список TV объектов на основе имеющихся
      $tvIdList=array_keys($tvlist);
      if (empty($tvIdList)) return false;

      $c = $this->xpdo->newQuery('modTemplateVar');
      $c->where(array(
        // 'name:IN' => $tvIdList,
        'id:IN' => $tvIdList,
      ));

      $total_tvs = $this->xpdo->getCount('modTemplateVar',$c);
      // print "всего ".$total_tvs;
      if (empty($total_tvs)) return false;

      $tvs = $this->xpdo->getCollection('modTemplateVar', $c);
      foreach ($tvs as $tv) {
          // print $tv->get('id');
          $tvKey = 'tv'.$tv->get('id');
          $value = $tvlist[$tv->get('id')];   //$this->getProperty($tvKey,null);
          // $value = $tvlist[$tv->get('name')];   //$this->getProperty($tvKey,null);

              /* set value of TV */
              if ($tv->get('type') != 'checkbox') {
                  $value = $value !== null ? $value : $tv->get('default_text');
              } else {
                  $value = $value ? $value : '';
              }

              /* validation for different types */
              switch ($tv->get('type')) {
                  case 'url':
                      $prefix = $this->getProperty($tvKey.'_prefix','');
                      if ($prefix != '--') {
                          $value = str_replace(array('ftp://','http://'),'', $value);
                          $value = $prefix.$value;
                      }
                      break;
                  case 'date':
                      $value = empty($value) ? '' : strftime('%Y-%m-%d %H:%M:%S',strtotime($value));
                      break;
                  /* ensure tag types trim whitespace from tags */
                  case 'tag':
                  case 'autotag':
                      $tags = explode(',',$value);
                      $newTags = array();
                      foreach ($tags as $tag) {
                          $newTags[] = trim($tag);
                      }
                      $value = implode(',',$newTags);
                      break;
                  default:
                      /* handles checkboxes & multiple selects elements */
                      if (is_array($value)) {
                          $featureInsert = array();
                          while (list($featureValue, $featureItem) = each($value)) {
                              if(empty($featureItem)) { continue; }
                              $featureInsert[count($featureInsert)] = $featureItem;
                          }
                          $value = implode('||',$featureInsert);
                      }
                      break;
              }

          // $tvlist[$tv->get('id')]=$value;
          $resultTvlist[$tv->get('name')]=$value;
      }
      // $result=123;

      return $resultTvlist;
  }









}


