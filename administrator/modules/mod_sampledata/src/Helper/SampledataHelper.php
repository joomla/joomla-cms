<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_sampledata
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Sampledata\Administrator\Helper;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_sampledata
 *
 * @since  3.8.0
 */
class SampledataHelper
{
    /**
     * Get a list of sampledata.
     *
     * @return  mixed  An array of sampledata, or false on error.
     *
     * @since  5.1.0
     */
    public function getSampledataList()
    {
        PluginHelper::importPlugin('sampledata');

        return Factory::getApplication()
            ->getDispatcher()
            ->dispatch(
                'onSampledataGetOverview',
                AbstractEvent::create(
                    'onSampledataGetOverview',
                    [
                        'subject' => new \stdClass(),
                    ]
                )
            )
            ->getArgument('result') ?? [];
    }

    /**
     * Get a list of sampledata.
     *
     * @return  mixed  An array of sampledata, or false on error.
     *
     * @since  3.8.0
     *
     * @deprecated 5.1.0 will be removed in 7.0
     *             Use the non-static method getSampledataList
     *             Example: Factory::getApplication()->bootModule('mod_sampledata', 'administrator')
     *                            ->getHelper('SampledataHelper')
     *                            ->getSampledataList()
     */
    public static function getList()
    {
        return (new self())->getSampledataList();
    }
}
