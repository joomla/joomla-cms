<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Capabilities view class
 *
 * @since  __DEPLOY_VERSION__
 */
class PrivacyViewCapabilities extends JViewLegacy
{
	/**
	 * The reported extension capabilities
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $capabilities;

	/**
	 * The HTML markup for the sidebar
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $sidebar;

	/**
	 * The state information
	 *
	 * @var    JObject
	 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_PRIVACY_VIEW_CAPABILITIES'), 'lock');

		JToolbarHelper::preferences('com_privacy');

		JToolbarHelper::help('JHELP_COMPONENTS_PRIVACY_CAPABILITIES');
	}
}
