<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Config Component Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @since 1.5
 */
class ConfigController extends JController
{
	public function display()
	{
		// Get the document object.
		$document	= &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'application');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		// Get and render the view.
		if ($view = &$this->getView($vName, $vFormat)) {
			switch ($vName) {
				case 'component':
					$model = &$this->getModel($vName);
					$model->setState('component', JRequest::getCmd('component'));
					$view->setModel($model, true);
					break;
				case 'application':
				default:
					$model = &$this->getModel($vName);
					$view->setModel($model, true);
					$lName = 'config';
					break;
			}

			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}
}