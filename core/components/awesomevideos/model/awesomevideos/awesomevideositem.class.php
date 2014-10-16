<?php

/**
 * @package awesomevideos
 */
class awesomeVideosItem extends xPDOSimpleObject {
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
}