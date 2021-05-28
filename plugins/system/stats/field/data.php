<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.stats
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('PlgSystemStatsFormFieldBase', __DIR__ . '/base.php');

/**
 * Unique ID Field class for the Stats Plugin.
 *
 * @since  3.5
 */
class PlgSystemStatsFormFieldData extends PlgSystemStatsFormFieldBase
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $type = 'Data';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $layout = 'field.data';

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	protected function getLayoutData()
	{
		$data       = parent::getLayoutData();

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('system', 'stats');

		$result = $dispatcher->trigger('onGetStatsData', array('stats.field.data'));

		$data['statsData'] = $result ? reset($result) : array();

		return $data;
	}
}
