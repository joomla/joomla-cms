<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.stats
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Stats\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Unique ID Field class for the Stats Plugin.
 *
 * @since  3.5
 */
class DataField extends AbstractStatsField
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

		PluginHelper::importPlugin('system', 'stats');

		$result = Factory::getApplication()->triggerEvent('onGetStatsData', array('stats.field.data'));

		// If the plugin is disabled, we need to compile the stats manually
		if (!$result)
		{
			$stats         = Factory::getApplication()->bootPlugin('stats', 'system');
			$stats->params = new Registry(\json_decode($this->getParams()));
			$result = [$stats->onGetStatsData('')];
		}

		$data['statsData'] = reset($result);

		return $data;
	}

	/**
	 * If the plugin is not published, we need to compile the stats manually, for that we need the params from the db.
	 *
	 * @return  string   The JSON of the params of this plugin.
	 */
	private function getParams()
	{
		$db = Factory::getContainer()->get('DatabaseDriver');

		$query = $db->getQuery(true)
			->select('params')
			->from('#__extensions')
			->where($db->quoteName('name') . ' = ' . $db->quote('plg_system_stats'));

		$db->setQuery($query);

		return $db->loadResult();
	}
}
