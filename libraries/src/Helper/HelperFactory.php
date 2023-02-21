<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Helper;

use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Namespace based implementation of the HelperFactoryInterface
 *
 * @since  4.0.0
 */
class HelperFactory implements HelperFactoryInterface
{
    use DatabaseAwareTrait;

    /**
     * The extension namespace
     *
     * @var  string
     *
     * @since   4.0.0
     */
    private $namespace;

    /**
     * HelperFactory constructor.
     *
     * @param   string  $namespace  The namespace
     *
     * @since   4.0.0
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Returns a helper instance for the given name.
     *
     * @param   string  $name    The name
     * @param   array   $config  The config
     *
     * @return  \stdClass
     *
     * @since   4.0.0
     */
    public function getHelper(string $name, array $config = [])
    {
        $className = '\\' . trim($this->namespace, '\\') . '\\' . $name;

        if (!class_exists($className)) {
            return null;
        }

        $helper = new $className($config);

        if ($helper instanceof DatabaseAwareInterface) {
            $helper->setDatabase($this->getDatabase());
        }

        return $helper;
    }
}
