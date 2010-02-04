<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Base controller class for Users.
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @version		1.0
 */
class UsersController extends JController
{
	/**
	 * Method to display a view.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	function display()
	{
		// Get the document object.
		$document = & JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'login');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		if ($view = & $this->getView($vName, $vFormat))
		{
			// Do any specific processing by view.
			switch ($vName)
			{
				case 'registration':
					// If the user is already logged in, redirect to the profile page.
					$user = & JFactory::getUser();
					if ($user->get('guest') != 1) {
						// Redirect to profile page.
						$app = & JFactory::getApplication();
						$app->redirect(JRoute::_('index.php?option=com_users&view=profile', false));
					}

					// The user is a guest, load the registration model and show the registration page.
					$model = & $this->getModel('Registration');
					break;

				// Handle view specific models.
				case 'profile':
					$model = & $this->getModel($vName);
					break;

				// Handle the default views.
				case 'login':
				case 'reset':
				case 'remind':
				case 'resend':
				case 'profile':
				default:
					$model = &$this->getModel('User');
					break;
			}

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}
}