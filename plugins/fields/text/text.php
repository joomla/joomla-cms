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
 * @since  3.7.0
 */
class PlgFieldsText extends JPlugin
{
	protected $autoloadLanguage = true;

	public function onGetCustomFields()
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
	 * @since   3.7.0
	 */
	public function onContentPrepareForm(JForm $form, $data)
	{
		if (strpos($form->getName(), 'com_fields.field') !== 0)
		{
			return;
		}

		// Gather the type
		$type = $form->getValue('type') ? : 'text';

		if(isset($data->type) && $data->type)
		{
			$type = $data->type;
		}

		if ($type != 'text')
		{
			return;
		}

		$form->load(file_get_contents(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/params.xml'), true, '/form/*');
	}
}
