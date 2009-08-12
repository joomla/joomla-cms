<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Messages component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesViewConfig extends JView
{
	public $vars;

	public function display($tpl = null)
	{
		$vars = $this->get('Vars');

		$this->assignRef('vars', $vars);

		parent::display($tpl);
		$this->_setToolbar();
	}

	protected function _setToolbar()
	{
		JToolBarHelper::title(JText::_('Private Messaging Configuration'), 'inbox.png');
		JToolBarHelper::save('saveconfig');
		JToolBarHelper::cancel('cancelconfig');
		JToolBarHelper::help('screen.messages.conf');
	}
}