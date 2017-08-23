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

/**
 * FileSystem Dropbox plugin.
 * The plugin used to manipulate dropbox filesystem in Media Manager
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgFileSystemDropbox extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Returns a dropbox media adapter to the caller which can be used to manipulate files
	 *
	 * @return   \Joomla\Plugin\Filesystem\Dropbox\Adapter\JoomlaDropboxAdapter[]
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public function onFileSystemGetAdapters()
	{
		$apiToken = $this->params->get('api_token');

		return [new \Joomla\Plugin\Filesystem\Dropbox\Adapter\JoomlaDropboxAdapter($apiToken)];
	}
}
