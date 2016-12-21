<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Text
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Fields Text Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgFieldsText extends JPlugin
{
	protected $autoloadLanguage = true;

	/**
	 * Returns the custom fields specification.
	 *
	 * @return  string[][]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onGetCustomFieldTypes()
	{
		$data = array('type' => 'text', 'label' => JText::_('PLG_FIELDS_TEXT_LABEL'));
		return array($data);
	}

	/**
	 * The form event.
	 *
	 * @param   JForm     $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm(JForm $form, $data)
	{
		if (strpos($form->getName(), 'com_fields.field') !== 0)
		{
			return;
		}

		$formData = (object)$data;

		// Gather the type
		$type = $form->getValue('type') ? : $this->_name;

		if(isset($formData->type) && $formData->type)
		{
			$type = $formData->type;
		}

		if ($type != $this->_name)
		{
			return;
		}

		$form->load(file_get_contents(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/params.xml'), true, '/form/*');
	}
}
