<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Form
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Platform
 * @subpackage	Form
 * @since		11.1
 */
class JFormFieldUrl extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	11.1
	 */
	protected $type = 'Url';
}
