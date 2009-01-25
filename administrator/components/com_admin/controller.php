<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Admin
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Admin Controller
 *
 * @package		Joomla
 * @subpackage	Admin
 * @since 1.5
 */
class AdminController extends JController
{
	/**
	 * Admin component display
	 */
	function display()
	{
		// Get the document object.
		$document	= &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'sysinfo');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		// Get and render the view.
		if ($view = &$this->getView($vName, $vFormat))
		{
			switch ($vName)
			{
				case 'changelog':
					$model = &$this->getModel($vName);
					$view->setModel($model, true);
					break;

				default:
					break;
			}

			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}

	/**
	 * TODO: Description?
	 */
	function keepalive()
	{
		return;
	}
}