<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Menu;

use Joomla\CMS\Cache\CacheControllerFactoryAwareInterface;
use Joomla\CMS\Cache\CacheControllerFactoryAwareTrait;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseAwareTrait;

/**
 * Default factory for creating Menu objects
 *
 * @since  4.0.0
 */
class MenuFactory implements MenuFactoryInterface
{
    use CacheControllerFactoryAwareTrait;
    use DatabaseAwareTrait;

    /**
     * Creates a new Menu object for the requested format.
     *
     * @param   string  $client   The name of the client
     * @param   array   $options  An associative array of options
     *
     * @return  AbstractMenu
     *
     * @since   4.0.0
     * @throws  \InvalidArgumentException
     */
    public function createMenu(string $client, array $options = []): AbstractMenu
    {
        // Create a Menu object
        $classname = __NAMESPACE__ . '\\' . ucfirst(strtolower($client)) . 'Menu';

        if (!class_exists($classname)) {
            throw new \InvalidArgumentException(Text::sprintf('JLIB_APPLICATION_ERROR_MENU_LOAD', $client), 500);
        }

        if (!array_key_exists('db', $options)) {
            $options['db'] = $this->getDatabase();
        }

        $instance = new $classname($options);

        if ($instance instanceof CacheControllerFactoryAwareInterface) {
            $instance->setCacheControllerFactory($this->getCacheControllerFactory());
        }

        return $instance;
    }
}
