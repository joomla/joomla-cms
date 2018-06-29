<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.smart-crop
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Manager Smart Crop Action
 *
 * @since  4.0.0
 */
class PlgMediaActionSmartCrop extends \Joomla\Component\Media\Administrator\Plugin\MediaActionPlugin
{
	/**
	 * Load the javascript files of the plugin.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function loadJs()
	{
		parent::loadJs();
	}

	/**
	 * Load the CSS files of the plugin.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function loadCss()
	{
		parent::loadCss();
	}
}
