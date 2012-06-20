<?php
/**
 * @package    Joomla.Installation
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Joomla Core Pre-Install View
 *
 * @package  Joomla.Installation
 * @since    3.0
 */
class InstallationViewPreinstall extends JViewLegacy
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
		$this->state		= $this->get('State');
		$this->settings		= $this->get('PhpSettings');
		$this->options		= $this->get('PhpOptions');
		$this->sufficient	= $this->get('PhpOptionsSufficient');
		$this->version		= new JVersion;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
		}

		parent::display($tpl);
	}
}
