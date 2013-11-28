<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of fonts
 *
 * @package		Joomla.Plugin
 * @subpackage	Editors.codemirror
 * @since		3.3
 */
class JFormFieldFonts extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  12.4
	 */
	protected $type = 'Fonts';

	/**
	 * Method to get the list of fonts field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  12.4
	 */
	protected function getOptions()
	{
		$fonts = json_decode(JFile::read(__DIR__ . '/fonts.json'));
		$options = array();

		foreach ($fonts as $key => $info)
		{
			$options[] = JHtml::_('select.option', $key, $info->name);
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}
}
