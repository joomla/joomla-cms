<?php
/**
 * @package    Joomla.Installation
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Joomla Core Site Configuration View
 *
 * @package  Joomla.Installation
 * @since    3.0
 */
class InstallationViewSite extends JViewLegacy
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
		$form  = $this->get('Form');
		$sample_installed = $form->getValue('sample_installed', null, 0);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->state = $state;
		$this->form  = $form;
		$this->sample_installed = $sample_installed;

		return parent::display($tpl);
	}
}
