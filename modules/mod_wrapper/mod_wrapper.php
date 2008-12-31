<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'helper.php';

$params = modWrapperHelper::getParams($params);

$load	= $params->get( 'load');
$url	= $params->get( 'url');
$target = $params->get( 'target' );
$width	= $params->get( 'width');
$height = $params->get( 'height');
$scroll = $params->get( 'scrolling' );
$class	= $params->get( 'moduleclass_sfx' );

require(JModuleHelper::getLayoutPath('mod_wrapper'));
