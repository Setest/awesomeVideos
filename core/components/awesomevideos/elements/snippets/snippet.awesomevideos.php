<?php
/** @var array $scriptProperties */
/** @var awesomeVideos $awesomeVideos */

error_reporting(E_ALL ^ E_NOTICE);ini_set('display_errors', true);
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

echo "<pre>";

// $modx->regClientStartupHTMLBlock('<tag>123</tag>');
// // print_r ($modx->sjscripts);   // sjscripts loadedjscripts jscripts
$path=$modx->getOption('awesomevideos_core_path', null, $modx->getOption('core_path') . 'components/awesomevideos/') . 'model/awesomevideos/';;

// $modx->loadClass('modPhpThumb',$modx->getOption('core_path').'model/phpthumb/',true,true);
// $modx->loadClass('awesomevideos',$path,true,true);
// print_r ($modx->config);
// print_r ($modx->map);
// print_r ($modx->classMap);
//

// echo $modx->getOption('awesomevideos_core_path', null, $modx->getOption('core_path') . 'components/awesomevideos/') . 'model/awesomevideos/';

// echo 123;

// if (!$awesomeVideos = $modx->getService('awesomevideos', 'awesomeVideos', $modx->getOption('awesomevideos_core_path', null, $modx->getOption('core_path') . 'components/awesomevideos/') . 'model/awesomevideos/', $scriptProperties)) {
if (!$awesomeVideos = $modx->getService('awesomevideos', 'awesomeVideos', $modx->getOption('awesomevideos_core_path', null, $modx->getOption('core_path') . 'components/awesomevideos/') . 'model/awesomevideos/', $scriptProperties)) {
	return 'Could not load awesomeVideos class!';
}
// print_r ($modx->packages);


$m = $modx->getManager();
$created = $m->createObjectContainer('awesomeVideosItem');
var_dump($created);
return $created ? 'Table created.' : 'Table not created.';



// Do your snippet code here. This demo grabs 5 items from our custom table.
$tpl = $modx->getOption('tpl', $scriptProperties, 'Item');
$sortby = $modx->getOption('sortby', $scriptProperties, 'name');
$sortdir = $modx->getOption('sortbir', $scriptProperties, 'ASC');
$limit = $modx->getOption('limit', $scriptProperties, 5);
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, "\n");
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);

// Build query
$c = $modx->newQuery('awesomeVideosItem');
$c->sortby($sortby, $sortdir);
$c->limit($limit);
$items = $modx->getIterator('awesomeVideosItem', $c);

// Iterate through items
$list = array();
/** @var awesomeVideosItem $item */
foreach ($items as $item) {
	$list[] = $modx->getChunk($tpl, $item->toArray());
}

// Output
$output = implode($outputSeparator, $list);
if (!empty($toPlaceholder)) {
	// If using a placeholder, output nothing and set output to specified placeholder
	$modx->setPlaceholder($toPlaceholder, $output);

	return '';
}



// By default just return output
return $output;
