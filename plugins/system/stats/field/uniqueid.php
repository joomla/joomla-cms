<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.stats
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JLoader::register('PlgSystemStatsFormFieldBase', __DIR__ . '/base.php');

/**
 * Unique ID Field class for the Stats Plugin.
 *
 * @since  3.5
 */
class PlgSystemStatsFormFieldUniqueid extends PlgSystemStatsFormFieldBase
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $type = 'Uniqueid';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $layout = 'field.uniqueid';
}
