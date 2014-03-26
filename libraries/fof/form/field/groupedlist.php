<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

if (!class_exists('JFormFieldGroupedList'))
{
	require_once JPATH_LIBRARIES . '/joomla/form/fields/groupedlist.php';
}

/**
 * Form Field class for FOF
 * Supports a generic list of options.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormFieldGroupedlist extends JFormFieldGroupedList implements FOFFormField
{
	protected $static;

	protected $repeatable;
	
	/** @var   FOFTable  The item being rendered in a repeatable form field */
	public $item;
	
	/** @var int A monotonically increasing number, denoting the row number in a repeatable view */
	public $rowid;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   2.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'static':
				if (empty($this->static))
				{
					$this->static = $this->getStatic();
				}

				return $this->static;
				break;

			case 'repeatable':
				if (empty($this->repeatable))
				{
					$this->repeatable = $this->getRepeatable();
				}

				return $this->static;
				break;

			default:
				return parent::__get($name);
		}
	}

	/**
	 * Get the rendering of this field type for static display, e.g. in a single
	 * item view (typically a "read" task).
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getStatic()
	{
		$class = $this->element['class'] ? (string) $this->element['class'] : '';

		$selected = self::getOptionName($this->getGroups(), $this->value);

		if (is_null($selected))
		{
			$selected = array(
				'group'	 => '',
				'item'	 => ''
			);
		}

		return '<span id="' . $this->id . '-group" class="fof-groupedlist-group ' . $class . '>' .
			htmlspecialchars($selected['group'], ENT_COMPAT, 'UTF-8') .
			'</span>' .
			'<span id="' . $this->id . '-item" class="fof-groupedlist-item ' . $class . '>' .
			htmlspecialchars($selected['item'], ENT_COMPAT, 'UTF-8') .
			'</span>';
	}

	/**
	 * Get the rendering of this field type for a repeatable (grid) display,
	 * e.g. in a view listing many item (typically a "browse" task)
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getRepeatable()
	{
		$class = $this->element['class'] ? (string) $this->element['class'] : '';

		$selected = self::getOptionName($this->getGroups(), $this->value);

		if (is_null($selected))
		{
			$selected = array(
				'group'	 => '',
				'item'	 => ''
			);
		}

		return '<span class="' . $this->id . '-group fof-groupedlist-group ' . $class . '">' .
			htmlspecialchars($selected['group'], ENT_COMPAT, 'UTF-8') .
			'</span>' .
			'<span class="' . $this->id . '-item fof-groupedlist-item ' . $class . '">' .
			htmlspecialchars($selected['item'], ENT_COMPAT, 'UTF-8') .
			'</span>';
	}

	/**
	 * Gets the active option's label given an array of JHtml options
	 *
	 * @param   array   $data      The JHtml options to parse
	 * @param   mixed   $selected  The currently selected value
	 * @param   string  $groupKey  Group name
	 * @param   string  $optKey    Key name
	 * @param   string  $optText   Value name
	 *
	 * @return  mixed   The label of the currently selected option
	 */
	public static function getOptionName($data, $selected = null, $groupKey = 'items', $optKey = 'value', $optText = 'text')
	{
		$ret = null;

		foreach ($data as $dataKey => $group)
		{
			$label = $dataKey;
			$noGroup = is_int($dataKey);

			if (is_array($group))
			{
				$subList = $group[$groupKey];
				$label = $group[$optText];
				$noGroup = false;
			}
			elseif (is_object($group))
			{
				// Sub-list is in a property of an object
				$subList = $group->$groupKey;
				$label = $group->$optText;
				$noGroup = false;
			}
			else
			{
				throw new RuntimeException('Invalid group contents.', 1);
			}

			if ($noGroup)
			{
				$label = '';
			}

			$match = FOFFormFieldList::getOptionName($data, $selected, $optKey, $optText);

			if (!is_null($match))
			{
				$ret = array(
					'group'	 => $label,
					'item'	 => $match
				);
				break;
			}
		}

		return $ret;
	}
}
