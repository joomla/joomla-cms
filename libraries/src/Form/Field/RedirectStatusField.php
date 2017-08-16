<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('predefinedlist');

/**
 * Redirect Status field.
 *
 * @since  3.8.0
 */
class RedirectStatusField extends \JFormFieldPredefinedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.8.0
	 */
	public $type = 'Redirect_Status';

	/**
	 * Available statuses
	 *
	 * @var  array
	 * @since  3.8.0
	 */
	protected $predefinedOptions = array(
		'-2' => 'JTRASHED',
		'0'  => 'JDISABLED',
		'1'  => 'JENABLED',
		'*'  => 'JALL',
	);
}
