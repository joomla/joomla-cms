<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_messages
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

// Try to get the items from the messages model
try
{
	$messagesModel = new \Joomla\Component\Messages\Administrator\Model\MessagesModel(['ignore_request' => true]);
	$messagesModel->setState('filter.state', 0);
	$messages      = $messagesModel->getItems();
}
catch (RuntimeException $e)
{
	$messages = [];

	// Still render the error message from the Exception object
	$app->enqueueMessage($e->getMessage(), 'error');
}

$countUnread = count($messages);

require ModuleHelper::getLayoutPath('mod_messages', $params->get('layout', 'default'));
