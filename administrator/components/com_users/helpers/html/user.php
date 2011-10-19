<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	com_user
 */
abstract class JHtmlUser
{
	/**
	 * Displays a list of user groups.
	 *
	 * @return  array   An array containing a list of user groups.
	 *
	 * @see     JFormFieldUsergroup
	 *
	 * @since   1.7
	 */
	public static function groups()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level');
		$query->from($db->quoteName('#__usergroups').' AS a');
		$query->join('LEFT', $db->quoteName('#__usergroups').' AS b ON a.lft > b.lft AND a.rgt < b.rgt');
		$query->group('a.id');
		$query->order('a.lft ASC');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return null;
		}

		for ($i = 0, $n = count($options); $i < $n; $i++) {
			$options[$i]->text = str_repeat('- ', $options[$i]->level).$options[$i]->text;
			$groups[] = JHtml::_('select.option', $options[$i]->value, $options[$i]->text);
		}

		return $groups;
	}
}
