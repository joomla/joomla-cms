<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('filelist');

/**
 * Supports an HTML select list of image
 *
 * @since  11.1
 */
class JFormFieldImageList extends JFormFieldFileList implements JFormDomfieldinterface
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'ImageList';

	/**
	 * Method to get the list of images field options.
	 * Use the filter attribute to specify allowable file extensions.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Define the image file type filter.
		$this->filter = '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$|\.jpeg$|\.psd$|\.eps$';

		// Get the field options.
		return parent::getOptions();
	}

	/**
	 * Function to manipulate the DOM element of the field. The form can be
	 * manipulated at that point.
	 *
	 * @param   stdClass    $field      The field.
	 * @param   DOMElement  $fieldNode  The field node.
	 * @param   JForm       $form       The form.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function postProcessDomNode($field, DOMElement $fieldNode, JForm $form)
	{
		$fieldNode->setAttribute('hide_default', 'true');
		$fieldNode->setAttribute('directory', '/images/' . $fieldNode->getAttribute('directory'));

		return parent::postProcessDomNode($field, $fieldNode, $form);
	}
}
