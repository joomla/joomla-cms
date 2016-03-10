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
 * The Installation Summary View
 *
 * @since  3.1
 */
class InstallationViewSummaryHtml extends InstallationViewDefault
{
	/**
	 * The session options
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $options;

	/**
	 * The PHP options checked by the installer
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $phpoptions;

	/**
	 * The PHP settings checked by the installer
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $phpsettings;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.1
	 */
	public function render()
	{
		$this->options     = $this->model->getOptions();
		$this->phpoptions  = $this->model->getPhpOptions();
		$this->phpsettings = $this->model->getPhpSettings();

		return parent::render();
	}
}
