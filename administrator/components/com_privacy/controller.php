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
 * Privacy Controller
 *
 * @since  __DEPLOY_VERSION__
 */
class PrivacyController extends JControllerLegacy
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $default_view = 'requests';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($cachable = false, $urlparams = array())
	{
		JLoader::register('PrivacyHelper', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/privacy.php');

		// Load the submenu.
		PrivacyHelper::addSubmenu($this->input->get('view', $this->default_view));

		return parent::display();
	}

	/**
	 * Fetch and report privacy requests in JSON format, for AJAX requests
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function ajax()
	{
		$app = JFactory::getApplication();

		if (!JSession::checkToken('get'))
		{
			$app->setHeader('status', 403, true);
			$app->sendHeaders();
			echo JText::_('JINVALID_TOKEN');
			$app->close();
		}

		/** @var PrivacyModelRequests $model */
		$model    = $this->getModel('requests');	
		$requests = $model->getOlder();

		echo json_encode($requests);

		$app->close();
	}	
}
