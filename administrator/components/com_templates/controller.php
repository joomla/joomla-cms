<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Templates manager master display controller.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesController extends JController
{
	/**
	 * Method to display a view.
	 */
	public function display()
	{
		require_once JPATH_COMPONENT.'/helpers/templates.php';

		// Get the document object.
		$document	= JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'styles');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		// Get and render the view.
		if ($view = &$this->getView($vName, $vFormat))
		{
			// Get the model for the view.
			$model = &$this->getModel($vName);

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();

			// Load the submenu.
			TemplatesHelper::addSubmenu($vName);
		}
	}

	/**
	* Preview Template
	*/
	function preview()
	{
		JRequest::setVar('view', 'prevuuw');
		parent::display();
	}

	/**
	* Save Template Source
	*/
	function save_source()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize some variables
		$filecontent	= JRequest::getVar('filecontent', '', 'post', 'string', JREQUEST_ALLOWRAW);

		$model = $this->getModel('source');
		$client		= &$model->getClient();
		$template	= &$model->getTemplate();

		if (!$template) {
			$this->setRedirect('index.php?option=com_templates&client='.$client->id, JText::_('Operation Failed').': '.JText::_('No template specified.'));
			return;
		}

		if (!$filecontent) {
			$this->setRedirect('index.php?option=com_templates&client='.$client->id, JText::_('Operation Failed').': '.JText::_('Content empty.'));
			return;
		}

		if ($model->store($filecontent)) {
			$msg = JText::_('Template source saved');
		} else {
			$msg = $model->getError();
		}

		$task = JRequest::getCmd('task');
		switch($task)
		{
			case 'apply_source':
				$this->setRedirect('index.php?option=com_templates&client='.$client->id.'&task=edit_source&template='.$template, $msg);
				break;

			case 'save_source':
			default:
				$this->setRedirect('index.php?option=com_templates&client='.$client->id.'&task=edit&template='.$template, $msg);
				break;
		}
	}

	/**
	* Choose Template CSS
	*/
	function choose_css()
	{
		JRequest::setVar('hidemainmenu', 1);
		JRequest::setVar('view', 'csschoose');
		parent::display();
	}

	/**
	* Edit Template CSS
	*/
	function edit_css()
	{
		JRequest::setVar('hidemainmenu', 1);
		JRequest::setVar('view', 'cssedit');
		parent::display();
	}

	/**
	* Save Template CSS
	*/
	function save_css()
	{
		$mainframe = JFactory::getApplication();

		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize some variables
		$filecontent	= JRequest::getVar('filecontent', '', 'post', 'string', JREQUEST_ALLOWRAW);

		$model = $this->getModel('cssedit');
		$client		= &$model->getClient();
		$id			= &$model->getId();
		$filename	= &$model->getFilename();
		$template 	= &$model->getTemplate();
		if (!$template) {
			$this->setRedirect('index.php?option=com_templates&client='.$client->id, JText::_('Operation Failed').': '.JText::_('No template specified.'));
			return;
		}

		if (!$filecontent) {
			$this->setRedirect('index.php?option=com_templates&client='.$client->id, JText::_('Operation Failed').': '.JText::_('Content empty.'));
			return;
		}

		if ($model->store($filecontent)) {
			$msg = JText::_('File Saved');
		} else {
			$msg = $model->getError();
		}

		$task = JRequest::getCmd('task');
		switch($task)
		{
			case 'apply_css':
				$this->setRedirect('index.php?option=com_templates&client='.$client->id.'&task=edit_css&template='.$template.'&id='.$id.'&filename='.$filename, $msg);
				break;

			case 'save_css':
			default:
				$this->setRedirect('index.php?option=com_templates&client='.$client->id.'&task=edit&template='.$template, $msg);
				break;
		}
	}
}