<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.crop
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('components.com_media.libraries.media.plugin.mediaaction', JPATH_ADMINISTRATOR);

/**
 * Media Manager Crop Action
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgMediaActionCrop extends MediaActionPlugin
{
	/**
	 * Load the javascript files of the plugin.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function loadJs()
	{
		parent::loadJs();

		JHtml::_(
			'script',
			'plg_media-action_' . $this->_name . '/cropper.min.js',
			array('version' => 'auto', 'relative' => true)
		);
	}

	/**
	 * Load the CSS files of the plugin.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function loadCss()
	{
		parent::loadCss();

		JHtml::_(
			'stylesheet',
			'plg_media-action_' . $this->_name . '/cropper.min.css',
			array('version' => 'auto', 'relative' => true)
		);
	}
}
