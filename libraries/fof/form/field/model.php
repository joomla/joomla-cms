<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

if (!class_exists('JFormFieldSql'))
{
	require_once JPATH_LIBRARIES . '/joomla/form/fields/sql.php';
}

/**
 * Form Field class for FOF
 * Generic list from a model's results
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormFieldModel extends FOFFormFieldList implements FOFFormField
{
	protected $static;

	protected $repeatable;

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
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

		return '<span id="' . $this->id . '" ' . $class . '>' .
			htmlspecialchars(FOFFormFieldList::getOptionName($this->getOptions(), $this->value), ENT_COMPAT, 'UTF-8') .
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
		$class				= $this->id;
		$format_string		= '';
		$show_link			= false;
		$link_url			= '';
		$empty_replacement	= '';

		// Get field parameters
		if ($this->element['class'])
		{
			$class = (string) $this->element['class'];
		}

		if ($this->element['format'])
		{
			$format_string = (string) $this->element['format'];
		}

		if ($this->element['show_link'] == 'true')
		{
			$show_link = true;
		}

		if ($this->element['url'])
		{
			$link_url = $this->element['url'];
		}
		else
		{
			$show_link = false;
		}

		if ($show_link && ($this->item instanceof FOFTable))
		{
			// Replace [ITEM:ID] in the URL with the item's key value (usually:
			// the auto-incrementing numeric ID)
			$keyfield = $this->item->getKeyName();
			$replace  = $this->item->$keyfield;
			$link_url = str_replace('[ITEM:ID]', $replace, $link_url);

			// Replace the [ITEMID] in the URL with the current Itemid parameter
			$link_url = str_replace('[ITEMID]', JFactory::getApplication()->input->getInt('Itemid', 0), $link_url);

			// Replace other field variables in the URL
			$fields = $this->item->getFields();

			foreach ($fields as $fielddata)
			{
				$fieldname = $fielddata->Field;

				if (empty($fieldname))
				{
					$fieldname = $fielddata->column_name;
				}

				$search    = '[ITEM:' . strtoupper($fieldname) . ']';
				$replace   = $this->item->$fieldname;
				$link_url  = str_replace($search, $replace, $link_url);
			}
		}
		else
		{
			$show_link = false;
		}

		if ($this->element['empty_replacement'])
		{
			$empty_replacement = (string) $this->element['empty_replacement'];
		}

		$value = FOFFormFieldList::getOptionName($this->getOptions(), $this->value);

		// Get the (optionally formatted) value
		if (!empty($empty_replacement) && empty($value))
		{
			$value = JText::_($empty_replacement);
		}

		if (empty($format_string))
		{
			$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		}
		else
		{
			$value = sprintf($format_string, $value);
		}

		// Create the HTML
		$html = '<span class="' . $class . '">';

		if ($show_link)
		{
			$html .= '<a href="' . $link_url . '">';
		}

		$html .= $value;

		if ($show_link)
		{
			$html .= '</a>';
		}

		$html .= '</span>';

		return $html;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();

		// Initialize some field attributes.
		$key = $this->element['key_field'] ? (string) $this->element['key_field'] : 'value';
		$value = $this->element['value_field'] ? (string) $this->element['value_field'] : (string) $this->element['name'];
		$translate = $this->element['translate'] ? (string) $this->element['translate'] : false;
		$applyAccess = $this->element['apply_access'] ? (string) $this->element['apply_access'] : 'false';
		$modelName = (string) $this->element['model'];
		$nonePlaceholder = (string) $this->element['none'];

		if (!empty($nonePlaceholder))
		{
			$options[] = JHtml::_('select.option', JText::_($nonePlaceholder), null);
		}

		// Process field atrtibutes
		$applyAccess = strtolower($applyAccess);
		$applyAccess = in_array($applyAccess, array('yes', 'on', 'true', '1'));

		// Explode model name into model name and prefix
		$parts = FOFInflector::explode($modelName);
		$mName = ucfirst(array_pop($parts));
		$mPrefix = FOFInflector::implode($parts);

		// Get the model object
		$config = array('savestate' => 0);
		$model = FOFModel::getTmpInstance($mName, $mPrefix, $config);

		if ($applyAccess)
		{
			$model->applyAccessFiltering();
		}

		// Process state variables
		foreach ($this->element->children() as $stateoption)
		{
			// Only add <option /> elements.
			if ($stateoption->getName() != 'state')
			{
				continue;
			}

			$stateKey = (string) $stateoption['key'];
			$stateValue = (string) $stateoption;

			$model->setState($stateKey, $stateValue);
		}

		// Set the query and get the result list.
		$items = $model->getItemList(true);

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if ($translate == true)
				{
					$options[] = JHtml::_('select.option', $item->$key, JText::_($item->$value));
				}
				else
				{
					$options[] = JHtml::_('select.option', $item->$key, $item->$value);
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
