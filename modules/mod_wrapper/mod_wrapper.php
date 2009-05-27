<?php
/**
 * @version		$Id: mod_banners.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Site
 * @subpackage	mod_wrapper
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'helper.php';

$params = modWrapperHelper::getParams($params);

$load	= $params->get('load');
$url	= $params->get('url');
$target = $params->get('target');
$width	= $params->get('width');
$height = $params->get('height');
$scroll = $params->get('scrolling');
$class	= $params->get('moduleclass_sfx');

require JModuleHelper::getLayoutPath('mod_wrapper');
