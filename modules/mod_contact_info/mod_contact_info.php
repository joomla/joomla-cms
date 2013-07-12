<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_users_latest
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the latest functions only once
require_once dirname(__FILE__) . '/helper.php';

if ('' != $params->get('contact') AND 0 < $params->get('contact'))
{
	$contact = $params->get('contact');
}

// Retrieve data from the database table
if (is_numeric($contact) AND 0 < $contact)
{
	$dataContact = ModContactsInfoHelper::getData($contact, 'com_contact', '#__contact_details', 'id');
}
else
{
	unset($dataContact);
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_contact_info', $params->get('layout', 'default'));
