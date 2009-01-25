<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Content
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
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
 * @package		Joomla.Administrator
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