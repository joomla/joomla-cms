<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Form Field class for the TinyMCE editor.
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 * @since       3.7.0
 */
class JFormFieldTinymceBuilder extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $type = 'tinymcebuilder';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $layout = 'plugins.editors.tinymce.field.tinymcebuilder';

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since  3.7.0
	 */
	protected function getLayoutData()
	{
		$data       = parent::getLayoutData();
		$valueAll   = (object) $this->form->getValue('params');
		$setsAmount = empty($valueAll->sets_amount) ? 3 : $valueAll->sets_amount;

		// Get the plugin
		require_once JPATH_PLUGINS . '/editors/tinymce/tinymce.php';

		$menus = array(
			'edit'   => array('label' => 'Edit'),
			'insert' => array('label' => 'Insert'),
			'view'   => array('label' => 'View'),
			'format' => array('label' => 'Format'),
			'table'  => array('label' => 'Table'),
			'tools'  => array('label' => 'Tools'),
		);

		$data['menus']         = $menus;
		$data['menubarSource'] = array_keys($menus);
		$data['buttons']       = PlgEditorTinymce::getKnownButtons();
		$data['buttonsSource'] = array_keys($data['buttons']);
		$data['toolbarPreset'] = PlgEditorTinymce::getToolbarPreset();
		$data['setsAmount']    = $setsAmount;

		// Get array of sets names
		for ($i = 0; $i < $setsAmount; $i++)
		{
			$data['setsNames'][$i] = JText::sprintf('PLG_TINY_SET_TITLE', $i);
		}

		krsort($data['setsNames']);

		// Prepare the forms for each set
		$setsForms  = array();
		$formsource = JPATH_PLUGINS . '/editors/tinymce/form/setoptions.xml';

		// Check the old values for B/C
		$valueOld = new stdClass;
		if ($this->value && empty($this->value['setoptions']))
		{
			$valueOld = $valueAll;
		}

		foreach (array_keys($data['setsNames']) as $num)
		{
			$formname = 'set.form.' . $num;
			$control  = $this->name . '[setoptions][' . $num . ']';

			$setsForms[$num] = JForm::getInstance($formname, $formsource, array('control' => $control));

			// Bind the values

			if (empty($this->value['setoptions'][$num]))
			{
				$formValues = $valueOld;

				// Predefine access: 0 for special, 1 for registered, all else is public
				$formValues->access = !$num ? 3 : ($num === 1 ? 2 : 1);
			}
			else
			{
				$formValues = $this->value['setoptions'][$num];
			}

			$setsForms[$num]->bind($formValues);
		}

		$data['setsForms'] = $setsForms;

		return $data;
	}

}
