<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Media-Action.rotate
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Manager Rotate Action
 *
 * @since  4.0.0
 */
class PlgMediaActionRotate extends \Joomla\Component\Media\Administrator\Plugin\MediaActionPlugin
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

		JHtml::_('script', 'vendor/cropperjs/cropper.min.js', array('version' => 'auto', 'relative' => true));
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

		JHtml::_('stylesheet', 'vendor/cropperjs/cropper.min.css', array('version' => 'auto', 'relative' => true));
	}
}
