<?php

abstract class awesomeVideosHelper{


  /**
   * Возвращает значение константы класса modX
   * @param  [string] $const имя константы
   * @return [int]  значение константы
   */
  protected function _getModxConst($const){
    // $this->modx->log(modX::LOG_LEVEL_INFO,'CONST: '.$const);
    // $res=(is_numeric($const))? $const : constant('modX::'.strtoupper($const));
    // $this->modx->log(modX::LOG_LEVEL_INFO,'CONST RESULT: '.$res);
    return (is_numeric($const))? $const : constant('modX::'.strtoupper($const));
  }


  /**
   * Записывает лог, в случае если тот установлен в конфиге
   * @param  [string, array] $message  сообщения для лога
   * @param  [string] $def      уровень различия, можно послать любое значение, оно отразиться в строке лога как префикс, можно передавать например номер строки __LINE__
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

    // перезапишем данные в плейсхолдере
    // strftime('%Y-%m-%d %H:%M:%S')
    $microtime=$this->modx->getMicroTime();
    $time= sprintf( "%2.4f s", $this->modx->getMicroTime() );
    // $this->_logContent.="#{$time}# ".$message;
    $logDef=($def)?"# $def # ":'';
    $this->_logContent[$microtime]=$logDef.$message;

    if ($this->config['log']['log_target']=='ECHO') $message="<pre>$message</pre>";

    if ($this->config['log']['log_placeholder']){
      // $oldLog=$this->modx->getPlaceholder($this->config['log']['log_placeholder']);
      $this->modx->setPlaceholder($this->config['log']['log_placeholder'],implode("<br/>", $this->_logContent));
    }else{
      flush();  // иначе в циклах херня происходит
      usleep(1000); //
      // $this->modx->log($logLevel, $message, '', $def);
      $this->modx->log($logLevel, '<pre>'.$message.'</pre>', '', $def);
      // $this->modx->log(1, '<pre>'.$message.'</pre>', '', $def);
      // $this->modx->log(modX::LOG_LEVEL_WARN,'Class is not already loaded');
    }
    return false;
  }

}