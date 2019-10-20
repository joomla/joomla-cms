<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Repeatable
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;

/**
 * Repeatable plugin.
 *
 * @since  3.9.0
 */
class PlgFieldsRepeatable extends FieldsPlugin
{
	/**
	 * Before prepares the field value.
	 *
	 * @param   string     $context  The context.
	 * @param   \stdclass  $item     The item.
	 * @param   \stdclass  $field    The field.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function onCustomFieldsBeforePrepareField($context, $item, $field)
	{
		if (!$this->app->isClient('api'))
		{
			return;
		}

		// Check if the field should be processed by us
		if (!$this->isTypeSupported($field->type))
		{
			return;
		}

		$field->apivalue = (array) json_decode($field->value, true);
	}

	/**
	 * Transforms the field into a DOM XML element and appends it as a child on the given parent.
	 *
	 * @param   stdClass    $field   The field.
	 * @param   DOMElement  $parent  The field node parent.
	 * @param   Form        $form    The form.
	 *
	 * @return  DOMElement
	 *
	 * @since   3.9.0
	 */
	public function onCustomFieldsPrepareDom($field, DOMElement $parent, Form $form)
	{
		$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

		if (!$fieldNode)
		{
			return $fieldNode;
		}

		$readonly = false;

		if (!FieldsHelper::canEditFieldValue($field))
		{
			$readonly = true;
		}

		$fieldNode->setAttribute('type', 'subform');
		$fieldNode->setAttribute('multiple', 'true');
		$fieldNode->setAttribute('layout', 'joomla.form.field.subform.repeatable-table');

		// Build the form source
		$fieldsXml = new SimpleXMLElement('<form/>');
		$fields    = $fieldsXml->addChild('fields');

		// Get the form settings
		$formFields = $field->fieldparams->get('fields');

		// Add the fields to the form
		foreach ($formFields as $index => $formField)
		{
			$child = $fields->addChild('field');
			$child->addAttribute('name', $formField->fieldname);
			$child->addAttribute('type', $formField->fieldtype);
			$child->addAttribute('readonly', $readonly);

			if (isset($formField->fieldfilter))
			{
				$child->addAttribute('filter', $formField->fieldfilter);
			}
		}

		$fieldNode->setAttribute('formsource', $fieldsXml->asXML());

		// Return the node
		return $fieldNode;
	}
}
