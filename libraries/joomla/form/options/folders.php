<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');

/**
 * Folders Option class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
abstract class JFormOptionFolders
{
	/**
	 * Method to get a list of options.
	 *
	 * @param   SimpleXMLElement  $option     <option/> element
	 * @param   string            $fieldname  The name of the field containing this option.
	 *
	 * @return  array  A list of objects representing HTML option elements (such as created by JHtmlSelect::option).
	 *
	 * @since   11.1
	 */
	public static function getOptions(SimpleXMLElement $option, $fieldname = '')
	{
		$options = array();

		// Initialize some field attributes.
		$filter = (string) $option['filter'];
		$exclude = (string) $option['exclude'];

		// Get the path in which to search for file options.
		$path = (string) $option['directory'];

		if (!is_dir($path))
		{
			$path = JPATH_ROOT . '/' . $path;
		}

		// Get a list of folders in the search path with the given filter.
		$folders = JFolder::folders($path, $filter);

		// Build the options list from the list of folders.
		if (is_array($folders))
		{
			foreach ($folders as $folder)
			{
				// Check to see if the file is in the exclude mask.
				if ($exclude)
				{
					if (preg_match(chr(1) . $exclude . chr(1), $folder))
					{
						continue;
					}
				}

				$options[] = (object) array(
					'value' => $folder,
					'text' => $folder
				);
			}
		}

		return $options;
	}
}
