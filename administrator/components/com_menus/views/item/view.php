<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.view');
jimport('joomla.presentation.pane');

/**
 * @package Joomla
 * @subpackage Menus
 * @since 1.5
 */
class JMenuViewItem extends JView
{

	function edit($tpl=null)
	{
		global $mainframe;

		$this->_layout = 'form';

		$item			= &$this->get('Item');
		$component		= &$this->get('Component');
		$params			= $this->get( 'StateParams' );
		$advanced		= $this->get( 'AdvancedParams' );
		$menuTypes 		= $this->get('MenuTypelist');
		$components		= $this->get('ComponentList');
		$name			= $this->get( 'StateName' );
		$description	= $this->get( 'StateDescription' );

		mosCommonHTML::loadOverlib();

		$document = & JFactory::getDocument();
		if ($item->id) {
			$document->setTitle('Edit Menu Item');
		} else {
			$document->setTitle('New Menu Item');
		}

		// Was showing up null in some cases....
		if (!$item->published) {
			$item->published = 0;
		}

		$lists = new stdClass();
		// Build the state list options
		$put[] = mosHTML::makeOption( '0', JText::_( 'No' ));
		$put[] = mosHTML::makeOption( '1', JText::_( 'Yes' ));
		$put[] = mosHTML::makeOption( '-1', JText::_( 'Trash' ));
		$lists->published = mosHTML::radioList( $put, 'published', '', $item->published );
		$lists->disabled = ($item->type != 'url' ? 'disabled="true"' : '');
		if ($item->type != 'url') {
			$lists->disabled = 'disabled="true"';
			$item->linkfield = '<input type="hidden" name="link" value="'.$item->link.'" />';
		} else {
			$lists->disabled = null;
			$item->linkfield = null;
		}

		$this->assignRef('lists', $lists);
		$this->assignRef('item', $item);
		$this->assignRef('params', $params);
		$this->assignRef('advanced', $advanced);
		$this->assignRef('menutypes', $menuTypes);
		$this->assignRef('name', $name);
		$this->assignRef('description', $description);

		// Add slider pane
		$pane =& JPane::getInstance('sliders');
		$this->assignRef('pane', $pane);

		parent::display($tpl);
	}

	function type($tpl=null)
	{
		global $mainframe;

		$this->_layout = 'type';

		// Add scripts and stylesheets to the document
		$document	= & JFactory::getDocument();
		$url		= $mainframe->getSiteURL();
		$document->addScript($url.'includes/js/joomla/common.js');
		$document->addScript($url.'includes/js/joomla/cookie.js');
		$document->addScript('components/com_menus/assets/tree.js');
		$document->addScript('components/com_menus/assets/description.js');
		$document->addStyleSheet('components/com_menus/assets/type.css');

		mosCommonHTML::loadOverlib();

		// Initialize variables
		$item			= &$this->get('Item');
		$expansion		= &$this->get('Expansion');
		$component		= &$this->get('Component');
		$menuTypes 		= $this->get('MenuTypelist');
		$components		= $this->get('ComponentList');
		$name			= $this->get( 'StateName' );
		$description	= $this->get( 'StateDescription' );

		// Set document title
		if ($item->id) {
			$document->setTitle('Edit Menu Item Type');
		} else {
			$document->setTitle('New Menu Item Type');
		}

		$this->assignRef('item', $item);
		$this->assignRef('components', $components);
		$this->assignRef('expansion', $expansion);
		parent::display($tpl);
	}
}
?>