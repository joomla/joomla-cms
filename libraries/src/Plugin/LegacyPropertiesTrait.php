<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Plugin;

use Joomla\CMS\Application\ApplicationAwareInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseAwareInterface;
use ReflectionClass;

/**
 * A trait to support the legacy $app and $db properties in plugins.
 *
 * @since   __DEPLOY_VERSION__
 * @deprecated 5.0 Use the ApplicationAwareInterface, DatabaseAwareInterface and their traits
 */
trait LegacyPropertiesTrait
{
    /**
     * Looks for and populates the legacy $app and $db properties.
     *
     * @return  void
     * @throws  \ReflectionException
     * @since   __DEPLOY_VERSION__
     * @deprecated 5.0 Use the ApplicationAwareInterface, DatabaseAwareInterface and their respective traits.
     */
    private function implementLegacyProperties()
    {
        if (property_exists($this, 'app')) {
            @trigger_error(
                'The application should be injected through the ApplicationAwareInterface and trait',
                E_USER_DEPRECATED
            );
            $reflection = new ReflectionClass($this);
            $appProperty = $reflection->getProperty('app');

            if ($appProperty->isPrivate() === false && \is_null($this->app)) {
                $this->app = ($this instanceof ApplicationAwareInterface)
                    ? $this->getApplication()
                    : Factory::getApplication();
            }
        }

        if (property_exists($this, 'db')) {
            @trigger_error(
                'The database should be injected through the DatabaseAwareInterface and trait.',
                E_USER_DEPRECATED
            );
            $reflection = new ReflectionClass($this);
            $dbProperty = $reflection->getProperty('db');

            if ($dbProperty->isPrivate() === false && \is_null($this->db)) {
                $this->db = ($this instanceof DatabaseAwareInterface)
                    ? $this->getDatabase()
                    : Factory::getContainer()->get('DatabaseDriver');
            }
        }
    }
}
