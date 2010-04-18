<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
	 * @var	array	A cached array of the asset groups
	 */
	protected static $asset_groups = null;

	/**
	 * Displays a list of the available access view levels
	 *
	 * @param	string	The form field name.
	 * @param	string	The name of the selected section.
	 * @param	string	Additional attributes to add to the select field.
	 * @param	mixed	True to add "All Sections" option or and array of option
	 * @param	string	The form field id
	 *
	 * @return	string	The required HTML for the SELECT tag.
	 */
	public static function level($name, $selected, $attribs = '', $params = true, $id = false)
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('a.id AS value, a.title AS text');
		$query->from('#__viewlevels AS a');
		$query->group('a.id');
		$query->order('a.ordering ASC');
		$query->order('`title` ASC');

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
			return null;
		}

		// If params is an array, push these options to the array
		if (is_array($params)) {
			$options = array_merge($params,$options);
		}
		// If all levels is allowed, push it into the array.
		elseif ($params) {
			array_unshift($options, JHtml::_('select.option', '', JText::_('JOPTION_ACCESS_SHOW_ALL_LEVELS')));
		}

		return JHtml::_('select.genericlist', $options, $name,
			array(
				'list.attr' => $attribs,
				'list.select' => $selected,
				'id' => $id
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
			' LEFT JOIN `#__usergroups` AS b ON a.lft > b.lft AND a.rgt < b.rgt' .
			' GROUP BY a.id' .
			' ORDER BY a.lft ASC'
		);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return null;
		}

		for ($i=0,$n=count($options); $i < $n; $i++) {
			$options[$i]->text = str_repeat('- ',$options[$i]->level).$options[$i]->text;
		}

		// If all usergroups is allowed, push it into the array.
		if ($allowAll) {
			array_unshift($options, JHtml::_('select.option', '', JText::_('JOPTION_ACCESS_SHOW_ALL_GROUPS')));
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
			' LEFT JOIN `#__usergroups` AS b ON a.lft > b.lft AND a.rgt < b.rgt' .
			' GROUP BY a.id' .
			' ORDER BY a.lft ASC'
		);
		$groups = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return null;
		}

		$html = array();

		$html[] = '<ul class="checklist usergroups">';

		for ($i=0, $n=count($groups); $i < $n; $i++) {
			$item = &$groups[$i];

			// Setup  the variable attributes.
			$eid = $count.'group_'.$item->id;
			// don't call in_array unless something is selected
			$checked = '';
			if ($selected) {
				$checked = in_array($item->id, $selected) ? ' checked="checked"' : '';
			}
			$rel = ($item->parent_id > 0) ? ' rel="'.$count.'group_'.$item->parent_id.'"' : '';

			// Build the HTML for the item.
			$html[] = '	<li>';
			$html[] = '		<input type="checkbox" name="'.$name.'[]" value="'.$item->id.'" id="'.$eid.'"';
			$html[] = '				'.$checked.$rel.' />';
			$html[] = '		<label for="'.$eid.'">';
			$html[] = '		'.str_repeat('<span class="gi">|&mdash;</span>', $item->level).$item->title;
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
	public static function actions($name, $selected, $component, $section = 'global')
	{
		static $count;

		$count++;

		$actions	= JAccess::getActions($component, $section);

		$html		= array();
		$html[]		= '<ul class="checklist access-actions">';

		for ($i=0, $n=count($actions); $i < $n; $i++) {
			$item = &$actions[$i];

			// Setup  the variable attributes.
			$eid = $count.'action_'.$item->id;
			$checked = in_array($item->id, $selected) ? ' checked="checked"' : '';

			// Build the HTML for the item.
			$html[] = '	<li>';
			$html[] = '		<input type="checkbox" name="'.$name.'[]" value="'.$item->id.'" id="'.$eid.'"';
			$html[] = '			'.$checked.' />';
			$html[] = '		<label for="'.$eid.'">';
			$html[] = '			'.JText::_($item->title);
			$html[] = '		</label>';
			$html[] = '	</li>';
		}
		$html[] = '</ul>';

		return implode("\n", $html);
	}

	/**
	 * Gets a list of the asset groups as an array of JHtml compatible options.
	 *
	 * @param	array $config	An array of options for the options
	 *
	 * @return	mixed			An array or false if an error occurs
	 */
	public static function assetgroups($config = array())
	{
		if (empty(JHtmlAccess::$asset_groups)) {
			$db		= &JFactory::getDbo();
			$query	= $db->getQuery(true);

			$query->select('a.id AS value, a.title AS text');
			$query->from('#__viewlevels AS a');
			$query->group('a.id');
			$query->order('a.ordering ASC');

			$db->setQuery($query);
			JHtmlAccess::$asset_groups = $db->loadObjectList();

			// Check for a database error.
			if ($db->getErrorNum()) {
				JError::raiseNotice(500, $db->getErrorMsg());
				return false;
			}
		}

		return JHtmlAccess::$asset_groups;
	}

	/**
	 * Displays a Select list of the available asset groups
	 *
	 * @param	string $name	The name of the select element
	 * @param	mixed $selected	The selected asset group id
	 * @param	string $attribs	Optional attributes for the select field
	 * @param	array $config	An array of options for the control
	 *
	 * @return	mixed			An HTML string or null if an error occurs
	 */
	public static function assetgrouplist($name, $selected, $attribs = null, $config = array())
	{
		static $count;

		$options = JHtmlAccess::assetgroups();
		if (isset($config['title'])) {
			array_unshift($options, JHtml::_('select.option', '', $config['title']));
		}

		return JHtml::_(
			'select.genericlist',
			$options,
			$name,
			array(
				'id' =>				isset($config['id']) ? $config['id'] : 'assetgroups_'.++$count,
				'list.attr' =>		(is_null($attribs) ? 'class="inputbox" size="3"' : $attribs),
				'list.select' =>	(int) $selected,
				'list.translate' => true
			)
		);
	}
}
