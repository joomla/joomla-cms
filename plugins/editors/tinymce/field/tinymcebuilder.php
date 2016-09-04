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
 * @since       __DEPLOY_VERSION__
 */
class JFormFieldTinymceBuilder extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'tinymcebuilder';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $layout = 'plugins.editors.tinymce.field.tinymcebuilder';

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

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
		$data['viewLevels']    = $this->getAccessViewLevels();

		// Prepare the forms for extra options
		$levelsForms = array();
		$formsource  = JPATH_PLUGINS . '/editors/tinymce/form/leveloptions.xml';

		// Check the old values for B/C
		$valueOld = array();
		if ($this->value && empty($this->value['extraoptions']))
		{
			$valueOld = $this->form->getValue('params');
		}

		foreach($data['viewLevels'] as $level)
		{
			$levelId  = $level['value'];
			$formname = 'view.level.form.' . $levelId;
			$control  = $this->name . '[extraoptions][' . $levelId . ']';

			$levelsForms[$levelId] = JForm::getInstance($formname, $formsource, array('control' => $control));

			// Bind the values
			$formValues = empty($this->value['extraoptions'][$levelId]) ? $valueOld : $this->value['extraoptions'][$levelId];

			$levelsForms[$levelId]->bind($formValues);
		}

		$data['viewLevelForms'] = $levelsForms;

		return $data;
	}

	/**
	 * Get list of Access View Levels
	 *
	 * @return array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getAccessViewLevels()
	{
		static $levels = array();

		if (empty($levels))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select( $db->quoteName('a.id', 'value') . ', ' . $db->quoteName('a.title', 'text'))
                ->from( $db->quoteName('#__viewlevels', 'a'))
			    ->group( $db->quoteName(array( 'a.id', 'a.title', 'a.ordering')))
			    ->order( $db->quoteName('a.ordering') . ' ASC')
			    ->order( $db->quoteName('title') . ' ASC');

			// Get the options.
			$db->setQuery($query);
			$levels = $db->loadAssocList();
			$levels = $levels ? $levels : array();
		}

		return $levels;
	}

}
