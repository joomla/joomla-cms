<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('filelist');
jimport('joomla.filesystem.folder');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 * @since       3.2
 */
class JFormFieldFlagEdit extends JFormFieldFileList
{
	/**
	 * A flexible flag lists
	 *
	 * @var        string
	 * @since   3.2
	 */
	public $type = 'FlagEdit';

	/**
	 * Method to get a list of flags
	 *
	 * @return  array  The field option objects.
	 * @since   3.2
	 */
	protected function getOptions()
	{
		$path = JPATH_SITE . '/media/mod_languages/images/';

	if ($flags = JFolder::files($path, '.gif'))
	{
		foreach ($flags as $flag)
			{
				// Remove the extension
				$nameParts = explode('.', $flag);
				unset($nameParts[count($nameParts) - 1]);
				$value = implode('.', $nameParts);

				// Create the final option
				$option = new StdClass;
				$option->value = $value;
				$option->text = $value;

				// Add the option as result
				$options[] = $option;
			}
		}

		return $options;
	}
}
