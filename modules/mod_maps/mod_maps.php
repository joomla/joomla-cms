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

$doc = JFactory::getDocument();

// Ready parameters
$id                 = $module->id;
$width              = $params->get('width', '100%');
$height             = $params->get('height', '350');
$mapType            = $params->get('mapType','ROADMAP');
$zoomLevel          = $params->get('zoom', '1');
$centerType         = $params->get('mapCenterType', 'address');
$centerCoordinate   = $params->get('mapCenterCoordinate', '0,0');
$centerAddress      = $params->get('mapCenterAddress', 'Earth');
$key                = $params->get('api_key', false);

// Add map api
if ($key) {
	$gmapjs = 'https://maps.googleapis.com/maps/api/js?v=3&key='. $key .'&sensor=false';
}
else {
	$gmapjs = 'https://maps.googleapis.com/maps/api/js?v=3&sensor=false';
}
$doc->addScript($gmapjs);

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_maps', $params->get('layout', 'default'));
