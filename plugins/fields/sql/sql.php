<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Sql
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('fields.list.list', JPATH_PLUGINS);

/**
 * Fields Sql Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgFieldsSql extends PlgFieldsList
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
	 * @since   3.7.0
	 */
	public function onCustomFieldsPrepareDom($field, DOMElement $parent, JForm $form)
	{
		$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

		if (!$fieldNode)
		{
			return $fieldNode;
		}

		$fieldNode->setAttribute('value_field', 'text');
		$fieldNode->setAttribute('key_field', 'value');

		if (! $fieldNode->getAttribute('query'))
		{
			$fieldNode->setAttribute('query', 'select id as value, name as text from #__users');
		}

		return $fieldNode;
	}
}
