<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Router;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\DatabaseInterface;

/**
 * Default router factory.
 *
 * @since  4.0.0
 */
class RouterFactory implements RouterFactoryInterface
{
    /**
     * The namespace must be like:
     * Joomla\Component\Content
     *
     * @param   string                    $namespace        The namespace
     * @param   CategoryFactoryInterface  $categoryFactory  The category object
     * @param   DatabaseInterface         $db               The database object
     *
     * @since   4.0.0
     */
    public function __construct(private $namespace, private readonly CategoryFactoryInterface $categoryFactory = null, private readonly DatabaseInterface $db = null)
    {
    }

    /**
     * Creates a router.
     *
     * @param   CMSApplicationInterface  $application  The application
     * @param   AbstractMenu             $menu         The menu object to work with
     *
     *
     * @since   4.0.0
     */
    public function createRouter(CMSApplicationInterface $application, AbstractMenu $menu): RouterInterface
    {
        $className = trim($this->namespace, '\\') . '\\' . ucfirst($application->getName()) . '\\Service\\Router';

        if (!class_exists($className)) {
            throw new \RuntimeException('No router available for this application.');
        }

        return new $className($application, $menu, $this->categoryFactory, $this->db);
    }
}
