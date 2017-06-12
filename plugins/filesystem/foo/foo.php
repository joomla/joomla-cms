<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  FileSystem.Local
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('filesystem.foo.adapter.adapter', JPATH_PLUGINS);

/**
 * FileSystem Foo plugin.
 * This is a mock plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgFileSystemFoo extends JPlugin
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
	 * @return   MediaFileAdapterFoo
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public function onFileSystemGetAdapters()
	{
		// Compile the root path
		$root = JPATH_ROOT . '/fooFiles';
		$root = rtrim($root) . '/';

		return new MediaFileAdapterFoo($root);
	}
}
