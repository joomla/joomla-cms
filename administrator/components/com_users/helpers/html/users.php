<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

/**
 * HTML behavior class
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 */
class JHTMLUsers
{
	public static function groups($name, $attribs, $selected)
	{
		// Get a database object.
		$db = &JFactory::getDbo();

		// Get the user groups from the database.
		$db->setQuery(
			'SELECT a.id AS value, a.name AS text, COUNT(DISTINCT b.id) AS level' .
			' FROM #__core_acl_aro_groups AS a' .
			' LEFT JOIN `#__core_acl_aro_groups` AS b ON a.lft > b.lft AND a.rgt < b.rgt' .
			' LEFT JOIN `#__core_acl_aro_groups` AS p ON p.id = 28' .
			' WHERE a.lft > p.lft AND a.rgt < p.rgt' .
			' GROUP BY a.id' .
			' ORDER BY a.lft ASC'
		);
		$options = $db->loadObjectList();

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i=0,$n=count($options); $i < $n; $i++)
		{
			$options[$i]->text = str_repeat('&nbsp;&nbsp;',($options[$i]->level - 2)).$options[$i]->text;
		}

		array_unshift($options, JHtml::_('select.option', 0, 'Select Group'));

		return JHtml::_('select.genericlist', $options, $name, 'class="inputbox" '.$attribs, 'value', 'text', $selected, $name);
	}
}