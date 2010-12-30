<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_wrapper
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'helper.php';

$params = modWrapperHelper::getParams($params);

$load	= $params->get('load');
$url	= $params->get('url');
$target = htmlspecialchars($params->get('target'));
$width	= $params->get('width');
$height = $params->get('height');
$scroll = $params->get('scrolling');
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_wrapper', $params->get('layout', 'default'));
