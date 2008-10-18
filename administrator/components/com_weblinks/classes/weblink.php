<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	HTML
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Utility class for creating HTML Grids
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */

class JHTMLWeblink
{
	function state( &$row, $i, $imgY = 'tick.png', $imgX = 'publish_x.png', $imgR = 'report.png', $prefix='' )
	{
		// State cannot be set to "Reported" here
		$alt 	= $row->state == 1 ? JText::_( 'Published' ) : ($row->state == -1 ? JText::_( 'Reported' ) : JText::_( 'Unpublished' ));
		$img 	= JHTML::_('image.administrator', $row->state == 1 ? $imgY : ($row->state == -1 ? $imgR : $imgX), null, null, null, $alt);
		$task 	= $row->state == 1 ? 'unpublish' : 'publish';
		$action = $row->state == 1 ? JText::_( 'Unpublish Item' ) : JText::_( 'Publish item' );

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $prefix.$task .'\')" title="'. $action .'">'
		. $img .'</a>'
		;

		return $href;
	}

	function statefilter( $filter_state='' )
	{
		$state[] = JHTML::_('select.option',  '', '- '. JText::_( 'Select State' ) .' -' );
		$state[] = JHTML::_('select.option',  'P', JText::_( 'Published' ) );
		$state[] = JHTML::_('select.option',  'U', JText::_( 'Unpublished' ) );
		$state[] = JHTML::_('select.option',  'R', JText::_( 'Reported' ) );

		return JHTML::_('select.genericlist',   $state, 'filter_state', 'class="inputbox" size="1" onchange="submitform( );"', 'value', 'text', $filter_state );
	}

	/**
	* Select list of weblink states
	*/
	function statelist( $name, $active = NULL, $javascript = NULL )
	{
		$state[] = JHTML::_('select.option',  1, JText::_( 'Published' ) );
		$state[] = JHTML::_('select.option',  0, JText::_( 'Unpublished' ) );
		$state[] = JHTML::_('select.option',  -1, JText::_( 'Reported' ) );

		return JHTML::_('select.genericlist', $state, $name, 'class="inputbox" size="1"'. $javascript, 'value', 'text', $active );
	}
}
