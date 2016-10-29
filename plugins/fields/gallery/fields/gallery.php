<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Gallery
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');

/**
 * Fields Gallery form field
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldGallery extends JFormFieldList
{

	public $type = 'Gallery';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
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
				$relativePath = str_replace($path . '/', '', $folder);

				$options[] = JHtml::_('select.option', $relativePath, $relativePath);
			}
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
