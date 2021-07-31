<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright  Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @note       This file has been modified by the Joomla! Project and no longer reflects the original work of its author.
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

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
class FOFFormHeaderModel extends FOFFormHeaderFieldselectable
{
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
		$applyAccess = $this->element['apply_access'] ? (string) $this->element['apply_access'] : 'false';
		$modelName = (string) $this->element['model'];
		$nonePlaceholder = (string) $this->element['none'];
		$translate = empty($this->element['translate']) ? 'true' : (string) $this->element['translate'];
		$translate = in_array(strtolower($translate), array('true','yes','1','on')) ? true : false;

		if (!empty($nonePlaceholder))
		{
			$options[] = JHtml::_('select.option', null, JText::_($nonePlaceholder));
		}

		// Process field attributes
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
