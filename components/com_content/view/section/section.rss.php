<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the Content component
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JViewRSSSection extends JView
{
	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Section';

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function display()
	{
		global $mainframe;
		
		$document = $mainframe->getDocument();

		//Initialize some variables
		$menu	= & $this->get( 'Menu' );
		$params	= & $menu->parameters;
		$Itemid	= $menu->id;

		// Lets get our data from the model
		$rows = & $this->get( 'Section' );

		$count = count( $rows );
		for ( $i=0; $i < $count; $i++ )
		{
			$Itemid = $mainframe->getItemid( $rows[$i]->id );
			$rows[$i]->link = $rows[$i]->link .'&Itemid='. $Itemid;
		}

		$document->createFeed( $rows, $format, $menu->name, $params );

	}
}
?>
