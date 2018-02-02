<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Associations;

/**
 * Assoc Field class.
 * Supports a generic list of options.
 * Displays only when Associations are enabled.
 *
 * @since  4.0
 */
class AssocField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $type = 'Assoc';

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     AssocField::setup()
	 * @since   4.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		if (!Associations::isEnabled())
		{
			return false;
		}

		return parent::setup($element, $value, $group);
	}
}
