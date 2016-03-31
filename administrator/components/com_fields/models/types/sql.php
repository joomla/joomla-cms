<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 * 
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::import('components.com_fields.models.types.base', JPATH_ADMINISTRATOR);

class FieldsTypeSql extends FieldsTypeBase
{

	protected function postProcessDomNode ($field, DOMElement $fieldNode, JForm $form)
	{
		$fieldNode->setAttribute('value_field', 'text');
		$fieldNode->setAttribute('key_field', 'value');

		if (! $fieldNode->getAttribute('query'))
		{
			$fieldNode->setAttribute('query', 'select id as value, name as text from #__users');
		}

		return parent::postProcessDomNode($field, $fieldNode, $form);
	}
}
