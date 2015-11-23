<?php
/**
 * @version		$Id: view.html.php 01 2012-08-13 11:37:09Z maverick $
 * @package		CoreJoomla.CjLib
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2012 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport( 'joomla.application.component.view');

class CjLibViewDefault extends JViewLegacy {
	
	function display($tpl = null) {

		JFactory::getDocument()->addStyleSheet(JURI::base(true).'/components/com_cjlib/assets/css/styles.css');
		
		JToolBarHelper::title(JText::_('TITLE_CJLIB'), 'cjlib.png');
		JToolBarHelper::save();
		
		parent::display($tpl);
	}
}
?>