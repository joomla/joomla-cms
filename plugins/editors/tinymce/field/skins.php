<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.helper');

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the TinyMCE editor.
 * Generates the list of options for available skins.
 *
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 * @since       3.4
 */
class JFormFieldSkins extends JFormFieldList
{
	protected $type = 'skins';

	/**
	 * Method to get the skins options.
	 *
	 * @return  array  The skins option objects.
	 *
	 * @since   3.4
	 */
	public function getOptions()
	{
		$options = array();

		$directories = glob(JPATH_ROOT . '/media/vendor/tinymce/skins' . '/*', GLOB_ONLYDIR);

		for ($i = 0; $i < count($directories); ++$i)
		{
			$dir = basename($directories[$i]);
			$options[] = JHtml::_('select.option', $i, $dir);
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * Method to get the field input markup for the list of skins.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.4
	 */
	protected function getInput()
	{
		$html = array();

		// Get the field options.
		$options = (array) $this->getOptions();
		$attrbs  = array(
			'class' => 'custom-select'
		);

		// Create a regular list.
		$html[] = JHtml::_('select.genericlist', $options, $this->name, $attrbs, 'value', 'text', $this->value, $this->id);

		return implode($html);
	}
}
