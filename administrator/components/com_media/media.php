<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Application\Web\WebClient;

defined('_JEXEC') or die;

$input  = JFactory::getApplication()->input;
$user   = JFactory::getUser();
$asset  = $input->get('asset');
$author = $input->get('author');

// Access check.
if (!$user->authorise('core.manage', 'com_media') && (!$asset || (!$user->authorise('core.edit', $asset)
	&& !$user->authorise('core.create', $asset)
	&& count($user->getAuthorisedCategories($asset, 'core.create')) == 0)
	&& !($user->id == $author && $user->authorise('core.edit.own', $asset))))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

$client   = JFactory::getApplication()->client;
$basePath = JPATH_COMPONENT_ADMINISTRATOR;

// Show the old media manager for browsers that do not support ECMAScript 5
if ($client->browser == WebClient::IE && $client->version <= 8)
{
	// Load the helper class
	JLoader::register('MediaHelper', JPATH_ADMINISTRATOR . '/components/com_media/legacy/helpers/media.php');

	// Get the component parameters
	$params = JComponentHelper::getParams('com_media');

	// Set the path definitions
	$popup_upload = $input->get('pop_up', null);
	$path         = 'file_path';
	$view         = $input->get('view');

	if (substr(strtolower($view), 0, 6) == 'images' || $popup_upload == 1)
	{
		$path = 'image_path';
	}

	define('COM_MEDIA_BASE', JPATH_ROOT . '/' . $params->get($path, 'images'));
	define('COM_MEDIA_BASEURL', JUri::root() . $params->get($path, 'images'));

	$basePath .= '/legacy';
}

// Autoload the libraries
JLoader::discover('MediaFileAdapter', JPATH_COMPONENT_ADMINISTRATOR . '/libraries/media/file/adapter', true, true);

$controller = JControllerLegacy::getInstance('Media', array('base_path' => $basePath));
$controller->execute($input->get('task'));
$controller->redirect();
