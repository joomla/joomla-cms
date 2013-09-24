<?php
/**
 * @package	Joomla.Site
 * @subpackage	mod_admin_services
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

// Include the services functions only once
require_once dirname(__FILE__).'/helper.php';

// Access check for currently logged user
$user = JFactory::getUser();
if (!JAccess::check($user->id, 'core.admin'))
{

	return;
}

// Get javascript
$result = modAdminServicesHelper::execute($params);

//Display javascript
require(JModuleHelper::getLayoutPath('mod_admin_services'));