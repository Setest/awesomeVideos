<?php
// set_time_limit(10);
// ini_set("max_execution_time", "600"); // включаем 10 минут на ограничение работы скрипта
// ini_set("max_input_time", "600"); // включаем 10 минут на ограничение работы скрипта

error_reporting(E_ALL ^ E_NOTICE);  ini_set('display_errors', true);

/**
 * Enable an Item
 */
class awesomeVideosItemsDragAndDropProcessor extends modObjectProcessor {
	public $classKey = 'awesomeVideosItem';
	public $languageTopics = array('awesomevideos');
	public $objectType = 'awesomeVideosItem';
	public $checkSavePermission = true;

	function initialize() {
		$primaryKey = $this->getProperty($this->primaryKeyField,false);
		if (empty($primaryKey)) return $this->modx->lexicon($this->objectType.'_err_ns');
		$this->object = $this->modx->getObject($this->classKey,$primaryKey);
		if (empty($this->object)) return $this->modx->lexicon($this->objectType.'_err_nfs',array($this->primaryKeyField => $primaryKey));

		if ($this->checkSavePermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('save')) {
			return $this->modx->lexicon('access_denied');
		}
		return true;
	}

	/**
	 * @return array|string
	 */
	public function process() {
		$source = &$this->object;
		// $source = &$this;

		$sourceId = $this->getProperty('id');
		$targetId = $this->getProperty('targetId');
// echo $targetId;

		$tableName=$this->modx->getTableName($this->classKey);


		$target = $this->modx->getObject($this->classKey,array(
		    'id' => $targetId,
		));
		$targetRank=$target->get('rank');

// $this->modx->getFieldMeta($this->classKey);
// echo "<pre>";

		$c = $this->modx->newQuery($this->classKey);
		$c->command('update');
		if ($source->get('rank') < $target->get('rank')) {
			// echo "MIN";
      $sql= "
        UPDATE {$tableName}
            SET rank = rank - 1
        WHERE
            rank < {$target->get('rank')}
        AND rank > {$source->get('rank')}
        AND rank > 0
        ORDER BY rank DESC
	    ";
	    $targetRank=$target->get('rank')-1;

		}else{
      $sql= "
        UPDATE {$tableName}
            SET rank = rank + 1
        WHERE
            rank >= {$target->get('rank')}
        AND rank < {$source->get('rank')}
        ORDER BY rank DESC
	    ";
		}

    $stmt= $this->modx->prepare($sql);
    // $stmt->execute();
  	// $xxx=$stmt->errorInfo();
		// print_r($stmt);
    if ($stmt && $stmt->execute()) {
    	// echo 'OK';
    	$source->set('rank',$targetRank);
    	// $source->save();
    	// print_r($source->save());
      if (!$source->save()) {
          return $this->modx->error->failure('Ошибка при сохранении RANK у документа с ID = '.$sourceId);
      }
    }else{
    	$error=$stmt->errorInfo();
    	return $this->modx->error->failure('Ошибка при сохранении RANK: '.$error[2]);
    }
		return $this->modx->error->success('');
	}
}

return 'awesomeVideosItemsDragAndDropProcessor';