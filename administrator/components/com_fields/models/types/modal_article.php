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

class FieldsTypeModal_Article extends FieldsTypeBase
{

	protected function postProcessDomNode ($field, DOMElement $fieldNode, JForm $form)
	{
		$form->addFieldPath(JPATH_ADMINISTRATOR . '/components/com_content/models/fields');
	}
}
