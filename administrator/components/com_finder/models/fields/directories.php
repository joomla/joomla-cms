<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the base adapter.
JLoader::register('FinderIndexerAdapter', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php');

JFormHelper::loadFieldClass('list');

/**
 * Renders a list of directories.
 *
 * @since       2.5
 * @deprecated  4.0  Use JFormFieldFolderlist
 */
class JFormFieldDirectories extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type = 'Directories';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   2.5
	 */
	public function getOptions()
	{
		$values  = array();
		$options = array();
		$exclude = array(
			JPATH_ADMINISTRATOR,
			JPATH_INSTALLATION,
			JPATH_LIBRARIES,
			JPATH_PLUGINS,
			JPATH_SITE . '/cache',
			JPATH_SITE . '/components',
			JPATH_SITE . '/includes',
			JPATH_SITE . '/language',
			JPATH_SITE . '/modules',
			JPATH_THEMES,
			JFactory::getApplication()->get('log_path'),
			JFactory::getApplication()->get('tmp_path')
		);

		// Get the base directories.
		jimport('joomla.filesystem.folder');
		$dirs = JFolder::folders(JPATH_SITE, '.', false, true);

		// Iterate through the base directories and find the subdirectories.
		foreach ($dirs as $dir)
		{
			// Check if the directory should be excluded.
			if (in_array($dir, $exclude))
			{
				continue;
			}

			// Get the child directories.
			$return = JFolder::folders($dir, '.', true, true);

			// Merge the directories.
			if (is_array($return))
			{
				$values[] = $dir;
				$values = array_merge($values, $return);
			}
		}

		// Convert the values to options.
		foreach ($values as $value)
		{
			$options[] = JHtml::_('select.option', str_replace(JPATH_SITE . '/', '', $value), str_replace(JPATH_SITE . '/', '', $values));
		}

		// Add a null option.
		array_unshift($options, JHtml::_('select.option', '', '- ' . JText::_('JNONE') . ' -'));

		return $options;
	}
}
