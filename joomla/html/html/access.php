<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Extended Utility class for all HTML drawing classes.
 *
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @static
 * @since		1.6
 */
abstract class JHtmlAccess
{
	/**
	 * Displays a list of the available access sections
	 *
	 * @param	string	The form field name.
	 * @param	string	The name of the selected section.
	 * @param	string	Additional attributes to add to the select field.
	 * @param	boolean	True to add "All Sections" option.
	 *
	 * @return	string	The required HTML for the SELECT tag.
	 */
	public static function section($name, $selected, $attribs = '', $allowAll = true)
	{
		$db = &JFactory::getDbo();
		$db->setQuery(
			'SELECT `id` AS value, `title` AS text'
			.' FROM #__access_sections'
			.' ORDER BY `ordering`, `title`'
		);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return null;
		}

		// If all usergroups is allowed, push it into the array.
		if ($allowAll) {
			array_unshift($options, JHtml::_('select.option', '', JText::_('JOption_Access_Show_All_Sections')));
		}

		return JHtml::_('select.genericlist', $options, $name,
			array(
				'list.attr' => $attribs,
				'list.select' => $selected
			)
		);
	}

	/**
	 * Displays a list of the available user groups.
	 *
	 * @param	string	The form field name.
	 * @param	string	The name of the selected section.
	 * @param	string	Additional attributes to add to the select field.
	 * @param	boolean	True to add "All Groups" option.
	 * @return	string	The required HTML for the SELECT tag.
	 */
	public static function usergroup($name, $selected, $attribs = '', $allowAll = true)
	{
		$db = &JFactory::getDbo();
		$db->setQuery(
			'SELECT a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level' .
			' FROM #__usergroups AS a' .
			' LEFT JOIN `#__usergroups` AS b ON a.left_id > b.left_id AND a.right_id < b.right_id' .
			' GROUP BY a.id' .
			' ORDER BY a.left_id ASC'
		);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return null;
		}

		for ($i=0,$n=count($options); $i < $n; $i++)
		{
			$options[$i]->text = str_repeat('- ',$options[$i]->level).$options[$i]->text;
		}

		// If all usergroups is allowed, push it into the array.
		if ($allowAll) {
			array_unshift($options, JHtml::_('select.option', '', JText::_('JOption_Access_Show_All_Groups')));
		}

		return JHtml::_('select.genericlist', $options, $name,
			array(
				'list.attr' => $attribs,
				'list.select' => $selected
			)
		);
	}

	/**
	 * Returns a UL list of user groups with check boxes
	 *
	 * @param	string $name	The name of the checkbox controls array
	 * @param	array $selected	An array of the checked boxes
	 *
	 * @return	string
	 */
	public static function usergroups($name, $selected)
	{
		static $count;

		$count++;

		$db = &JFactory::getDbo();
		$db->setQuery(
			'SELECT a.*, COUNT(DISTINCT b.id) AS level' .
			' FROM #__usergroups AS a' .
			' LEFT JOIN `#__usergroups` AS b ON a.left_id > b.left_id AND a.right_id < b.right_id' .
			' GROUP BY a.id' .
			' ORDER BY a.left_id ASC'
		);
		$groups = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return null;
		}

		$html = array();

		$html[] = '<ul class="checklist usergroups" style="padding:0;">';

		for ($i=0, $n=count($groups); $i < $n; $i++)
		{
			$item = &$groups[$i];

			// Setup  the variable attributes.
			$eid = $count.'group_'.$item->id;
			$checked = in_array($item->id, $selected) ? ' checked="checked"' : '';
			$rel = ($item->parent_id > 0) ? ' rel="'.$count.'group_'.$item->parent_id.'"' : '';

			// Build the HTML for the item.
			$html[] = '	<li>';
			$html[] = '		<label for="'.$eid.'">';
			$html[] = '			<input type="checkbox" name="'.$name.'[]" value="'.$item->id.'" id="'.$eid.'"';
			$html[] = '				'.$checked.$rel.' />';
			$html[] = '			'.str_repeat('- ', $item->level).$item->title;
			$html[] = '		</label>';
			$html[] = '	</li>';
		}
		$html[] = '</ul>';

		return implode("\n", $html);
	}

	/**
	 * Returns a UL list of user groups with check boxes
	 *
	 * @param	string $name	The name of the checkbox controls array
	 * @param	array $selected	An array of the checked boxes
	 *
	 * @return	string
	 */
	public static function actions($name, $selected, $section = 'core', $type = 1)
	{
		static $count;

		$count++;

		jimport('joomla.access.helper');
		$actions	= JAccessHelper::getActions($section, $type);

		$html		= array();
		$html[]		= '<ul class="checklist access-actions" style="padding:0;">';

		for ($i=0, $n=count($actions); $i < $n; $i++)
		{
			$item = &$actions[$i];

			// Setup  the variable attributes.
			$eid = $count.'action_'.$item->id;
			$checked = in_array($item->id, $selected) ? ' checked="checked"' : '';

			// Build the HTML for the item.
			$html[] = '	<li>';
			$html[] = '		<label for="'.$eid.'">';
			$html[] = '			<input type="checkbox" name="'.$name.'[]" value="'.$item->id.'" id="'.$eid.'"';
			$html[] = '				'.$checked.' />';
			$html[] = '			'.JText::_($item->title);
			$html[] = '		</label>';
			$html[] = '	</li>';
		}
		$html[] = '</ul>';

		return implode("\n", $html);
	}

	/**
	 * Displays a Select list of the available asset groups
	 *
	 * @param	string $name	The name of the select element
	 * @param	mixed $selected	The selected asset group id
	 * @param	string $attribs	Optional attributes for the select field
	 *
	 * @return	mixed			An HTML string or null if an error occurs
	 */
	public static function assetgroups($name, $selected, $attribs = null)
	{
		static $count, $cache;

		$count++;

		if ($cache == null)
		{
			$db = &JFactory::getDbo();
			$db->setQuery(
				'SELECT a.*, COUNT(DISTINCT b.id) AS level' .
				' FROM #__access_assetgroups AS a' .
				' LEFT JOIN `#__access_assetgroups` AS b ON a.left_id > b.left_id AND a.right_id < b.right_id' .
				' GROUP BY a.id' .
				' ORDER BY a.left_id ASC'
			);

			$cache = $db->loadObjectList();

			// Check for a database error.
			if ($db->getErrorNum()) {
				JError::raiseNotice(500, $db->getErrorMsg());
				return false;
			}

			foreach ($cache as $i => $group)
			{
				$cache[$i]->value	= $group->id;
				// We are not exposing any hierarchy in access levels yet.
				//$cache[$i]->text	= str_pad($group->title, strlen($group->title) + 2*($group->level), '- ', STR_PAD_LEFT);
				$cache[$i]->text	= $group->title;
			}
		}

		return JHtml::_(
			'select.genericlist',
			$cache,
			$name,
			array(
				'id' =>				'assetgroups_'.$count,
				'list.attr' =>		(is_null($attribs) ? 'class="inputbox" size="3"' : $attribs),
				'list.select' =>	(int) $selected,
				'list.translate' => true
			)
		);
	}
}