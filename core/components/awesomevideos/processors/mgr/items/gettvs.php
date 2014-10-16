<?php
// set_time_limit(10);
// ini_set("max_execution_time", "600"); // включаем 10 минут на ограничение работы скрипта
// ini_set("max_input_time", "600"); // включаем 10 минут на ограничение работы скрипта

$modx->getService('smarty', 'smarty.modSmarty');

if (!isset($modx->smarty)) {
    $modx->getService('smarty', 'smarty.modSmarty', '', array('template_dir' => $modx->getOption('manager_path') . 'templates/' . $modx->getOption('manager_theme', null, 'default') . '/', ));
}
$modx->smarty->template_dir = $modx->getOption('manager_path') . 'templates/' . $modx->getOption('manager_theme', null, 'default') . '/';

// $modx->smarty->assign('OnResourceTVFormPrerender', $onResourceTVFormPrerender);
$modx->smarty->assign('_config', $modx->config);

if (file_exists(MODX_CORE_PATH . 'model/modx/modmanagercontroller.class.php')) {
    require_once MODX_CORE_PATH . 'model/modx/modmanagercontroller.class.php';
    require_once MODX_CORE_PATH . 'model/modx/modmanagercontrollerdeprecated.class.php';
    $c = new modManagerControllerDeprecated($this->modx, array());
    $modx->controller = call_user_func_array(array($c, 'getInstance'), array($this->modx, 'modManagerControllerDeprecated', array()));
}



// вызываем события TV параметров, по сути т.к ресурс и шаблон у нас не известен,то их можно и не вызывать
// но также можно использовать нулевое значение
$onResourceTVFormPrerender = $modx->invokeEvent('OnResourceTVFormPrerender',array(
    'resource' => 0,
));
if (is_array($onResourceTVFormPrerender)) {
    $onResourceTVFormPrerender = implode('',$onResourceTVFormPrerender);
}
$result['onResourceTVFormPrerender']=$onResourceTVFormPrerender;

$onResourceTVFormRender = $modx->invokeEvent('OnResourceTVFormRender',array(
            // 'categories' => $categories,
            // 'template' => 0,
            'resource' => 0,
            // 'tvCounts' => 1,
            // 'hidden' => array(),
        ));
if (is_array($onResourceTVFormRender)) {
    $onResourceTVFormRender = implode('',$onResourceTVFormRender);
}
$result['onResourceTVFormRender']=$onResourceTVFormRender;


$curTvValues=array();
$selectedId=($_REQUEST['selected'])?$_REQUEST['selected']:false;   // массив выбранных документов в гриде

if ($selectedId){
    $modx->getService('vidlister','VidLister',$modx->getOption('vidlister.core_path',null,$modx->getOption('core_path').'components/vidlister/').'model/vidlister/');
    $modx->lexicon->load('vidlister:default');
    $c = $modx->newQuery('vlVideo');

    $where["id:IN"]=$selectedId;
    // $where["id:IN"]=array(1,2,3,4,5);

    //criteria
    if (!empty($where)) {
        $c->where($where);
    }

    $videos = $modx->getCollection('vlVideo', $c);
    // echo "<pre>";
    // print_r($where);
    foreach($videos as $video)
    {
        if ($video->tvdata) $curTvValues = $modx->fromJson($video->tvdata);
    }

}


/* get categories */
$c = $modx->newQuery('modCategory');
$c->sortby('id','ASC');
$categories = $modx->getCollection('modCategory',$c);
$emptycat = $modx->newObject('modCategory');
$emptycat->set('category',ucfirst($modx->lexicon('uncategorized')));
$emptycat->id = 0;
$categories[0] = $emptycat;

$tvsListConfig=$modx->getOption('vidlister.video.tvs',null,'');
$tvsListConfig=explode(",", $tvsListConfig);
$tvs = $modx->getCollection('modTemplateVar',array('name:IN'=>$tvsListConfig));

$modx->controller->setPlaceholder('tvcount',count($tvs));

// if (count($tvs)){
    // вытаскиваем значения параметров для текщего ID
// }

foreach ($tvs as $tv) {

    $cat = (int)$tv->get('category');
    // echo $cat;
    // $cat = 0;   // пусть будет одна категория по-умолчанию
    $default = $tv->processBindings($tv->get('default_text'),$resourceId);
    if (strpos($tv->get('default_text'),'@INHERIT') > -1 && (strcmp($default,$tv->get('value')) == 0 || $tv->get('value') == null)) {
        $tv->set('inherited',true);
    }

    if ($tv->get('value') == null) {
        $v = $tv->get('default_text');
        if ($tv->get('type') == 'checkbox' && $tv->get('value') == '') {
            $v = '';
        }
        $tv->set('value',$v);
    }


    // $inputForm = $tv->renderInput(0,array("value"=>"banners/220x400.jpg"));
    $value=(isset($curTvValues[$tv->get('name')]))?$curTvValues[$tv->get('name')]:$tv->get('default_text');
    // $value=(isset($curTvValues[$tv->get('id')]))?$curTvValues[$tv->get('id')]:$tv->get('default_text');
    $result['res'][$tv->get('id')]=$value;
    // $inputForm = $tv->renderInput(0,array("value"=>"17660||17719" ));
    $inputForm = $tv->renderInput(0,array("value"=>$value ));

    if (empty($inputForm)) continue;
    // echo $inputForm; return;
    $result['renderInput'][]=$inputForm;
    $tv->set('formElement',$inputForm);
    // $hidden[]=$tv;

    /* add to tv/category map */
    $tvMap[$tv->get('id')] = $tv->category;

    if (!is_array($categories[$cat]->tvs)) {
        $categories[$cat]->tvs = array();
        $categories[$cat]->tvCount = 0;
        // $categories[$cat]->category = ucfirst($this->modx->lexicon('uncategorized'));
    }

    $prevTvs=$categories[$cat]->get("tvs");
    // $prevTvs=$prevTvs||array();
    $prevTvs[]=(Object)$tv;

    // $categories[$cat]->tvs[] = (Object)$tv; //->toArray();
    $categories[$cat]->set("tvs",$prevTvs);
    // $categories[$cat]->tvCount++;
    if ($tv->get('type') != 'hidden') {
        $prevCount=$categories[$cat]->get("tvCount");
        $prevCount++;
        $categories[$cat]->set("tvCount",$prevCount);
    }

}

$tvCounts = array();
$finalCategories = array();
foreach ($categories as $n => $category) {
        // print_r($categories[$cat]->toArray());
        // echo $category->caption;
    if (is_object($category) && $category instanceof modCategory) {
        $category->hidden = empty($category->tvCount) ? true : false;
        $ct = count($category->tvs);
        if ($ct > 0) {
            $finalCategories[$category->get('id')] = $category->toArray();
            $tvCounts[$n] = $ct;
            // print_r($finalCategories[$category->get('id')]["category"]);
        }
    }
}


$modx->controller->setPlaceholder('categories',$finalCategories);
$modx->controller->setPlaceholder('tvCounts',$modx->toJSON($tvCounts));
$modx->controller->setPlaceholder('tvMap',$modx->toJSON($tvMap));
$modx->controller->setPlaceholder('hidden',$tv);
// обязательно переписываем эти плейсхолдеры
$modx->controller->setPlaceholder('OnResourceTVFormPrerender','');
$modx->controller->setPlaceholder('OnResourceTVFormRender','');


$tvs = $modx->controller->fetchTemplate('resource/sections/tvs.tpl');
$modx->controller->setPlaceholder('tvOutput',$tvs);

// можно все это заменить на метод getRender

$output = '
        <div id="modx-resource-tvs-div" class="modx-resource-tab x-form-label-left x-panel">{$tvOutput}</div>
';


$output_result=$modx->controller->modx->smarty->fetch('string:'.$output);

// нужно выдернуть отдельно все скрипты что - бы запускать их из вне
preg_match_all('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', $output_result, $result['scripts']);
$result['scripts']=$result['scripts'][0];
$output_result = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $output_result);
// $output_result = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '<script type="text/javascript"></script>', $code);

$result['output']=$output_result;


$result['success']=true;
return $modx->toJson($result);

