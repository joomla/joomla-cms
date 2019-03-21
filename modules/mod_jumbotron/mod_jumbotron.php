<?php
/**
 * @version     1.4
 * @package     mod_jumbotron
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @author      Brad Traversy <support@joomdigi.com> - http://www.bootstrapjoomla.com
 */
//No Direct Access
defined('_JEXEC') or die;

/* Params */
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$show_read_more =  $params->get('show_read_more');
$read_more_text =  $params->get('read_more_text');
$read_more_link =  $params->get('read_more_link');
$header_text =  $params->get('header_text');
$paragraph_text=  $params->get('paragraph_text');
$center_text=  $params->get('center_text');
$background_image=  $params->get('background_image');
$background_color=  $params->get('background_color');
$foreground_image=  $params->get('foreground_image');
$x_pos=  $params->get('x_pos',0);
$y_pos=  $params->get('y_pos',0);
$foreground_image_width=  $params->get('foreground_image_width');
$paragraphtextcolor=  $params->get('paragraphtextcolor');
$headingtextcolor=  $params->get('headingtextcolor');
$buttonstyle=  $params->get('buttonstyle','btn btn-primary btn-lg btn-color');

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

require JModuleHelper::getLayoutPath('mod_jumbotron', $params->get('layout', 'default'));
?>