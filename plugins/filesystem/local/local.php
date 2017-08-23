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
		// Compile the root path
		$filePath = ComponentHelper::getParams('com_media')->get('file_path', 'images');
		$root = JPATH_ROOT . '/' . $filePath;
		$root = rtrim($root) . '/';

		return [new \Joomla\Plugin\Filesystem\Local\Adapter\LocalAdapter($root, $filePath)];
	}
}
