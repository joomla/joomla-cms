<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.stats
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Stats\Field;

use Joomla\CMS\Event\Plugin\System\Stats\GetStatsDataEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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

        $result = Factory::getApplication()->getDispatcher()->dispatch(
            'onGetStatsData',
            new GetStatsDataEvent('onGetStatsData', ['context' => 'stats.field.data'])
        )->getArgument('result', []);

        $data['statsData'] = $result ? reset($result) : [];

        return $data;
    }
}
