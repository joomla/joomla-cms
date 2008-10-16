<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Utility class for creating different select lists
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JHTMLUser
{
	/**
	* Build the select list for access level
	*/
	function groups( $selected = null, $parentId = 0, $type = 'aro' )
	{
		$model = JModel::getInstance( 'Group', 'UserModel', array( 'ignore_request' => 1 ) );
		$model->setState( 'type', $type );

		// Set the model state to get the groups tree
		$model->setState( 'select',		'a.id AS value, a.name AS text' );
		$model->setState( 'show.tree',	1 );
		$model->setState( 'order by',	'a.lft' );
		$model->setState( 'parent_id',	$parentId );

		// Get a list without resolving foreign keys
		$options = $model->getItems( 0 );

		// Find the level of the parent
		$parentLevel = ($parentId > 0) ? $model->getLevel( $parentId, $type ) : 0;

		// Pad out the options to create a visual tree
		foreach ($options as $i => $option) {
			$options[$i]->text = str_pad( $option->text, strlen( $option->text ) + 2*($option->level - $parentLevel), '- ', STR_PAD_LEFT );
		}
		//array_unshift( $options, JHTML::_( 'select.option', 0, 'Select Group' ) );

		return JHTML::_( 'select.options', $options, 'value', 'text', $selected );
	}
}