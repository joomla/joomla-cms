<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;

$user     = Factory::getUser();
$app      = Factory::getApplication();
$sitename = htmlspecialchars($app->get('sitename', ''), ENT_QUOTES, 'UTF-8');

// Try to get the items from the post-installation model
try
{
	$messagesModel = new \Joomla\Component\Postinstall\Administrator\Model\MessagesModel(['ignore_request' => true]);
	$messages      = $messagesModel->getItems();
}
catch (RuntimeException $e)
{
	$messages = [];

	// Still render the error message from the Exception object
	$app->enqueueMessage($e->getMessage(), 'error');
}

// Load the com_postinstall language file
Factory::getLanguage()->load('com_postinstall', JPATH_ADMINISTRATOR, 'en-GB', true);

require ModuleHelper::getLayoutPath('mod_status', $params->get('layout', 'default'));
