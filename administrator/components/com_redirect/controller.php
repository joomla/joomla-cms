<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Invalid Request.');

jimport('joomla.application.component.controller');

/**
 * Base controller class for Redirect.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @version		1.6
 */
class RedirectController extends JController
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
		$document = &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'links');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		// Instantiate the view and model.
		if ($view = &$this->getView($vName, $vFormat))
		{
			switch ($vName)
			{
				case 'link':
					$model = &$this->getModel('link');
					break;

				case 'links':
				default:
					$model = &$this->getModel('links');
					break;
			}

			// Configure the view.
			$view->setModel($model, true);
			$view->setLayout($lName);
			$view->assignRef('document', $document);

			// Display the view.
			$view->display();
		}
	}
}
