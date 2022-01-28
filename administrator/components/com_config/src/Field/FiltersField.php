<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * Text Filters form field.
 *
 * @since  1.6
 */
class FiltersField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Filters';

	/**
	 * Method to get the field input markup.
	 *
	 * @todo: Add access check.
	 *
	 * @return	string	The field input markup.
	 *
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Add translation string for notification
		Text::script('COM_CONFIG_TEXT_FILTERS_NOTE');

		// Add Javascript
		Factory::getDocument()->getWebAssetManager()->useScript('com_config.filters');

		// Get the available user groups.
		$groups = $this->getUserGroups();

		// Build the form control.
		$html = array();

		// Open the table.
		$html[] = '<table id="filter-config" class="table">';

		// The table heading.
		$html[] = '	<thead>';
		$html[] = '	<tr>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action">' . Text::_('JGLOBAL_FILTER_GROUPS_LABEL') . '</span>';
		$html[] = '		</th>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action">' . Text::_('JGLOBAL_FILTER_TYPE_LABEL') . '</span>';
		$html[] = '		</th>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action">' . Text::_('JGLOBAL_FILTER_TAGS_LABEL') . '</span>';
		$html[] = '		</th>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action">' . Text::_('JGLOBAL_FILTER_ATTRIBUTES_LABEL') . '</span>';
		$html[] = '		</th>';
		$html[] = '	</tr>';
		$html[] = '	</thead>';

		// The table body.
		$html[] = '	<tbody>';

		foreach ($groups as $group)
		{
			if (!isset($this->value[$group->value]))
			{
				$this->value[$group->value] = array('filter_type' => 'BL', 'filter_tags' => '', 'filter_attributes' => '');
			}

			$group_filter = $this->value[$group->value];

			$group_filter['filter_tags']       = !empty($group_filter['filter_tags']) ? $group_filter['filter_tags'] : '';
			$group_filter['filter_attributes'] = !empty($group_filter['filter_attributes']) ? $group_filter['filter_attributes'] : '';

			$html[] = '	<tr>';
			$html[] = '		<td class="acl-groups left">';
			$html[] = '			' . LayoutHelper::render('joomla.html.treeprefix', array('level' => $group->level + 1)) . $group->text;
			$html[] = '		</td>';
			$html[] = '		<td>';
			$html[] = '			<label for="' . $this->id . $group->value . '_filter_type" class="visually-hidden">'
				. Text::_('JGLOBAL_FILTER_TYPE_LABEL') . '</label>';
			$html[] = '				<select'
				. ' name="' . $this->name . '[' . $group->value . '][filter_type]"'
				. ' id="' . $this->id . $group->value . '_filter_type"'
				. ' data-parent="' . ($group->parent) . '" '
				. ' data-id="' . ($group->value) . '" '
				. ' class="novalidate form-select"'
				. '>';
			$html[] = '					<option value="BL"' . ($group_filter['filter_type'] == 'BL' ? ' selected="selected"' : '') . '>'
				. Text::_('COM_CONFIG_FIELD_FILTERS_DEFAULT_FORBIDDEN_LIST') . '</option>';
			$html[] = '					<option value="CBL"' . ($group_filter['filter_type'] == 'CBL' ? ' selected="selected"' : '') . '>'
				. Text::_('COM_CONFIG_FIELD_FILTERS_CUSTOM_FORBIDDEN_LIST') . '</option>';
			$html[] = '					<option value="WL"' . ($group_filter['filter_type'] == 'WL' ? ' selected="selected"' : '') . '>'
				. Text::_('COM_CONFIG_FIELD_FILTERS_ALLOWED_LIST') . '</option>';
			$html[] = '					<option value="NH"' . ($group_filter['filter_type'] == 'NH' ? ' selected="selected"' : '') . '>'
				. Text::_('COM_CONFIG_FIELD_FILTERS_NO_HTML') . '</option>';
			$html[] = '					<option value="NONE"' . ($group_filter['filter_type'] == 'NONE' ? ' selected="selected"' : '') . '>'
				. Text::_('COM_CONFIG_FIELD_FILTERS_NO_FILTER') . '</option>';
			$html[] = '				</select>';
			$html[] = '		</td>';
			$html[] = '		<td>';
			$html[] = '			<label for="' . $this->id . $group->value . '_filter_tags" class="visually-hidden">'
				. Text::_('JGLOBAL_FILTER_TAGS_LABEL') . '</label>';
			$html[] = '				<input'
				. ' name="' . $this->name . '[' . $group->value . '][filter_tags]"'
				. ' type="text"'
				. ' id="' . $this->id . $group->value . '_filter_tags" class="novalidate form-control"'
				. ' value="' . htmlspecialchars($group_filter['filter_tags'], ENT_QUOTES) . '"'
				. '>';
			$html[] = '		</td>';
			$html[] = '		<td>';
			$html[] = '			<label for="' . $this->id . $group->value . '_filter_attributes"'
				. ' class="visually-hidden">' . Text::_('JGLOBAL_FILTER_ATTRIBUTES_LABEL') . '</label>';
			$html[] = '				<input'
				. ' name="' . $this->name . '[' . $group->value . '][filter_attributes]"'
				. ' type="text"'
				. ' id="' . $this->id . $group->value . '_filter_attributes" class="novalidate form-control"'
				. ' value="' . htmlspecialchars($group_filter['filter_attributes'], ENT_QUOTES) . '"'
				. '>';
			$html[] = '		</td>';
			$html[] = '	</tr>';
		}

		$html[] = '	</tbody>';

		// Close the table.
		$html[] = '</table>';

		return implode("\n", $html);
	}

	/**
	 * A helper to get the list of user groups.
	 *
	 * @return	array
	 *
	 * @since	1.6
	 */
	protected function getUserGroups()
	{
		// Get a database object.
		$db = Factory::getDbo();

		// Get the user groups from the database.
		$query = $db->getQuery(true);
		$query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id as parent');
		$query->from('#__usergroups AS a');
		$query->join('LEFT', '#__usergroups AS b on a.lft > b.lft AND a.rgt < b.rgt');
		$query->group('a.id, a.title, a.lft');
		$query->order('a.lft ASC');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return $options;
	}
}
