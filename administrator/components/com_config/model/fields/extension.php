<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

// WHY DON'T THESE AUTOLOAD!!!!!!
require_once JPATH_LIBRARIES.'/joomla/form/fields/list.php';

/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @since  11.1
 */
class JFormFieldExtension extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Extension';


	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config/');

		$componentModel = new ConfigModelComponent(array('com_config'));
		$components = $componentModel->getList();

		ConfigHelperConfig::loadLanguageForComponents($components);
		foreach($components AS $component)
		{
			$this->element->addChild('option', $component->element)->addAttribute('value', $component->element);
		}

		return parent::getOptions();
	}
}
