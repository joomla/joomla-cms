<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Joomla Core Install Summary View
 *
 * @package  Joomla.Installation
 * @since    3.0
 */
class InstallationViewSummary extends JViewLegacy
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
		$this->state = $this->get('State');
		$this->form = $this->get('Form');
		$this->options = $this->get('Options');

		// Get the config string from the session.
		$session = JFactory::getSession();
		$this->config = $session->get('setup.config', null);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage(implode("\n", $errors), 'error');
			return false;
		}

		$this->phpsettings		= $this->get('PhpSettings');
		$this->phpoptions		= $this->get('PhpOptions');

		parent::display($tpl);
	}
}
