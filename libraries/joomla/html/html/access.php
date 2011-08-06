<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Extended Utility class for all HTML drawing classes.
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.1
 */
abstract class JHtmlAccess
{
	/**
	 * A cached array of the asset groups
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $asset_groups = null;

	/**
	 * Displays a list of the available access view levels
	 *
	 * @param   string  $name      The form field name.
	 * @param   string  $selected  The name of the selected section.
	 * @param   string  $attribs   Additional attributes to add to the select field.
	 * @param   mixed   $params    True to add "All Sections" option or and array of options
	 * @param   string  $id        The form field id
	 *
	 * @return  string  The required HTML for the SELECT tag.
	 *
	 * @since  11.1
	 *
	 * @see    JFormFieldAccessLevel
	 */
	public static function level($name, $selected, $attribs = '', $params = true, $id = false)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id AS value, a.title AS text');
		$query->from('#__viewlevels AS a');
		$query->group('a.id');
		$query->order('a.ordering ASC');
		$query->order($query->qn('title') . ' ASC');

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			JError::raiseWarning(500, $db->getErrorMsg());
			return null;
		}

		// If params is an array, push these options to the array
		if (is_array($params))
		{
			$options = array_merge($params, $options);
		}
		// If all levels is allowed, push it into the array.
		elseif ($params)
		{
			array_unshift($options, JHtml::_('select.option', '', JText::_('JOPTION_ACCESS_SHOW_ALL_LEVELS')));
		}

		return JHtml::_(
			'select.genericlist',
			$options,
			$name,
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
	 * @param   string   $name      The form field name.
	 * @param   string   $selected  The name of the selected section.
	 * @param   string   $attribs   Additional attributes to add to the select field.
	 * @param   boolean  $allowAll  True to add "All Groups" option.
	 *
	 * @return  string   The required HTML for the SELECT tag.
	 *
	 * @see     JFormFieldUsergroup
	 *
	 * @since   11.1
	 */
	public static function usergroup($name, $selected, $attribs = '', $allowAll = true)
	{
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level' . ' FROM #__usergroups AS a'
			. ' LEFT JOIN #__usergroups AS b ON a.lft > b.lft AND a.rgt < b.rgt' . ' GROUP BY a.id' . ' ORDER BY a.lft ASC'
		);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			JError::raiseNotice(500, $db->getErrorMsg());
			return null;
		}

		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
		}

		// If all usergroups is allowed, push it into the array.
		if ($allowAll)
		{
			array_unshift($options, JHtml::_('select.option', '', JText::_('JOPTION_ACCESS_SHOW_ALL_GROUPS')));
		}

		return JHtml::_('select.genericlist', $options, $name, array('list.attr' => $attribs, 'list.select' => $selected));
	}

	/**
	 * Returns a UL list of user groups with check boxes
	 *
	 * @param   string   $name             The name of the checkbox controls array
	 * @param   array    $selected         An array of the checked boxes
	 * @param   boolean  $checkSuperAdmin  If false only super admins can add to super admin groups
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public static function usergroups($name, $selected, $checkSuperAdmin = false)
	{
		static $count;

		$count++;

		$isSuperAdmin = JFactory::getUser()->authorise('core.admin');

		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT a.*, COUNT(DISTINCT b.id) AS level' . ' FROM #__usergroups AS a'
			. ' LEFT JOIN #__usergroups AS b ON a.lft > b.lft AND a.rgt < b.rgt' . ' GROUP BY a.id' . ' ORDER BY a.lft ASC'
		);
		$groups = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			JError::raiseNotice(500, $db->getErrorMsg());
			return null;
		}

		$html = array();

		$html[] = '<ul class="checklist usergroups">';

		for ($i = 0, $n = count($groups); $i < $n; $i++)
		{
			$item = &$groups[$i];

			// If checkSuperAdmin is true, only add item if the user is superadmin or the group is not super admin
			if ((!$checkSuperAdmin) || $isSuperAdmin || (!JAccess::checkGroup($item->id, 'core.admin')))
			{
				// Setup  the variable attributes.
				$eid = $count . 'group_' . $item->id;
				// Don't call in_array unless something is selected
				$checked = '';
				if ($selected)
				{
					$checked = in_array($item->id, $selected) ? ' checked="checked"' : '';
				}
				$rel = ($item->parent_id > 0) ? ' rel="' . $count . 'group_' . $item->parent_id . '"' : '';

				// Build the HTML for the item.
				$html[] = '	<li>';
				$html[] = '		<input type="checkbox" name="' . $name . '[]" value="' . $item->id . '" id="' . $eid . '"';
				$html[] = '				' . $checked . $rel . ' />';
				$html[] = '		<label for="' . $eid . '">';
				$html[] = '		' . str_repeat('<span class="gi">|&mdash;</span>', $item->level) . $item->title;
				$html[] = '		</label>';
				$html[] = '	</li>';
			}
		}
		$html[] = '</ul>';

		return implode("\n", $html);
	}

	/**
	 * Returns a UL list of actions with check boxes
	 *
	 * @param   string  $name       The name of the checkbox controls array
	 * @param   array   $selected   An array of the checked boxes
	 * @param   string  $component  The component the permissions apply to
	 * @param   string  $section    The section (within a component) the permissions apply to
	 *
	 * @return  string
	 *
	 * @see     JAccess
	 * @since   11.1
	 */
	public static function actions($name, $selected, $component, $section = 'global')
	{
		static $count;

		$count++;

		$actions = JAccess::getActions($component, $section);

		$html = array();
		$html[] = '<ul class="checklist access-actions">';

		for ($i = 0, $n = count($actions); $i < $n; $i++)
		{
			$item = &$actions[$i];

			// Setup  the variable attributes.
			$eid = $count . 'action_' . $item->id;
			$checked = in_array($item->id, $selected) ? ' checked="checked"' : '';

			// Build the HTML for the item.
			$html[] = '	<li>';
			$html[] = '		<input type="checkbox" name="' . $name . '[]" value="' . $item->id . '" id="' . $eid . '"';
			$html[] = '			' . $checked . ' />';
			$html[] = '		<label for="' . $eid . '">';
			$html[] = '			' . JText::_($item->title);
			$html[] = '		</label>';
			$html[] = '	</li>';
		}
		$html[] = '</ul>';

		return implode("\n", $html);
	}

	/**
	 * Gets a list of the asset groups as an array of JHtml compatible options.
	 *
	 * @param   array  $config  An array of options for the options
	 *
	 * @return  mixed  An array or false if an error occurs
	 *
	 * @since   11.1
	 */
	public static function assetgroups($config = array())
	{
		if (empty(JHtmlAccess::$asset_groups))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.id AS value, a.title AS text');
			$query->from('#__viewlevels AS a');
			$query->group('a.id');
			$query->order('a.ordering ASC');

			$db->setQuery($query);
			JHtmlAccess::$asset_groups = $db->loadObjectList();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				JError::raiseNotice(500, $db->getErrorMsg());
				return false;
			}
		}

		return JHtmlAccess::$asset_groups;
	}

	/**
	 * Displays a Select list of the available asset groups
	 *
	 * @param   string  $name      The name of the select element
	 * @param   mixed   $selected  The selected asset group id
	 * @param   string  $attribs   Optional attributes for the select field
	 * @param   array   $config    An array of options for the control
	 *
	 * @return  mixed  An HTML string or null if an error occurs
	 *
	 * @since   11.1
	 */
	public static function assetgrouplist($name, $selected, $attribs = null, $config = array())
	{
		static $count;

		$options = JHtmlAccess::assetgroups();
		if (isset($config['title']))
		{
			array_unshift($options, JHtml::_('select.option', '', $config['title']));
		}

		return JHtml::_(
			'select.genericlist',
			$options,
			$name,
			array(
				'id' => isset($config['id']) ? $config['id'] : 'assetgroups_' . ++$count,
				'list.attr' => (is_null($attribs) ? 'class="inputbox" size="3"' : $attribs),
				'list.select' => (int) $selected
			)
		);
	}
}
