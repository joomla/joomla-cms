<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  FileSystem.Local
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Component\ComponentHelper;

/**
 * FileSystem Local plugin.
 * The plugin used to manipulate local filesystem in Media Manager
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgFileSystemLocal extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Returns a local media adapter to the caller which can be used to manipulate files
	 *
	 * @return   \Joomla\Plugin\Filesystem\Local\Adapter\LocalAdapter[]
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public function onFileSystemGetAdapters()
	{
		$adapters = [];
		$directories = $this->params->get('directories', '[{"directory":{"directory": "images"}}]');

		// Do a check if default settings are not saved by user
		// If not initialize them manually
		if (is_string($directories))
		{
			$directories = json_decode($directories);
			list($directories) = $directories;
		}

		foreach ($directories as $directoryEntity)
		{
			if ($directoryEntity->directory)
			{
				$directoryPath = JPATH_ROOT . '/' . $directoryEntity->directory;
				$directoryPath = rtrim($directoryPath) . '/';
				$adapters[]    = new \Joomla\Plugin\Filesystem\Local\Adapter\LocalAdapter($directoryPath, $directoryEntity->directory);
			}
		}

		return $adapters;
	}
}
