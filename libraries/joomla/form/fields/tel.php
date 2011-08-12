<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla Platform.
 * Supports a text field telephone numbers.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 * 
 * @see         JFormRuleTel for telephone number validation
 * @see         JHtmlTel for rendering of telephone numbers
 * @link        http://www.w3.org/TR/html-markup/input.tel.html
 */
class JFormFieldTel extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Tel';
}
