<?php
/**
 * @package    Fields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
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
