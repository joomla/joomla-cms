<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * @package		Joomla
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
		$component	= JComponentHelper::getComponent(JRequest::getCmd( 'component' ));

		$document = & JFactory::getDocument();
		$document->setTitle( JText::_('Edit Preferences') );
		JHTML::_('behavior.tooltip');

		$this->assignRef('params',		$params);
		$this->assignRef('component',	$component);

		parent::display($tpl);
	}
}