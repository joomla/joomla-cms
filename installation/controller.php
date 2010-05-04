<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');

/**
 * Base controller class for the Joomla Core Installer.
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationController extends JController
{
	/**
	 * Method to display a view.
	 *
	 * @return	void
	 * @since	1.0
	 */
	public function display()
	{
		// Get the current URI to redirect to.
		$uri		= &JURI::getInstance();
		$redirect	= base64_encode($uri);

		// Get the document object.
		$document = &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'language');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		if ($view = &$this->getView($vName, $vFormat))
		{
			switch ($vName)
			{
				default:
					$model = &$this->getModel('Setup', 'JInstallationModel', array('dbo' => null));
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
