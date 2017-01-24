<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Subform
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('components.com_fields.libraries.fieldsplugin', JPATH_ADMINISTRATOR);


/**
 * Fields subform Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgFieldsSubform extends FieldsPlugin
{
	/**
	 * Transforms the field into an XML element and appends it as child on the given parent. This
	 * is the default implementation of a field. Form fields which do support to be transformed into
	 * an XML Element mut implemet the JFormDomfieldinterface.
	 *
	 * @param   stdClass    $field   The field.
	 * @param   DOMElement  $parent  The field node parent.
	 * @param   JForm       $form    The form.
	 *
	 * @return  DOMElement
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onCustomFieldsPrepareDom($field, DOMElement $parent, JForm $form)
	{
		$node = parent::onCustomFieldsPrepareDom($field, $parent, $form);

		if (!$node)
		{
			return $node;
		}

		$node->setAttribute('disabled', 'true');

		$node->setAttribute('formsource', $field->subFormFieldsToAttach);

		// Return the node
		return $node;
	}

	/**
	 * Returns an array of key values to put in a list from the given field.
	 *
	 * @param   stdClass  $field The field.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getSubFormsFromField($field)
	{
		$data = array();

		// Fetch the options from the field
		foreach ($field->value as $subForm)
		{
			foreach ($subForm as $name => $value)
			{
				$data[$value] = $name;
			}
		}

		return $data;
	}
}
