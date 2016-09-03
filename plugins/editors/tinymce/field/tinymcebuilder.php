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

		$menus = array(
			'edit'   => array('label' => 'Edit'),
			'insert' => array('label' => 'Insert'),
			'view'   => array('label' => 'View'),
			'format' => array('label' => 'Format'),
			'table'  => array('label' => 'Table'),
			'tools'  => array('label' => 'Tools'),
		);
		$data['menus']      = $menus;
		$data['menubarSource'] = array_keys($menus);

		// https://www.tinymce.com/docs/demo/full-featured/
		$buttons = array(
			'|' => array('label' => 'Separator', 'text' => '|'),

			'undo' => array('label' => 'Undo', 'text' => ''),
			'redo' => array('label' => 'Redo', 'text' => ''),

			'bold' => array('label' => 'Bold', 'text' => ''),
			'italic' => array('label' => 'Italic', 'text' => ''),
			'underline' => array('label' => 'Underline', 'text' => ''),
			'strikethrough' => array('label' => 'Strikethrough', 'text' => ''),

			'formatselect' => array('label' => 'Paragraph', 'text' => 'Paragraph'),

			'fontselect' => array('label' => 'Font Select', 'text' => 'Font'),
			'fontsizeselect' => array('label' => 'Fontsize Select', 'text' => 'Fontsize'),

			'alignleft' => array('label' => 'Align left', 'text' => ''),
			'aligncenter' => array('label' => 'Align center', 'text' => ''),
			'alignright' => array('label' => 'Align right', 'text' => ''),
			'alignjustify' => array('label' => 'Justify', 'text' => ''),

			'outdent' => array('label' => 'Decrease indent', 'text' => ''),
			'indent' => array('label' => 'Increase indent', 'text' => ''),

			'bullist' => array('label' => 'Bulleted list', 'text' => ''),
			'numlist' => array('label' => 'Numbered list', 'text' => ''),

			'link' => array('label' => 'Insert/edit link', 'text' => ''),
			'unlink' => array('label' => 'Remove link', 'text' => ''),
			'anchor' => array('label' => 'Anchor', 'text' => ''),

			'subscript' => array('label' => 'Subscript', 'text' => ''),
			'superscript' => array('label' => 'Superscript', 'text' => ''),

			'cut' => array('label' => 'Cut', 'text' => ''),
			'copy' => array('label' => 'Copy', 'text' => ''),
			'paste' => array('label' => 'Paste', 'text' => ''),
			'pastetext' => array('label' => 'Paste as text', 'text' => ''),

			'ltr' => array('label' => 'Left to right', 'text' => '', 'plugin' => 'directionality'),
			'rtl' => array('label' => 'Right to left', 'text' => '', 'plugin' => 'directionality'),

			'forecolor' => array('label' => 'Text color', 'text' => ''),
			'backcolor' => array('label' => 'Background color', 'text' => ''),

			'searchreplace' => array('label' => 'Find and replace', 'text' => ''),
			'insertdatetime' => array('label' => 'Insert date/time', 'text' => ''),
			'blockquote' => array('label' => 'Blockquote', 'text' => ''),
			'code' => array('label' => 'Source code', 'text' => ''),
			'codesample' => array('label' => 'Codesample', 'plugin' => 'codesample'),
			'hr' => array('label' => 'Horizontal line', 'text' => ''),
			'table' => array('label' => 'Table', 'text' => ''),
			'charmap' => array('label' => 'Special character', 'text' => ''),
			'visualchars'  => array('label' => 'Show invisible characters', 'plugin' => 'visualchars'),
			'visualblocks' => array('label' => 'Show blocks', 'plugin' => 'visualblocks'),
			'nonbreaking'  => array('label' => 'Nonbreaking space', 'plugin' => 'nonbreaking'),
			'removeformat' => array('label' => 'Clear formatting', 'text' => ''),
			'emoticons' => array('label' => 'Emoticons', 'text' => ''),
			'media' => array('label' => 'Insert/edit video', 'text' => ''),
			'print' => array('label' => 'Print', 'text' => ''),
			'fullscreen' => array('label' => 'Fullscreen', 'plugin' => 'fullscreen'),
			'template' => array('label' => 'Template', 'plugin' => 'template'),

			// TODO: get list of XTD Buttons
		);
		$data['buttons'] = $buttons;

		$data['buttonsSource'] = array(
			'undo', 'redo', '|',
			'bold', 'italic', 'underline', 'strikethrough', '|',
			'formatselect', 'fontselect', 'fontsizeselect', '|',
			'alignleft', 'aligncenter', 'alignright', 'alignjustify', '|',
			'outdent', 'indent', '|',
			'bullist', 'numlist', '|',
			'link', 'unlink', 'anchor', '|',
			'subscript', 'superscript', '|',
			'ltr', 'rtl', '|',
			'cut', 'copy', 'paste', 'pastetext', 'searchreplace', '|',
			'visualchars', 'visualblocks', 'nonbreaking', '|',
			'insertdatetime', 'forecolor', 'backcolor',
			'blockquote', 'code', 'codesample', 'hr', 'table', 'charmap', 'removeformat', 'emoticons',
			'media', 'print', 'fullscreen', 'template',
		);

		$preset = array();
		$preset['simple']   = array(
			'menu' => array(),
			'toolbar1' => array(
				'bold', 'italics', 'underline', 'strikethrough', '|',
				'undo', 'redo', '|', 'bullist', 'numlist', '|', 'code'
			),
			'toolbar2' => array(),
		);
		$preset['advanced'] = array(
			'menu'     => array_keys($menus),
			'toolbar1' => $data['buttonsSource'],
			'toolbar2' => array(),
		);
		$data['toolbarPreset'] = $preset;

		$data['viewLevels'] = $this->getAccessViewLevels();

		// Prepare the forms for extra options
		$levelsForms = array();
		$formsource  = JPATH_PLUGINS . '/editors/tinymce/form/leveloptions.xml';

		// Check the old values for B/C
		$valueOld = array();
		if ($this->value && empty($this->value['extraoptions']))
		{
			$valueOld = $this->form->getValue('params');
		}


		foreach($data['viewLevels'] as $level) {
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
			$query = $db->getQuery( true )
				->select( $db->quoteName( 'a.id', 'value' ) . ', ' . $db->quoteName( 'a.title', 'text' ) )
                ->from( $db->quoteName( '#__viewlevels', 'a' ) )
			    ->group( $db->quoteName( array( 'a.id', 'a.title', 'a.ordering' ) ) )
			    ->order( $db->quoteName( 'a.ordering' ) . ' ASC' )
			    ->order( $db->quoteName( 'title' ) . ' ASC' );

			// Get the options.
			$db->setQuery( $query );
			$levels = $db->loadAssocList();
			$levels = $levels ? $levels : array();
		}

		return $levels;
	}

}
