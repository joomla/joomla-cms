<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Field;

use Joomla\CMS\Form\Field\PredefinedlistField;

\defined('_JEXEC') or die;

/**
 * Form Field to load a list of request types
 *
 * @since  3.9.0
 */
class RequesttypeField extends PredefinedlistField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	public $type = 'RequestType';

	/**
	 * Available types
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $predefinedOptions = [
		'export' => 'COM_PRIVACY_HEADING_REQUEST_TYPE_TYPE_EXPORT',
		'remove' => 'COM_PRIVACY_HEADING_REQUEST_TYPE_TYPE_REMOVE',
	];
}
