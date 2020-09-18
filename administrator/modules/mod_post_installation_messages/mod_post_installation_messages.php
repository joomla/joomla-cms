<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_post_installation_messages
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Helper\ModuleHelper;

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

$joomlaFilesExtensionId = ExtensionHelper::getExtensionRecord('joomla', 'file')->extension_id;

// Load the com_postinstall language file
$app->getLanguage()->load('com_postinstall', JPATH_ADMINISTRATOR, 'en-GB', true);

require ModuleHelper::getLayoutPath('mod_post_installation_messages', $params->get('layout', 'default'));
