<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Joomla Core Install View
 *
 * @package  Joomla.Installation
 * @since    3.0
 */
class InstallationViewInstall extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function display($tpl = null)
	{
		$state = $this->get('State');
		$options = $this->get('Options');

		// Get the config string from the session.
		$session = JFactory::getSession();
		$config = $session->get('setup.config', null);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(implode("\n", $errors), 'error');
			return false;
		}

		$this->state   = $state;
		$this->options = $options;
		$this->config  = $config;

		parent::display($tpl);
	}
}
