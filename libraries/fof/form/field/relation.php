<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * Form Field class for FOF
 * Relation list
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormFieldRelation extends FOFFormFieldList
{
	/**
	 * Get the rendering of this field type for static display, e.g. in a single
	 * item view (typically a "read" task).
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getStatic() {
		return $this->getRepeatable();
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
		$class         = $this->element['class'] ? (string) $this->element['class'] : $this->id;
		$relationclass = $this->element['relationclass'] ? (string) $this->element['relationclass'] : '';
		$value_field   = $this->element['value_field'] ? (string) $this->element['value_field'] : 'title';
		$translate     = $this->element['translate'] ? (string) $this->element['translate'] : false;
		$link_url      = $this->element['url'] ? (string) $this->element['url'] : false;

		if (!($link_url && $this->item instanceof FOFTable))
		{
			$link_url = false;
		}

		if ($this->element['empty_replacement'])
		{
			$empty_replacement = (string) $this->element['empty_replacement'];
		}

		$relationName = FOFInflector::pluralize($this->name);
		$relations    = $this->item->getRelations()->getMultiple($relationName);

		foreach ($relations as $relation) {

			$html = '<span class="' . $relationclass . '">';

			if ($link_url)
			{
				$keyfield = $relation->getKeyName();
				$this->_relationId =  $relation->$keyfield;

				$url = $this->parseFieldTags($link_url);
				$html .= '<a href="' . $url . '">';
			}

			$value = $relation->get($relation->getColumnAlias($value_field));

			// Get the (optionally formatted) value
			if (!empty($empty_replacement) && empty($value))
			{
				$value = JText::_($empty_replacement);
			}

			if ($translate == true)
			{
				$html .= JText::_($value);
			}
			else
			{
				$html .= $value;
			}

			if ($link_url)
			{
				$html .= '</a>';
			}

			$html .= '</span>';

			$rels[] = $html;
		}

		$html = '<span class="' . $class . '">';
		$html .= implode(', ', $rels);
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
		$options     = array();
		$this->value = array();

		$value_field = $this->element['value_field'] ? (string) $this->element['value_field'] : 'title';

		$input     = new FOFInput;
		$component = ucfirst(str_replace('com_', '', $input->getString('option')));
		$view      = ucfirst($input->getString('view'));
		$relation  = FOFInflector::pluralize((string) $this->element['name']);

		$model = FOFModel::getTmpInstance(ucfirst($relation), $component . 'Model');
		$table = $model->getTable();

		$key   = $table->getKeyName();
		$value = $table->getColumnAlias($value_field);

		foreach ($model->getItemList(true) as $option)
		{
			$options[] = JHtml::_('select.option', $option->$key, $option->$value);
		}

		if ($id = FOFModel::getAnInstance($view)->getId())
		{
			$table = FOFTable::getInstance($view, $component . 'Table');
			$table->load($id);

			$relations = $table->getRelations()->getMultiple($relation);

			foreach ($relations as $item)
			{
				$this->value[] = $item->getId();
			}
		}

		return $options;
	}

	/**
	 * Replace string with tags that reference fields
	 *
	 * @param   string  $text  Text to process
	 *
	 * @return  string         Text with tags replace
	 */
	protected function parseFieldTags($text)
	{
		$ret = $text;

		// Replace [ITEM:ID] in the URL with the item's key value (usually:
		// the auto-incrementing numeric ID)
		$keyfield = $this->item->getKeyName();
		$replace  = $this->item->$keyfield;
		$ret = str_replace('[ITEM:ID]', $replace, $ret);

		// Replace the [ITEMID] in the URL with the current Itemid parameter
		$ret = str_replace('[ITEMID]', JFactory::getApplication()->input->getInt('Itemid', 0), $ret);

		// Replace the [RELATION:ID] in the URL with the relation's key value
		$ret = str_replace('[RELATION:ID]', $this->_relationId, $ret);

		// Replace other field variables in the URL
		$fields = $this->item->getTableFields();

		foreach ($fields as $fielddata)
		{
			$fieldname = $fielddata->Field;

			if (empty($fieldname))
			{
				$fieldname = $fielddata->column_name;
			}

			$search    = '[ITEM:' . strtoupper($fieldname) . ']';
			$replace   = $this->item->$fieldname;
			$ret  = str_replace($search, $replace, $ret);
		}

		return $ret;
	}
}
