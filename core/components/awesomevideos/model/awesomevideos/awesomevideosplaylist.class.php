<?php
class awesomeVideosPlaylist extends xPDOSimpleObject {
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
  	if ($this->get('playlistId')){
	  	$cacheDir = $this->xpdo->awesomevideos->cacheDir;
	    $path = $cacheDir.$this->get('playlistId').'.jpg';
	    // $this->xpdo->error->addError($path);	// вот так можно зафиксировать ошибку
	    if ($cacheDir && file_exists($path)) {
	    		// удаляем видео только 100% попавшие в папку cache при импорте,
	    		// т.к. остальные могут использоваться и в других местах.
	    		// недостаток: вероятно будет сохраняться мусор в папке кеша, если туда будут пихать свои файлы.
	        @chmod($path,0777);
	        fclose(fopen($path,'a'));
	        unlink($path);
	    }
  	}
    return parent::remove($ancestors);
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
      // unset($properties['HTTP_MODAUTH']);
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

}