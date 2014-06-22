<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media File Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       1.5
 * @deprecated  4.0
*/
class MediaControllerFile extends JControllerLegacy
{
	/**
	 * The folder we are uploading into
	 *
	 * @var   string
	 */
	protected $folder = '';

	/**
	 * Upload one or more files
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use MediaControllerMediaUpload instead.
	 */
	public function upload()
	{
		JLog::add('MediaControllerFile.upload() is deprecated. Use MediaControllerMediaUpload instead.', JLog::WARNING, 'deprecated');

		$controller = new MediaControllerMediaUpload;

		return $controller->execute();
	}

	/**
	 * Check that the user is authorized to perform this action
	 *
	 * @param   string  $action  - the action to be peformed (create or delete)
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 * @deprecated  4.0
	 */
	protected function authoriseUser($action)
	{
		if (!JFactory::getUser()->authorise('core.' . strtolower($action), 'com_media'))
		{
			// User is not authorised
			JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_' . strtoupper($action) . '_NOT_PERMITTED'));

			return false;
		}

		return true;
	}

	/**
	 * Deletes paths from the current path
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use MediaControllerMediaDelete instead.
	 */
	public function delete()
	{
		JLog::add('MediaControllerFile.delete() is deprecated. Use MediaControllerMediaDelete instead.', JLog::WARNING, 'deprecated');

		$controller = new MediaControllerMediaDelete;

		return $controller->execute();
	}
}
