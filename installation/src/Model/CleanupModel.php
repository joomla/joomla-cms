<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

defined('_JEXEC') or die;

/**
 * Cleanup model for the Joomla Core Installer.
 *
 * @since  4.0.0
 */
class CleanupModel extends BaseInstallationModel
{
	/**
	 * Deletes the installation folder. Returns true on success.
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	protected function deleteInstallationFolder()
	{
		$return = \JFolder::delete(JPATH_INSTALLATION) && (!file_exists(JPATH_ROOT . '/joomla.xml') || \JFile::delete(JPATH_ROOT . '/joomla.xml'));

		// Rename the robots.txt.dist file if robots.txt doesn't exist
		if ($return && !file_exists(JPATH_ROOT . '/robots.txt') && file_exists(JPATH_ROOT . '/robots.txt.dist'))
		{
			$return = \JFile::move(JPATH_ROOT . '/robots.txt.dist', JPATH_ROOT . '/robots.txt');
		}

		return $return;
	}
}
