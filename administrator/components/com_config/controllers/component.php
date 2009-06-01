<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once(JPATH_COMPONENT.DS.'views'.DS.'component'.DS.'view.php');

/**
 * Note: this view is intended only to be opened in a popup
 * @package		Joomla.Administrator
 * @subpackage	Config
 */
class ConfigControllerComponent extends JController
{
	/**
	 * Custom Constructor
	 */
	function __construct($default = array())
	{
		$default['default_task'] = 'edit';
		parent::__construct($default);

		$this->registerTask('apply', 'save');
	}

	/**
	 * Show the configuration edit form
	 * @param string The URL option
	 */
	function edit()
	{
		JRequest::setVar('tmpl', 'component'); //force the component template
		$component = JRequest::getCmd('component');

		if (empty($component))
		{
			JError::raiseWarning(500, 'Not a valid component');
			return false;
		}

		// load the component's language file
		$lang = & JFactory::getLanguage();
		$lang->load($component);

		$model = $this->getModel('Component');
		$table = &JTable::getInstance('component');

		if (!$table->loadByOption($component))
		{
			JError::raiseWarning(500, 'Not a valid component');
			return false;
		}

		$view = new ConfigViewComponent();
		$view->assignRef('component', $table);
		$view->setModel($model, true);
		$view->display();
	}

	/**
	 * Save the configuration
	 */
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$component = JRequest::getCmd('component');

		$table = &JTable::getInstance('component');
		if (!$table->loadByOption($component))
		{
			JError::raiseWarning(500, 'Not a valid component');
			return false;
		}

		$post = JRequest::get('post');
		$post['option'] = $component;
		$table->bind($post);

		// pre-save checks
		if (!$table->check()) {
			JError::raiseWarning(500, $table->getError());
			return false;
		}

		// save the changes
		if (!$table->store()) {
			JError::raiseWarning(500, $table->getError());
			return false;
		}

		//$this->setRedirect('index.php?option=com_config', $msg);
		$this->edit();
	}

	/**
	 * Cancel operation
	 */
	function cancel()
	{
		$this->setRedirect('index.php');
	}
}