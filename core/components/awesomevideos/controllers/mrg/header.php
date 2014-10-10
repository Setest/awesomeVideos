<?php
/**
 * Loads the header for mgr pages.
 *
 * @package vidlister
 * @subpackage controllers
 */
$modx->regClientStartupScript($awesomeVideos->config['jsUrl'].'mgr/vidlister.js');


function LoadRichTextEditor($modx) {
	$event_output = $modx->invokeEvent("OnRichTextEditorRegister");
	if(is_array($event_output))
		$editor = $event_output[0];
	else
		return "";

	$event_output = $modx->invokeEvent("OnRichTextEditorInit", array('editor'=>$editor, 'elements'=>array()));
	if(is_array($event_output))
		$editor_html = implode("",$event_output);
	// echo $editor_html;
	$modx->regClientStartupHTMLBlock($editor_html);
	define("RTE_LOADED", true);
}
if(!defined("RTE_LOADED")) LoadRichTextEditor($modx);




$modx->regClientStartupHTMLBlock('<div id="tvslist"></div>');

// $modx->regClientStartupHTMLBlock('<script type="text/javascript">
// Ext.onReady(function() {
//     VidLister.config = '.$modx->toJSON($awesomeVideos->config).';

// /*MODx.Ajax.request({
//     url: VidLister.config.connectorUrl
//     ,params: {
//         action: "mgr/video/gettvs"
//         ,register: "mgr"
//     }
//     ,listeners: {
//         "success":{fn:function(responce, options ,status) {
//             console.log("TVS:",responce);
//             // console.log("INSIDE:",VideoWindow);
//             // Ext.get("tvslist").update(responce.output);
//         },scope:this}
//     }
// });*/

// });
// </script>');

return '';  // обязательно возвращаем пустую строку иначе будет писать 1 на выходе.