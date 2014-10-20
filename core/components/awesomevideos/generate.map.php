<?php
/**
 * The script for generation map files and data tables from schema.
 *
 * @package awesomevideos
 * @subpackage model
 */
// http://copy.sportsreda.ru/awesomeVideos/core/components/awesomevideos/generate.map.php

define('MODX_API_MODE', true);
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/index.php';
/*******************************************************/

$package = 'awesomevideos'; // Class name for generation
$suffix = 'mse2_'; // Suffix of tables.
$prefix = $modx->config['table_prefix']; // table prefix

// Folders for schema and model
$Model = dirname(__FILE__).'/model/';
$Schema = dirname(__FILE__).'/model/schema/';
$xml = $Schema.$package.'.mysql.schema.xml';

// Remove old files
rrmdir($Model.$package .'/mysql');

/*******************************************************/

$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');
$modx->error->message = null;
$modx->loadClass('transport.modPackageBuilder', '', false, true);
$manager = $modx->getManager();

$generator = $manager->getGenerator();
$generator->parseSchema($xml, $Model);
$modx->addPackage($package, $Model);



$manager->removeObjectContainer('awesomeVideosItem');
$manager->removeObjectContainer('awesomeVideosPlaylist');

// создадим таблицы в БД:
$manager->createObjectContainer('awesomeVideosItem');
$manager->createObjectContainer('awesomeVideosPlaylist');




print "\nDone\n";


/********************************************************/
function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);

		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
			}
		}

		reset($objects);
		rmdir($dir);
	}
}