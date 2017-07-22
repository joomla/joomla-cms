<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user     = JFactory::getUser();
$lang     = JFactory::getLanguage();
$app      = JFactory::getApplication();
$sitename = htmlspecialchars($app->get('sitename', ''), ENT_QUOTES, 'UTF-8');

// Try to get the items from the post-installation model
try
{
	$messagesModel = new \Joomla\Component\Postinstall\Administrator\Model\Messages(['ignore_request' => true]);
	$messages      = $messagesModel->getItems();
}
catch (RuntimeException $e)
{
	$messages = [];

	// Still render the error message from the Exception object
	JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
}

// Load the com_postinstall language file
$lang->load('com_postinstall', JPATH_ADMINISTRATOR, 'en-GB', true);

require JModuleHelper::getLayoutPath('mod_status', $params->get('layout', 'default'));
