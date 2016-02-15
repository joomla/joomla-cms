<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Joomla Core Pre-Install View
 *
 * @since  3.1
 */
class InstallationViewPreinstallHtml extends InstallationViewDefault
{
	/**
	 * Array of PHP config options.
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $options;

	/**
	 * Array of PHP settings
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $settings;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.1
	 */
	public function render()
	{
		$this->options  = $this->model->getPhpOptions();
		$this->settings = $this->model->getPhpSettings();

		return parent::render();
	}
}
