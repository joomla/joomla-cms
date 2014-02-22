<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input  = JFactory::getApplication()->input;
$user   = JFactory::getUser();
$asset  = $input->get('asset');
$author = $input->get('author');

// Access check.
if (!$user->authorise('core.manage', 'com_media')
	&&	(!$asset or (
			!$user->authorise('core.edit', $asset)
		&&	!$user->authorise('core.create', $asset)
		&& 	count($user->getAuthorisedCategories($asset, 'core.create')) == 0)
		&&	!($user->id == $author && $user->authorise('core.edit.own', $asset))))
{
	return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
}

$params = JComponentHelper::getParams('com_media');

// Load the helper class
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/media.php';

// Set the path definitions
$popup_upload = $input->get('pop_up', null);
$path = 'file_path';

$view = $input->get('view');
if (substr(strtolower($view), 0, 6) == 'images' || $popup_upload == 1)
{
	$path = 'image_path';
}

define('COM_MEDIA_BASE',    JPATH_ROOT . '/' . $params->get($path, 'images'));
define('COM_MEDIA_BASEURL', JUri::root() . $params->get($path, 'images'));

$controller	= JControllerLegacy::getInstance('Media', array('base_path' => JPATH_COMPONENT_ADMINISTRATOR));
$controller->execute($input->get('task'));
$controller->redirect();
