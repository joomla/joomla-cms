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

		$data['buttons'] = array(
			'bold' => 'Bold',
			'italic' => 'Italic',
			'underline' => 'Underline',
			'strikethrough' => 'Strikethrough',
			'alignleft' => 'Align left',
			'aligncenter' => 'Align center',
			'alignright' => 'Align right',
			'alignjustify' => 'Justify',
			'styleselect' => 'Formats',
			'formatselect' => 'Paragraph',
			'fontselect' => 'Font Family',
			'fontsizeselect' => 'Font Sizes',
			'cut' => 'Cut',
			'copy' => 'Copy',
			'paste' => 'Paste',
			'bullist' => 'Bulleted list',
			'numlist' => 'Numbered list',
			'outdent' => 'Decrease indent',
			'indent' => 'Increase indent',
			'blockquote' => 'Blockquote',
			'undo' => 'Undo',
			'redo' => 'Redo',
			'removeformat' => 'Clear formatting',
			'subscript' => 'Subscript',
			'superscript' => 'Superscript',

//			'hr' => 'Horizontal line',
//			'link' => 'Insert/edit link',
//			'unlink' => 'Remove link',
//			'image' => 'Insert/edit image',
//			'charmap' => 'Special character',
//			'pastetext' => 'Paste as text',
//			'print' => 'Print',
//			'anchor' => 'Anchor',
//			'searchreplace' => 'Find and replace',
//			'visualblocks' => 'Show blocks',
//			'visualchars' => 'Show invisible characters',
//			'code' => 'Source code',
//			'wp_code' => 'Code',
//			'fullscreen' => 'Fullscreen',
//			'insertdatetime' => 'Insert date/time',
//			'media' => 'Insert/edit video',
//			'nonbreaking' => 'Nonbreaking space',
//			'table' => 'Table',
//			'ltr' => 'Left to right',
//			'rtl' => 'Right to left',
//			'emoticons' => 'Emoticons',
//			'forecolor' => 'Text color',
//			'backcolor' => 'Background color',
		);

		return $data;
	}

}
