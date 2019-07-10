<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Provides a list of access levels. Access levels control what users in specific
 * groups can see.
 *
 * @see    JAccess
 * @since  1.7.0
 */
class AccesslevelField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Accesslevel';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $layout = 'joomla.form.field.accesslevel';
}
