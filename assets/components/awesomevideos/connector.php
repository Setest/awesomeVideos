<?php

set_time_limit(5);
ini_set("max_execution_time", "5"); // включаем 10 минут на ограничение работы скрипта
ini_set("max_input_time", "5"); // включаем 10 минут на ограничение работы скрипта
ini_set('default_socket_timeout', 900); // 900 Seconds = 15 Minutes

// phpinfo();
// return;

/** @noinspection PhpIncludeInspection */
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var awesomeVideos $awesomeVideos */
$awesomeVideos = $modx->getService('awesomevideos', 'awesomeVideos', $modx->getOption('awesomevideos_core_path', null, $modx->getOption('core_path') . 'components/awesomevideos/') . 'model/awesomevideos/'
	,array(
		'log'=>array(
			'log_target'=>'ECHO',
			// 'log_status'=>false
		)
	)
);
$modx->lexicon->load('awesomevideos:default');

// handle request
$corePath = $modx->getOption('awesomevideos_core_path', null, $modx->getOption('core_path') . 'components/awesomevideos/');
$path = $modx->getOption('processorsPath', $awesomeVideos->config, $corePath . 'processors/');
$modx->getRequest();
$modx->request->sanitizeRequest();
$modx->request->handleRequest(array(
	'processors_path' => $path,
	'location' => '',
));