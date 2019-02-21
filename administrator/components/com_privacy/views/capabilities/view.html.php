<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Capabilities view class
 *
 * @since  3.9.0
 */
class PrivacyViewCapabilities extends JViewLegacy
{
	/**
	 * The reported extension capabilities
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $capabilities;

	/**
	 * The HTML markup for the sidebar
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	protected $sidebar;

	/**
	 * The state information
	 *
	 * @var    JObject
	 * @since  3.9.0
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 * @since   3.9.0
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		// Initialise variables
		$this->capabilities = $this->get('Capabilities');
		$this->state        = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_PRIVACY_VIEW_CAPABILITIES'), 'lock');

		JToolbarHelper::preferences('com_privacy');

		JToolbarHelper::help('JHELP_COMPONENTS_PRIVACY_CAPABILITIES');
	}
}
