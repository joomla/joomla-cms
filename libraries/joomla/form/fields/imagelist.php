<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('filelist');

/**
 * Supports an HTML select list of image
 *
 * @package		Joomla.Platform
 * @subpackage	Form
 * @since		11.1
 */
class JFormFieldImageList extends JFormFieldFileList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	11.1
	 */
	public $type = 'ImageList';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	11.1
	 */
	protected function getOptions()
	{
		// Define the image file type filter.
		$filter = '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$|\.jpeg$|\.psd$|\.eps$';

		// Set the form field element attribute for file type filter.
		$this->element->addAttribute('filter', $filter);

		// Get the field options.
		return parent::getOptions();
	}
}
