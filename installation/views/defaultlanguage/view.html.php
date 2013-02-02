<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Joomla Core Default Language View
 *
 * @package  Joomla.Installation
 * @since    3.0
 */
class InstallationViewDefaultlanguage extends JViewLegacy
{
	/**
	 * @var object item list of languages installed in the administrator
	 */
	public $items;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  name of the template to be loaded.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$items = new stdClass;
		$items->administrator	= $this->get('InstalledlangsAdministrator');
		$items->frontend		= $this->get('InstalledlangsFrontend');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(implode("\n", $errors), 'error');
			return false;
		}

		$this->items = $items;

		parent::display($tpl);
	}
}
