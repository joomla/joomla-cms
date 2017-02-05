<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Extended Utility class for all HTML drawing classes.
 *
 * @since  1.6
 */
abstract class JHtmlAccess
{
	/**
	 * A cached array of the asset groups
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected static $asset_groups = null;

	/**
	 * Displays a list of the available access view levels
	 *
	 * @param   string  $name      The form field name.
	 * @param   string  $selected  The name of the selected section.
	 * @param   string  $attribs   Additional attributes to add to the select field.
	 * @param   mixed   $params    True to add "All Sections" option or an array of options
	 * @param   mixed   $id        The form field id or false if not used
	 *
	 * @return  string  The required HTML for the SELECT tag.
	 *
	 * @see    JFormFieldAccessLevel
	 * @since  1.6
	 */
	public static function level($name, $selected, $attribs = '', $params = true, $id = false)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('a.id', 'value') . ', ' . $db->quoteName('a.title', 'text'))
			->from($db->quoteName('#__viewlevels', 'a'))
			->group($db->quoteName(array('a.id', 'a.title', 'a.ordering')))
			->order($db->quoteName('a.ordering') . ' ASC')
			->order($db->quoteName('title') . ' ASC');

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();

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
				'id' => $id,
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
	 * @param   mixed    $id        The form field id
	 *
	 * @return  string   The required HTML for the SELECT tag.
	 *
	 * @see     JFormFieldUsergroup
	 * @since   1.6
	 */
	public static function usergroup($name, $selected, $attribs = '', $allowAll = true, $id = false)
	{
		$options = array_values(JHelperUsergroups::getInstance()->getAll());

		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			$options[$i]->value = $options[$i]->id;
			$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->title;
		}

		// If all usergroups is allowed, push it into the array.
		if ($allowAll)
		{
			array_unshift($options, JHtml::_('select.option', '', JText::_('JOPTION_ACCESS_SHOW_ALL_GROUPS')));
		}

		return JHtml::_('select.genericlist', $options, $name, array('list.attr' => $attribs, 'list.select' => $selected, 'id' => $id));
	}

	/**
	 * Returns a UL list of user groups with checkboxes
	 *
	 * @param   string   $name             The name of the checkbox controls array
	 * @param   array    $selected         An array of the checked boxes
	 * @param   boolean  $checkSuperAdmin  If false only super admins can add to super admin groups
	 *
	 * @return  string
	 *
	 * @since   1.6
	 */
	public static function usergroups($name, $selected, $checkSuperAdmin = false)
	{
		static $count;

		$count++;

		$isSuperAdmin = JFactory::getUser()->authorise('core.admin');

		$groups = array_values(JHelperUsergroups::getInstance()->getAll());

		$html = array();

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
				$html[] = '	<div class="control-group">';
				$html[] = '		<div class="controls">';
				$html[] = '			<label class="checkbox" for="' . $eid . '">';
				$html[] = '			<input type="checkbox" name="' . $name . '[]" value="' . $item->id . '" id="' . $eid . '"';
				$html[] = '					' . $checked . $rel . ' />';
				$html[] = '			' . JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level + 1)) . $item->title;
				$html[] = '			</label>';
				$html[] = '		</div>';
				$html[] = '	</div>';
			}
		}

		return implode("\n", $html);
	}

	/**
	 * Returns a UL list of actions with checkboxes
	 *
	 * @param   string  $name       The name of the checkbox controls array
	 * @param   array   $selected   An array of the checked boxes
	 * @param   string  $component  The component the permissions apply to
	 * @param   string  $section    The section (within a component) the permissions apply to
	 *
	 * @return  string
	 *
	 * @see     JAccess
	 * @since   1.6
	 */
	public static function actions($name, $selected, $component, $section = 'global')
	{
		static $count;

		$count++;

		$actions = JAccess::getActionsFromFile(
			JPATH_ADMINISTRATOR . '/components/' . $component . '/access.xml',
			"/access/section[@name='" . $section . "']/"
		);

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
	 * @return  mixed  An array or false if an error occurs
	 *
	 * @since   1.6
	 */
	public static function assetgroups()
	{
		if (empty(static::$asset_groups))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.id AS value, a.title AS text')
				->from($db->quoteName('#__viewlevels') . ' AS a')
				->group('a.id, a.title, a.ordering')
				->order('a.ordering ASC');

			$db->setQuery($query);
			static::$asset_groups = $db->loadObjectList();
		}

		return static::$asset_groups;
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
	 * @since   1.6
	 */
	public static function assetgrouplist($name, $selected, $attribs = null, $config = array())
	{
		static $count;

		$options = static::assetgroups();

		if (isset($config['title']))
		{
			array_unshift($options, JHtml::_('select.option', '', $config['title']));
		}

		return JHtml::_(
			'select.genericlist',
			$options,
			$name,
			array(
				'id' => isset($config['id']) ? $config['id'] : 'assetgroups_' . (++$count),
				'list.attr' => is_null($attribs) ? 'class="inputbox" size="3"' : $attribs,
				'list.select' => (int) $selected,
			)
		);
	}
}
