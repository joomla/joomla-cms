<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_wrapper
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Content Component Controller
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since		1.5
 */
class WrapperController extends JController
{
	/**
	 * Display the view
	 */
	function display()
	{
		// Initialise variables.
		$document	= &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'wrapper');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		// Get and render the view.
		if ($view = &$this->getView($vName, $vFormat))
		{
			// Get the model for the view.
			$model	= &$this->getModel($vName);

			// Push the model into the view (as default).
			if (!empty($model)) {
				$view->setModel($model, true);
			}
			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}
}
