<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Installation Install View
 *
 * @since  3.1
 */
class InstallationViewInstallHtml extends JViewHtml
{
	/**
	 * Redefine the model so the correct type hinting is available.
	 *
	 * @var     InstallationModelSetup
	 * @since   3.1
	 */
	protected $model;

	/**
	 * The session options
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $options;

	/**
	 * The installation tasks to perform
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $tasks = array();

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.1
	 */
	public function render()
	{
		$this->options = $this->model->getOptions();

		/*
		 * Prepare the tasks array
		 * Note: The first character of the task *MUST* be capitalised or the application will not find the task
		 */
		$this->tasks[] = ($this->options['db_old'] == 'remove') ? 'Database_remove' : 'Database_backup';
		$this->tasks[] = 'Database';

		if ($this->options['sample_file'])
		{
			$this->tasks[] = 'Sample';
		}

		$this->tasks[] = 'Config';

		if ($this->options['summary_email'])
		{
			$this->tasks[] = 'Email';
		}

		return parent::render();
	}
}
