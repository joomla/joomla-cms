<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/29/14 12:59 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

defined('CBLIB') or die();

/**
 * CBtoolmenuBar Class implementation
 * 
 */
class CBtoolmenuBar extends cbMenuBarBase
{
	/**
	 * displays $action toolbar button
	 *
	 * @param string $action
	 * @param string $link
	 * @param string $alt
	 * @param string $class
	 */
	public static function linkAction( $action = 'new', $link = null, $alt = 'New', $class = null )
	{
		if ( cbStartOfStringMatch( $link, 'javascript:' ) ) {
			$href		=	'#';
			$onClickJs	=	substr( $link, 11 );
		} else {
			$href		=	$link;
			$onClickJs	=	null;
		}

		CBtoolmenuBar::_output( $onClickJs, $action, $alt, $href, $class );
	}

	/**
	 * displays "edit" toolbar button
	 *
	 * @param string $task
	 * @param string $alt
	 */
	public static function editListNoSelect( $task = 'edit', $alt = 'Edit' )
	{
		CBtoolmenuBar::addToToolBar( $task, $alt, 'Edit', 'edit' );
	}
}
