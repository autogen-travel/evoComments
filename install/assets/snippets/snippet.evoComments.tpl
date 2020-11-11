//<?php
/**
 * evoComments
 * 
 * Сниппет вывода комментариев
 *
 * @category 	snippet
 * @version 	0.1
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@modx_category Manager and Admin
 */



include_once(MODX_BASE_PATH . 'assets/modules/evocomments/evocomments.class.php');
$evocomments = new EvoComments();

$render = $evocomments->render();

$modx->regClientScript('assets/modules/evocomments/js/evocomments.js'); 
$modx->regClientCSS('<link rel="stylesheet" href="assets/modules/evocomments/css/evocomments.css">');
return '<div id="evoComments" data-evocomments-page-id="'.$modx->documentIdentifier.'">'.$render.'</div>';