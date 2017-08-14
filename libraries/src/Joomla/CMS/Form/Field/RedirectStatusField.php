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
 * @since  __DEPLOY_VERSION__
 */
class RedirectStatusField extends \JFormFieldPredefinedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $type = 'Redirect_Status';

	/**
	 * Available statuses
	 *
	 * @var  array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $predefinedOptions = array(
		'-2' => 'JTRASHED',
		'0'  => 'JDISABLED',
		'1'  => 'JENABLED',
		'*'  => 'JALL',
	);
}
