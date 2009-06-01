<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Checkin
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Checkin component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Checkin
 * @since 1.0
 */
class CheckinViewCheckin extends JView
{
	protected $tables;

	public function display($tpl = null)
	{
		$model = $this->getModel();
		$tables = $model->checkin();
		
		JToolBarHelper::title(JText::_('Global Check-in'), 'checkin.png');
		JToolBarHelper::help('screen.checkin');
		$this->assignRef('tables', $tables);
		parent::display($tpl);
	}
}
