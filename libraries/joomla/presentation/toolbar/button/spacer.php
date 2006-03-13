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
 * Renders a button spacer
 *
 * @author 		Louis Landry <louis@webimagery.net>
 * @package 	Joomla.Framework
 * @subpackage 	Presentation
 * @since		1.1
 */
class JButton_Spacer extends JButton
{
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Spacer';

	function render( &$definition )
	{
		/*
		 * Initialize variables
		 */
		$html	= null;
		$style	= null;
		if (!empty($definition[1]))
		{
			$style = " style=\"width: $definition[1] px;\"";
		}

		$html	 = "<td class=\"spacer\"$style>\n";
		$html	.= "</td>\n";

		return $html;
	}
}
?>