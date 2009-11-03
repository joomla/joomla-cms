<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit a template style.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesViewTemplate extends JView
{
	protected $state;
	protected $files;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$template	= $this->get('Template');
		$files		= $this->get('Files');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',		$state);
		$this->assignRef('template',	$template);
		$this->assignRef('files',		$files);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 */
	protected function _setToolbar()
	{
		$user		= JFactory::getUser();
		$canDo		= TemplatesHelper::getActions();

		JToolBarHelper::title(JText::_('Templates_Manager_View_Template'), 'thememanager');

		JToolBarHelper::cancel('template.cancel', 'JToolbar_Close');

		JToolBarHelper::help('screen.template.view');
	}

	/**
	 * Helper method to route actions.
	 *
	 * A simple helper method to keep the line length down on many of the URL's in the layout.
	 *
	 * @param	string $suffix
	 *
	 * @return	string
	 */
	protected function route($suffix)
	{
		return JRoute::_('index.php?option=com_templates&'.$suffix);
	}
}
