<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Content
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Articles component
 *
 * NOTE: Weird spelling used because Joomla! framework prefers
 * view names not to contain "View"
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.0
 */
class ContentViewPrevuuw extends JView
{
	function display($tpl = null)
	{
		// Initialize variables
		$document		=& JFactory::getDocument();
		$db 			=& JFactory::getDBO();
		$id				= JRequest::getVar( 'id', 0, '', 'int' );
		$option			= JRequest::getCmd( 'option' );

		// Get the current default template
		$query = 'SELECT template' .
				' FROM #__templates_menu' .
				' WHERE client_id = 0' .
				' AND menuid = 0';
		$db->setQuery($query);
		$template = $db->loadResult();

		// check if template editor stylesheet exists
		if (!file_exists( JPATH_SITE.DS.'templates'.DS.$template.DS.'css'.DS.'editor.css' )) {
			$template = 'system';
		}

		// Set page title
		$document->setTitle(JText::_('Article Preview'));
		$document->addStyleSheet('/templates/'.$template.'/css/editor.css');
		$document->setBase(JURI::root());
		$document->setLink(JURI::root());

		// Render article preview
		parent::display($tpl);
	}
}