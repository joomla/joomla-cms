<?php
/**
 * @version		$Id: mod_login.php 22338 2011-11-04 17:24:53Z github_bot $
 * @package		Joomla.Administrator
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$langs	= modLoginHelper::getLanguageList();
$return	= modLoginHelper::getReturnURI();
echo $params->get('layout', 'default');
require JModuleHelper::getLayoutPath('mod_login', $params->get('layout', 'default'));

