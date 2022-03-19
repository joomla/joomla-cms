<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_messages
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

// Check permissions.
if (!$app->getIdentity()->authorise('core.login.admin') || !$app->getIdentity()->authorise('core.manage', 'com_messages'))
{
	return;
}

// Try to get the items from the messages model
try
{
	/** @var \Joomla\Component\Messages\Administrator\Model\MessagesModel $messagesModel */
	$messagesModel = $app->bootComponent('com_messages')->getMVCFactory()
		->createModel('Messages', 'Administrator', ['ignore_request' => true]);
	$messagesModel->setState('filter.state', 0);
	$messages = $messagesModel->getItems();
}
catch (RuntimeException $e)
{
	$messages = [];

	// Still render the error message from the Exception object
	$app->enqueueMessage($e->getMessage(), 'error');
}

$countUnread = count($messages);

require ModuleHelper::getLayoutPath('mod_messages', $params->get('layout', 'default'));
