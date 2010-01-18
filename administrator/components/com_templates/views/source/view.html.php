<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
 * @since 1.6
 */
class TemplatesViewSource extends JView
{
	protected $state;
	protected $source;
	protected $template;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		jimport('joomla.client.helper');

		// Initialise variables.
		$state		= $this->get('State');
		$template	= $this->get('Template');
		$source		= $this->get('Source');
		$form		= $this->get('Form');
		$ftp		= JClientHelper::setCredentialsFromRequest('ftp');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Bind the record to the form.
		$form->bind($source);

		$this->assignRef('state',		$state);
		$this->assignRef('source',		$source);
		$this->assignRef('template',	$template);
		$this->assignRef('form',		$form);
		$this->assignRef('ftp',			$ftp);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 */
	protected function _setToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$canDo		= TemplatesHelper::getActions();

		JToolBarHelper::title(JText::_('Templates_Manager_Edit_file'));

		// If not checked out, can save the item.
		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::apply('source.apply');
			JToolBarHelper::save('source.save');
		}

		JToolBarHelper::cancel('source.cancel');
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.source.edit');
	}
}
