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
 * Dashboard view class
 *
 * @since  __DEPLOY_VERSION__
 */
class PrivacyViewDashboard extends JViewLegacy
{
	/**
	 * Number of urgent requests based on the component configuration
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected $numberOfUrgentRequests;

	/**
	 * Information about whether a privacy policy is published
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $privacyPolicyInfo;

	/**
	 * The request counts
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $requestCounts;

	/**
	 * Information about whether a menu item for the request form is published
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $requestFormPublished;

	/**
	 * The HTML markup for the sidebar
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $sidebar;

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
		$this->privacyPolicyInfo    = $this->get('PrivacyPolicyInfo');
		$this->requestCounts        = $this->get('RequestCounts');
		$this->requestFormPublished = $this->get('RequestFormPublished');

		/** @var PrivacyModelRequests $requestsModel */
		$requestsModel = $this->getModel('requests');

		$this->numberOfUrgentRequests = $requestsModel->getNumberUrgentRequests();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->urgentRequestDays = (int) JComponentHelper::getParams('com_privacy')->get('notify', 14);

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
		JToolbarHelper::title(JText::_('COM_PRIVACY_VIEW_DASHBOARD'), 'lock');

		JToolbarHelper::preferences('com_privacy');

		JToolbarHelper::help('JHELP_COMPONENTS_PRIVACY_DASHBOARD');
	}
}
