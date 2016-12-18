<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_login
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Login Controller.
 *
 * @since  1.5
 */
class LoginController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		/*
		 * Special treatment is required for this component, as this view may be called
		 * after a session timeout. We must reset the view and layout prior to display
		 * otherwise an error will occur.
		 */
		$this->input->set('view', 'login');
		$this->input->set('layout', 'default');

		// For non-html formats we do not have login view, so just display 403 instead
		if ($this->input->get('format', 'html') !== 'html')
		{
			throw new RuntimeException(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		parent::display();
	}

	/**
	 * Method to log in a user.
	 *
	 * @return  void
	 */
	public function login()
	{
		// Check for request forgeries.
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		$model = $this->getModel('login');
		$credentials = $model->getState('credentials');
		$return = $model->getState('return');

		$result = $app->login($credentials, array('action' => 'core.login.admin'));

		if (!($result instanceof Exception))
		{
			// Only redirect to an internal URL.
			if (JUri::isInternal($return))
			{
				// If &tmpl=component - redirect to index.php
				if (strpos($return, "tmpl=component") === false)
				{
					$app->redirect($return);
				}
				else
				{
					$app->redirect('index.php');
				}
			}
		}

		parent::display();
	}

	/**
	 * Method to log out a user.
	 *
	 * @return  void
	 */
	public function logout()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		$userid = $this->input->getInt('uid', null);

		if ($app->get('shared_session', '0'))
		{
			$clientid = null;
		}
		else
		{
			$clientid = $userid ? 0 : 1;
		}

		$options = array(
			'clientid' => $clientid,
		);

		$result = $app->logout($userid, $options);

		if (!($result instanceof Exception))
		{
			$model  = $this->getModel('login');
			$return = $model->getState('return');

			// Only redirect to an internal URL.
			if (JUri::isInternal($return))
			{
				$app->redirect($return);
			}
		}

		parent::display();
	}
}
