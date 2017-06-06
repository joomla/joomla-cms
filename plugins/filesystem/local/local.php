<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  FileSystem.Local
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('filesystem.local.adapter.adapter', JPATH_PLUGINS);

/**
 * FileSystem Local plugin.
 * The plugin used to manipulate local filesystem in Media Manager
 *
 * @package  FileSystem.Local
 * @since    __DEPLOY_VERSION__
 */
class PlgFileSystemLocal extends JPlugin
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
	 * @return   MediaFileAdapterLocal return a new MediaFileAdapterLocal
	 *
	 * @since    version  __DEPLOY_VERSION__
	 */
	public function onFileSystemGetAdapters()
	{
		// Compile the root path
		$root = JPATH_ROOT . '/' . JComponentHelper::getParams('com_media')->get('file_path', 'images');
		$root = rtrim($root) . '/';

		return new MediaFileAdapterLocal($root);
	}
}
