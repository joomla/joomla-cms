<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Config Component Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @since 1.5
 */
class ConfigController extends JController
{
	/**
	 * @var		string	The default view.
	 * @since	1.6
	 */
	protected $default_view = 'application';

	/**
	 * Method to display the view.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Get the document object.
		$document	= JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getCmd('view', 'application');
		$vFormat	= $document->getType();
		$lName		= JRequest::getCmd('layout', 'default');

		// Get and render the view.
		if ($view = $this->getView($vName, $vFormat)) {
			if ($vName != 'close') {
				// Get the model for the view.
				$model = $this->getModel($vName);

				// Access check.
				if (!JFactory::getUser()->authorise('core.admin', $model->getState('component.option'))) {
					return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
				}

				// Push the model into the view (as default).
				$view->setModel($model, true);
			}

			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}
}