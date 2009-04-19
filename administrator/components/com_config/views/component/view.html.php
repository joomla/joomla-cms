<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * @package		Joomla.Administrator
 * @subpackage	Config
 */
class ConfigViewComponent extends JView
{
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		$model		= &$this->getModel();
		$params		= &$model->getParams();
		$component	= JComponentHelper::getComponent(JRequest::getCmd('component'));

		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('Edit Preferences'));
		JHtml::_('behavior.tooltip');

		$this->assignRef('params',		$params);
		$this->assignRef('component',	$component);

		parent::display($tpl);
	}
}