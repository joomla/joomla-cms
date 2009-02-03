<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.controller');

/**
 * Base controller class for Members.
 *
 * @package		Joomla.Site
 * @subpackage	com_members
 * @version		1.0
 */
class MembersController extends JController
{
	/**
	 * Method to display a view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function display()
	{
		// Get the document object.
		$document = & JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName	 = JRequest::getWord('view', 'login');
		$vFormat = $document->getType();
		$lName	 = JRequest::getWord('layout', 'default');

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
						$app->redirect(JRoute::_('index.php?option=com_members&view=profile', false));
					}

					// The user is a guest, load the registration model and show the registration page.
					$model = & $this->getModel('Registration');
					break;

				// Load the member model for login and profile views.
				case 'login':
					$model = & $this->getModel('Member');
					break;

				// By default load the model based on the requested view.
				default:
					$model = & $this->getModel($vName);
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