<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Dashboard view class
 *
 * @since  3.9.0
 */
class PrivacyViewDashboard extends JViewLegacy
{
	/**
	 * Number of urgent requests based on the component configuration
	 *
	 * @var    integer
	 * @since  3.9.0
	 */
	protected $numberOfUrgentRequests;

	/**
	 * Information about whether a privacy policy is published
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $privacyPolicyInfo;

	/**
	 * The request counts
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $requestCounts;

	/**
	 * Information about whether a menu item for the request form is published
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $requestFormPublished;

	/**
	 * Flag indicating the site supports sending email
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $sendMailEnabled;

	/**
	 * The HTML markup for the sidebar
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	protected $sidebar;

	/**
	 * Id of the system privacy consent plugin
	 *
	 * @var    integer
	 * @since  3.9.2
	 */
	protected $privacyConsentPluginId;

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
		$this->privacyConsentPluginId = PrivacyHelper::getPrivacyConsentPluginId();
		$this->privacyPolicyInfo      = $this->get('PrivacyPolicyInfo');
		$this->requestCounts          = $this->get('RequestCounts');
		$this->requestFormPublished   = $this->get('RequestFormPublished');
		$this->sendMailEnabled        = (bool) Factory::getConfig()->get('mailonline', 1);

		/** @var PrivacyModelRequests $requestsModel */
		$requestsModel = $this->getModel('requests');

		$this->numberOfUrgentRequests = $requestsModel->getNumberUrgentRequests();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->urgentRequestDays = (int) ComponentHelper::getParams('com_privacy')->get('notify', 14);

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
		JToolbarHelper::title(Text::_('COM_PRIVACY_VIEW_DASHBOARD'), 'lock');

		JToolbarHelper::preferences('com_privacy');

		JToolbarHelper::help('JHELP_COMPONENTS_PRIVACY_DASHBOARD');
	}
}
