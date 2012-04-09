<?php
/**
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * The HTML Joomla Core Install Complete View
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationViewComplete extends JView
{
	/**
	 * Display the view
	 *
	 */
	public function display($tpl = null)
	{
		$state = $this->get('State');
		$options = $this->get('Options');

		// Get the config string from the session.
		$session = JFactory::getSession();
		$config = $session->get('setup.config', null);

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->state   = $state;
		$this->options = $options;
		$this->config  = $config;

		parent::display($tpl);
	}
}
