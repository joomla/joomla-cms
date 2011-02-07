<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Form
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.language.helper');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldLanguage extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Language';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getOptions()
	{
		// Initialize some field attributes.
		$client	= (string) $this->element['client'];
		if ($client != 'site' && $client != 'administrator') {
			$client = 'site';
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(
			parent::getOptions(),
			JLanguageHelper::createLanguageList($this->value, constant('JPATH_'.strtoupper($client)), true, true)
		);

		return $options;
	}
}
