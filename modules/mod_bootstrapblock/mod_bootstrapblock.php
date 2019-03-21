<?php
/**
 * @version     1.1
 * @package     mod_bootstrapblock
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author      Brad Traversy <support@bootstrapjoomla.com> - http://www.bootstrapjoomla.com
 */
//No Direct Access
defined('_JEXEC') or die;

/* Params */
$moduleclass_sfx = 	htmlspecialchars($params->get('moduleclass_sfx'));
$show_read_more =  	$params->get('show_read_more',1);
$read_more_text =  	htmlspecialchars($params->get('read_more_text','Read More'));
$read_more_link =  	htmlspecialchars($params->get('read_more_link','#'));
$headingtext = 		htmlspecialchars($params->get('headingtext','Bootstrap Block'));
$paragraphtext=  	htmlspecialchars($params->get('paragraphtext'));
$paragraphcolor=  	htmlspecialchars($params->get('paragraphcolor'));
$headingcolor=  	htmlspecialchars($params->get('headingcolor'));
$show_glyphicon=  		$params->get('show_glyphicon');
$glyphicon=  		$params->get('glyphicon','glyphicon glyphicon-th');
$buttonstyle=  $params->get('buttonstyle','btn btn-primary');

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

require JModuleHelper::getLayoutPath('mod_bootstrapblock', $params->get('layout', 'default'));
?>