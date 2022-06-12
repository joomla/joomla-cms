<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_login
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
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
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
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
		$this->checkToken('request');

		$app = JFactory::getApplication();

		$model = $this->getModel('login');
		$credentials = $model->getState('credentials');
		$return = $model->getState('return');

		$result = $app->login($credentials, array('action' => 'core.login.admin'));

		if ($result && !($result instanceof Exception))
		{
			// Only redirect to an internal URL.
			if (JUri::isInternal($return))
			{
				// If &tmpl=component - redirect to index.php
				if (strpos($return, 'tmpl=component') === false)
				{
					$app->redirect($return);
				}
				else
				{
					$app->redirect('index.php');
				}
			}
		}

		$this->display();
	}

	/**
	 * Method to log out a user.
	 *
	 * @return  void
	 */
	public function logout()
	{
		$this->checkToken('request');

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
