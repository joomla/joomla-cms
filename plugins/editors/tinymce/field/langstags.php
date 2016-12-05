<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the TinyMCE editor.
 * Generates the list of the languages for wcag 2.0.
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 * @since       3.4
 */
class JFormFieldLangstags extends JFormFieldList
{
	protected $type = 'langstags';

	/**
	 * Method to get the languages options.
	 *
	 * @return  array  The languages.
	 *
	 * @since   3.7
	 */
	public function getOptions()
	{
		include_once dirname(__DIR__) . '/helpers/tinymce.php';

		$options    = TinymceHelper::getAllLanguages();
		$newOptions = array();

		// We need to merge the english with the local language name
		foreach ($options as $i => $option)
		{
			$newOptions[$i]['text'] = $option['name'] . ', ' . $option['nativeName'];
			$newOptions[$i]['value']   = $option['code'];
		}

		$options = array_merge(parent::getOptions(), $newOptions);

		return $options;
	}

	/**
	 * Method to get the field input markup for the list of the language tags for wcag 2.0.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.7
	 */
	protected function getInput()
	{
		// Get the field options.
		$options = (array) $this->getOptions();

		return JHtml::_(
			'select.genericlist',
			$options,
			$this->name,
			array('multiple' => 'true'),
			'value',
			'text',
			$this->value,
			$this->text
		);
	}
}
