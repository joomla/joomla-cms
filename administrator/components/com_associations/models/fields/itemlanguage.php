<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Utilities\ArrayHelper;

JLoader::register('AssociationsHelper', JPATH_ADMINISTRATOR . '/components/com_associations/helpers/associations.php');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  3.7.0
 */
class JFormFieldItemLanguage extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $type = 'ItemLanguage';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  3.7.0
	 */
	protected function getOptions()
	{
		$input = JFactory::getApplication()->input;

		list($extensionName, $typeName) = explode('.', $input->get('itemtype'));

		$extension = AssociationsHelper::getSupportedExtension($extensionName);
		$types     = $extension->get('types');

		if (array_key_exists($typeName, $types))
		{
			$type = $types[$typeName];
		}

		$details = $type->get('details');

		if (array_key_exists('fields', $details))
		{
			$fields = $details['fields'];
		}

		$languageField = substr($fields['language'], 2);
		$referenceId   = $input->get('id', 0, 'int');
		$reference     = ArrayHelper::fromObject(AssociationsHelper::getItem($extensionName, $typeName, $referenceId));
		$referenceLang = $reference[$languageField];

		// Get item associations given ID and item type
		$associations = AssociationsHelper::getAssociationList($extensionName, $typeName, $referenceId);

		// Check if user can create items in this component item type.
		$canCreate = AssociationsHelper::allowAdd($extensionName, $typeName);

		// Gets existing languages.
		$existingLanguages = AssociationsHelper::getContentLanguages();

		$options = array();

		// Each option has the format "<lang>|<id>", example: "en-GB|1"
		foreach ($existingLanguages as $langCode => $language)
		{
			// If language code is equal to reference language we don't need it.
			if ($language->lang_code == $referenceLang)
			{
				continue;
			}

			$options[$langCode]       = new stdClass;
			$options[$langCode]->text = $language->title;

			// If association exists in this language.
			if (isset($associations[$language->lang_code]))
			{
				$itemId                    = (int) $associations[$language->lang_code]['id'];
				$options[$langCode]->value = $language->lang_code . ':' . $itemId . ':edit';

				 // Check if user does have permission to edit the associated item.
				$canEdit = AssociationsHelper::allowEdit($extensionName, $typeName, $itemId);

				// ToDo: Do an additional check to check if user can edit a checked out item (if component item type supports it).
				$canCheckout = true;

				// Disable language if user is not allowed to edit the item associated to it.
				$options[$langCode]->disable = !($canEdit && $canCheckout);
			}
			else
			{
				// New item, id = 0 and disabled if user is not allowed to create new items.
				$options[$langCode]->value = $language->lang_code . ':0:add';

				// Disable language if user is not allowed to create items.
				$options[$langCode]->disable = !$canCreate;
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
