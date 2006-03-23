<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Renders a custom button
 *
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	Presentation
 * @since		1.1
 */
class JButton_Custom extends JButton
{
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Custom';

	function fetchButton( $type='Custom', $html = '', $id = 'custom' )
	{
		return $html;
	}
	
	/**
	 * Get the button CSS Id
	 * 
	 * @access	public
	 * @return	string	Button CSS Id
	 * @since	1.1
	 */
	function fetchId( $type='Custom', $html = '', $id = 'custom' )
	{
		return $this->_parent->_name.'-'.$id;
	}
}
?>