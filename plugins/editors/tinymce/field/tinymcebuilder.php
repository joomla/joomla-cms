<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
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
	 * The prepared layout data
	 *
	 * @var    array
	 * @since  3.7.0
	 */
	protected $layoutData = array();

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since  3.7.0
	 */
	protected function getLayoutData()
	{
		if (!empty($this->layoutData))
		{
			return $this->layoutData;
		}

		$data       = parent::getLayoutData();
		$paramsAll  = (object) $this->form->getValue('params');
		$setsAmount = empty($paramsAll->sets_amount) ? 3 : $paramsAll->sets_amount;

		if (empty($data['value']))
		{
			$data['value'] = array();
		}

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

		// Prepare the forms for each set
		$setsForms  = array();
		$formsource = JPATH_PLUGINS . '/editors/tinymce/form/setoptions.xml';

		// Preload an old params for B/C
		$setParams = new stdClass;
		if (!empty($paramsAll->html_width) && empty($paramsAll->configuration['setoptions']))
		{
			$plugin = JPluginHelper::getPlugin('editors', 'tinymce');

			JFactory::getApplication()->enqueueMessage(JText::sprintf('PLG_TINY_LEGACY_WARNING', '#'), 'warning');

			if (is_object($plugin) && !empty($plugin->params))
			{
				$setParams = (object) json_decode($plugin->params);
			}
		}

		// Collect already used groups
		$groupsInUse = array();

		// Prepare the Set forms, for the set options
		foreach (array_keys($data['setsNames']) as $num)
		{
			$formname = 'set.form.' . $num;
			$control  = $this->name . '[setoptions][' . $num . ']';

			$setsForms[$num] = JForm::getInstance($formname, $formsource, array('control' => $control));

			// Check whether we already have saved values or it first time or even old params
			if (empty($this->value['setoptions'][$num]))
			{
				$formValues = $setParams;

				/*
				 * Predefine group:
				 * Set 0: for Administrator, Editor, Super Users (4,7,8)
				 * Set 1: for Registered, Manager (2,6), all else are public
				 */
				$formValues->access = !$num ? array(4,7,8) : ($num === 1 ? array(2,6) : array());

				// Assign Public to the new Set, but only when it not in use already
				if (empty($formValues->access) && !in_array(1, $groupsInUse))
				{
					$formValues->access = array(1);
				}
			}
			else
			{
				$formValues = (object) $this->value['setoptions'][$num];
			}

			// Collect already used groups
			if (!empty($formValues->access))
			{
				$groupsInUse = array_merge($groupsInUse, $formValues->access);
			}

			// Bind the values
			$setsForms[$num]->bind($formValues);
		}

		krsort($data['setsNames']);

		$data['setsForms'] = $setsForms;

		// Check for TinyMCE language file
		$language      = JFactory::getLanguage();
		$languageFile1 = 'media/editors/tinymce/langs/' . $language->getTag() . '.js';
		$languageFile2 = 'media/editors/tinymce/langs/' . substr($language->getTag(), 0, strpos($language->getTag(), '-')) . '.js';

		$data['languageFile'] = '';

		if (file_exists(JPATH_ROOT . '/' . $languageFile1))
		{
			$data['languageFile'] = $languageFile1;
		}
		elseif (file_exists(JPATH_ROOT . '/' . $languageFile2))
		{
			$data['languageFile'] = $languageFile2;
		}

		$this->layoutData = $data;

		return $data;
	}

}
