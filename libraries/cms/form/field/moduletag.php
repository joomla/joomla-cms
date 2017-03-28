<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Moduel Tag field.
 *
 * @since  3.0
 */
class JFormFieldModuletag extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $type = 'ModuleTag';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.0
	 */
	protected function getOptions()
	{
		$options = array();
		$tags    = array('address', 'article', 'aside', 'details', 'div', 'footer', 'header', 'main', 'nav', 'section', 'summary');

		// Create one new option object for each tag
		foreach ($tags as $tag)
		{
			$tmp = JHtml::_('select.option', $tag, $tag);
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
