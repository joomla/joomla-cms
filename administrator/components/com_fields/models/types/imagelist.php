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

class FieldsTypeImagelist extends FieldsTypeBase
{

	protected function postProcessDomNode ($field, DOMElement $fieldNode, JForm $form)
	{
		$fieldNode->setAttribute('hide_default', 'true');
		$fieldNode->setAttribute('directory', '/images/' . $fieldNode->getAttribute('directory'));

		return parent::postProcessDomNode($field, $fieldNode, $form);
	}
}
