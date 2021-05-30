<?php
/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\Rule\FilePathRule;
use Joomla\Registry\Registry;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  __DEPLOY_VERSION__
 */
class FolderPathExistsRule extends FilePathRule
{
	/**
	 * Method to test if the folder path is valid and points to an existing folder (directory) below the Joomla root
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   Registry           $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   Form               $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid and points to an existing folder below the Joomla root, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		if (!parent::test($element, $value, $group, $input, $form))
		{
			return false;
		}

		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		$rootCleaned = rtrim(Path::clean(JPATH_ROOT), \DIRECTORY_SEPARATOR);

		// In case of a folder list field we might have a directory, otherwise it's Joomla root
		if (isset($element['directory']) && trim((string) $element['directory']))
		{
			$parentPath = Path::clean((string) $element['directory']);

			if (strpos($parentPath, $rootCleaned) !== 0)
			{
				$parentPath = JPATH_ROOT . '/' . $parentPath;
			}
		}
		else
		{
			$parentPath = JPATH_ROOT;
		}

		$pathCleaned = rtrim(Path::clean($parentPath . '/' . $value), \DIRECTORY_SEPARATOR);

		// Joomla root is not allowed
		if ($pathCleaned === $rootCleaned)
		{
			return false;
		}

		return Folder::exists($pathCleaned);
	}
}
