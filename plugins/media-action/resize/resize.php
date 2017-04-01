<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.resize
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('components.com_media.libraries.media.plugin.mediaaction', JPATH_ADMINISTRATOR);

/**
 * Media Manager Resize Action
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgMediaActionResize extends MediaActionPlugin
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
			'plg_media-action_' . $this->_name . '/interact.min.js',
			array('version' => 'auto', 'relative' => true)
		);
	}
}
