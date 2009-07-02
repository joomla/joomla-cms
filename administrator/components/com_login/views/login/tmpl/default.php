<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_login
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Get the login module
$module = &JModuleHelper::getModule('login');

// Render the login module
echo JModuleHelper::renderModule($module, array('style' => 'rounded', 'id' => 'section-box'));
