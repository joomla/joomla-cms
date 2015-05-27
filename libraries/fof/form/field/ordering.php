<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * Form Field class for FOF
 * Renders the row ordering interface checkbox in browse views
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormFieldOrdering extends JFormField implements FOFFormField
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

				return $this->repeatable;
				break;

			default:
				return parent::__get($name);
		}
	}

	/**
	 * Method to get the field input markup for this field type.
	 *
	 * @since 2.0
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= $this->disabled ? ' disabled' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';

		$this->item = $this->form->getModel()->getItem();

		$keyfield = $this->item->getKeyName();
		$itemId   = $this->item->$keyfield;

		$query = $this->getQuery();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ($this->readonly)
		{
			$html[] = JHtml::_('list.ordering', '', $query, trim($attr), $this->value, $itemId ? 0 : 1);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		}
		else
		{
			// Create a regular list.
			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, $itemId ? 0 : 1);
		}

		return implode($html);
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
		throw new Exception(__CLASS__ . ' cannot be used in single item display forms');
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
		if (!($this->item instanceof FOFTable))
		{
			throw new Exception(__CLASS__ . ' needs a FOFTable to act upon');
		}

		$class = isset($this->element['class']) ? $this->element['class'] : 'input-mini';
		$icon  = isset($this->element['icon']) ? $this->element['icon'] : 'icon-menu';

		$html = '';

		$view = $this->form->getView();

		$ordering = $view->getLists()->order == 'ordering';

		if (!$view->hasAjaxOrderingSupport())
		{
			// Ye olde Joomla! 2.5 method
			$disabled = $ordering ? '' : 'disabled="disabled"';
			$html .= '<span>';
			$html .= $view->pagination->orderUpIcon($this->rowid, true, 'orderup', 'Move Up', $ordering);
			$html .= '</span><span>';
			$html .= $view->pagination->orderDownIcon($this->rowid, $view->pagination->total, true, 'orderdown', 'Move Down', $ordering);
			$html .= '</span>';
			$html .= '<input type="text" name="order[]" size="5" value="' . $this->value . '" ' . $disabled;
			$html .= 'class="text-area-order" style="text-align: center" />';
		}
		else
		{
			// The modern drag'n'drop method
			if ($view->getPerms()->editstate)
			{
				$disableClassName = '';
				$disabledLabel = '';

				$hasAjaxOrderingSupport = $view->hasAjaxOrderingSupport();

				if (!$hasAjaxOrderingSupport['saveOrder'])
				{
					$disabledLabel = JText::_('JORDERINGDISABLED');
					$disableClassName = 'inactive tip-top';
				}

				$orderClass = $ordering ? 'order-enabled' : 'order-disabled';

				$html .= '<div class="' . $orderClass . '">';
				$html .= '<span class="sortable-handler ' . $disableClassName . '" title="' . $disabledLabel . '" rel="tooltip">';
				$html .= '<i class="' . $icon . '"></i>';
				$html .= '</span>';

				if ($ordering)
				{
					$html .= '<input type="text" name="order[]" size="5" class="' . $class . ' text-area-order" value="' . $this->value . '" />';
				}

				$html .= '</div>';
			}
			else
			{
				$html .= '<span class="sortable-handler inactive" >';
				$html .= '<i class="' . $icon . '"></i>';
				$html .= '</span>';
			}
		}

		return $html;
	}

	/**
	 * Builds the query for the ordering list.
	 *
	 * @since 2.3.2
	 *
	 * @return JDatabaseQuery  The query for the ordering form field
	 */
	protected function getQuery()
	{
		$ordering = $this->name;
		$title    = $this->element['ordertitle'] ? (string) $this->element['ordertitle'] : $this->item->getColumnAlias('title');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array($db->quoteName($ordering, 'value'), $db->quoteName($title, 'text')))
				->from($db->quoteName($this->item->getTableName()))
				->order($ordering);

		return $query;
	}
}
