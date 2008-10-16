<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Admin
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
 * HTML View class for the Admin component
 *
 * @static
 * @package		Joomla
 * @subpackage	Admin
 * @since 1.0
 */
class AdminViewSysinfo extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		//Load switcher behavior
		JHTML::_('behavior.switcher');

		$contents = $this->loadTemplate('navigation');
		$document =& JFactory::getDocument();
		$document->setBuffer($contents, 'modules', 'submenu');

		// Toolbar
		JToolBarHelper::title( JText::_( 'Information' ), 'systeminfo.png' );
		JToolBarHelper::help( 'screen.system.info' );

		parent::display($tpl);
	}

	function get_php_setting($val)
	{
		$r =  (ini_get($val) == '1' ? 1 : 0);
		return $r ? JText::_( 'ON' ) : JText::_( 'OFF' ) ;
	}

	function get_server_software()
	{
		if (isset($_SERVER['SERVER_SOFTWARE'])) {
			return $_SERVER['SERVER_SOFTWARE'];
		} else if (($sf = getenv('SERVER_SOFTWARE'))) {
			return $sf;
		} else {
			return JText::_( 'n/a' );
		}
	}

	function writableRow( $folder, $relative=1, $text='', $visible=1 )
	{
		$writeable		= '<b><span style="color:green;">'. JText::_( 'Writable' ) .'</span></b>';
		$unwriteable	= '<b><span style="color:red;">'. JText::_( 'Unwritable' ) .'</span></b>';

		echo '<tr>';
		echo '<td class="item">';
		echo $text;
		if ( $visible ) {
			echo $folder . '/';
		}
		echo '</td>';
		echo '<td >';
		if ( $relative ) {
			echo is_writable( "../$folder" )	? $writeable : $unwriteable;
		} else {
			echo is_writable( "$folder" )		? $writeable : $unwriteable;
		}
		echo '</td>';
		echo '</tr>';
	}
}