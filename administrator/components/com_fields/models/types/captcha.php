<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 * 
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JLoader::import('components.com_fields.models.types.base', JPATH_ADMINISTRATOR);

class FieldsTypeCaptcha extends FieldsTypeBase
{

	protected function postProcessDomNode ($field, DOMElement $fieldNode, JForm $form)
	{
		$input = JFactory::getApplication()->input;
		if (JFactory::getApplication()->isAdmin())
		{
			$fieldNode->setAttribute('plugin', JFactory::getConfig()->get('captcha'));
		}
		else if ($input->get('option') == 'com_users' && $input->get('view') == 'profile' && $input->get('layout') != 'edit' &&
				 $input->get('task') != 'save')
		{
			// The user profile page does show the values by creating the form
			// and getting the values from it so we need to disable the field
			$fieldNode->setAttribute('plugin', null);
		}
		$fieldNode->setAttribute('validate', 'captcha');

		return parent::postProcessDomNode($field, $fieldNode, $form);
	}
}
