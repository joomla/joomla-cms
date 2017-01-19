<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Gallery
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');

/**
 * Fields Gallery form field
 *
 * @since  3.7.0
 */
class JFormFieldGallery extends JFormFieldList
{

	public $type = 'Gallery';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.7.0
	 */
	protected function getOptions()
	{
		$options = array();

		if (! $this->required)
		{
			$options[] = JHtml::_('select.option', '', JText::alt('JOPTION_DO_NOT_USE', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)));
		}

		$path = (string) $this->element['directory'];

		if (! is_dir($path))
		{
			$path = JPATH_ROOT . '/' . $path;
		}

		// Get a list of folders in the search path with the given filter.
		$folders = JFolder::folders($path, '.', true, true);

		// Build the options list from the list of folders.
		if (is_array($folders))
		{
			foreach ($folders as $folder)
			{
				// Relative path, in order to use str_replace you need same directory separators, so "clean" the paths
				$relativePath = str_replace(JPath::clean($path . '/'), '', JPath::clean($folder));

				$options[] = JHtml::_('select.option', $relativePath, $relativePath);
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
