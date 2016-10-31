<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Supports a generic list of options.
 *
 * @since  11.1
 */
class JFormFieldList extends JFormAbstractlist implements JFormDomfieldinterface
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'List';

	/**
	 * Method to add an option to the list field.
	 *
	 * @param   string  $text        Text/Language variable of the option.
	 * @param   array   $attributes  Array of attributes ('name' => 'value' format)
	 *
	 * @return  JFormFieldList  For chaining.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addOption($text, $attributes = array())
	{
		if ($text && $this->element instanceof SimpleXMLElement)
		{
			$child = $this->element->addChild('option', $text);

			foreach ($attributes as $name => $value)
			{
				$child->addAttribute($name, $value);
			}
		}

		return $this;
	}
}
