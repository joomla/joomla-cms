<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage		HTML
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML utility class for Acl data
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
abstract class JHtmlAcl
{
	/**
	 * Build a select list for Asset Groups
	 *
	 * @param	int $selected	The select value
	 *
	 * @return	string
	 * @since	1.6
	 */
	public static function assetGroups($selected = 0)
	{
		$db =& JFactory::getDBO();

		$db->setQuery(
			'SELECT value, name AS text'
			.' FROM #__core_acl_axo_groups'
			.' WHERE value >= 0'
			.' ORDER BY value'
		);
		try {
			$groups = $db->loadObjectList();
		}
		catch(JException $e) {
			$groups = array();
		}
		return JHtml::_(
			'select.genericlist',
			$groups,
			'access',
			array(
				'list.attr' => 'class="inputbox" size="3"',
				'list.select' => (int) $selected,
				'list.translate' => true
			)
		);
	}

	/**
	* Select list of active users
	*/
	public static function users($name, $active, $nouser = 0, $javascript = NULL, $order = 'name', $reg = 1)
	{
		$db =& JFactory::getDBO();

		$and = '';
		if ($reg) {
		// does not include registered users in the list
			$and = ' AND gid > 18';
		}

		$query = 'SELECT id AS value, name AS text'
		. ' FROM #__users'
		. ' WHERE block = 0'
		. $and
		. ' ORDER BY '. $order
		;
		$db->setQuery($query);
		if ($nouser) {
			$users[] = JHtml::_('select.option',  '0', '- '. JText::_('No User') .' -');
			try {
				$users = array_merge($users, $db->loadObjectList());
			} catch(JException $e) {
				//ignore the error here
			}
		} else {
			$users = $db->loadObjectList();
		}

		$users = JHtml::_(
			'select.genericlist',
			$users,
			$name,
			array('list.attr' => 'class="inputbox" size="1" '. $javascript, 'list.select' => $active)
		);
		return $users;
	}

}
