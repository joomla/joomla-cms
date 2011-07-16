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
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of files
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldFileList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'FileList';

	/**
	 * Method to get the list of files for the field options.
	 * Specify the directory in an element
	 * Elements allow an exclude mask and stripping of extensions from file name.
	 * Default may optionally be set to null (no file) or -1 (use a default).
	 *
	 * @return  array  The field option objects.
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Initialize some field attributes.
		$filter			= (string) $this->element['filter'];
		$exclude		= (string) $this->element['exclude'];
		$stripExt		= (string) $this->element['stripext'];
		$hideNone		= (string) $this->element['hide_none'];
		$hideDefault	= (string) $this->element['hide_default'];

		// Get the path in which to search for file options.
		$path = (string) $this->element['directory'];
		if (!is_dir($path)) {
			$path = JPATH_ROOT.'/'.$path;
		}

		// Prepend some default options based on field attributes.
		if (!$hideNone) {
			$options[] = JHtml::_('select.option', '-1', JText::alt('JOPTION_DO_NOT_USE', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)));
		}
		if (!$hideDefault) {
			$options[] = JHtml::_('select.option', '', JText::alt('JOPTION_USE_DEFAULT', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)));
		}

		// Get a list of files in the search path with the given filter.
		$files = JFolder::files($path, $filter);

		// Build the options list from the list of files.
		if (is_array($files)) {
			foreach($files as $file) {

				// Check to see if the file is in the exclude mask.
				if ($exclude) {
					if (preg_match(chr(1).$exclude.chr(1), $file)) {
						continue;
					}
				}

				// If the extension is to be stripped, do it.
				if ($stripExt) {
					$file = JFile::stripExt($file);
				}

				$options[] = JHtml::_('select.option', $file, $file);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}