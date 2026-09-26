<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use DebugBar\DataCollector\DataCollectorInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserFactoryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * User collector that stores the user id of the person making the request allowing us to filter on it after storage
 *
 * @since  4.2.4
 */
class UserCollector implements DataCollectorInterface
{
    /**
     * Collector name.
     *
     * @var   string
     * @since 4.2.4
     */
    private $name = 'juser';

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @since  4.2.4
     *
     * @return array Collected data
     */
    public function collect()
    {
        $user = Factory::getApplication()->getIdentity()
            ?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);

        return ['user_id' => $user->id];
    }

    /**
     * Returns the unique name of the collector
     *
     * @since  4.2.4
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
