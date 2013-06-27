<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_similar
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';
$id	= $module->id;
$height = $params->get('height', 350);

//$document = ModMapsHelper::getData($params);
$doc = JFactory::getDocument();
$doc->addScript('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false'); //Add map api script
$doc->addStyledeclaration('#map-canvas-'.$id.' {margin:0;padding:0;height:' . $height . 'px}'); //Add inline stlesheet

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_maps', $params->get('layout', 'default'));
