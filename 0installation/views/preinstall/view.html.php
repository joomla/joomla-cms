<?php
/**
 * @version		$Id: view.html.php 22429 2011-12-02 20:34:43Z github_bot $
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * The HTML Joomla Core Pre-Install View
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationViewPreinstall extends JView
{
	/**
	 * Display the view
	 *
	 * @param	string
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function display($tpl = null)
	{
		$this->state		= $this->get('State');
		$this->settings		= $this->get('PhpSettings');
		$this->options		= $this->get('PhpOptions');
		$this->sufficient	= $this->get('PhpOptionsSufficient');
		$this->version		= new JVersion;

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		parent::display($tpl);
	}
}
