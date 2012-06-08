<?php
/**
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Joomla Core Site Configuration View
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationViewSite extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 */
	public function display($tpl = null)
	{
		$state = $this->get('State');
		$form  = $this->get('Form');
		$sample_installed = $form->getValue('sample_installed', null, 0);

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->state = $state;
		$this->form  = $form;
		$this->sample_installed = $sample_installed;

		parent::display($tpl);
	}
}
