<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_post_installation_messages
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Helper\ModuleHelper;

// Try to get the items from the post-installation model
try
{
	/** @var \Joomla\Component\Postinstall\Administrator\Model\MessagesModel $messagesModel */
	$messagesModel = $app->bootComponent('com_postinstall')->getMVCFactory()
		->createModel('Messages', 'Administrator', ['ignore_request' => true]);
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
