<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service registry for JHtml services
 *
 * @since  4.0.0
 */
final class Registry
{
    /**
     * Mapping array of the core CMS JHtml helpers
     *
     * @var    array
     * @since  4.0.0
     */
    private $serviceMap = [
        'access'          => Helpers\Access::class,
        'actionsdropdown' => Helpers\ActionsDropdown::class,
        'adminlanguage'   => Helpers\AdminLanguage::class,
        'behavior'        => Helpers\Behavior::class,
        'bootstrap'       => Helpers\Bootstrap::class,
        'category'        => Helpers\Category::class,
        'content'         => Helpers\Content::class,
        'contentlanguage' => Helpers\ContentLanguage::class,
        'date'            => Helpers\Date::class,
        'debug'           => Helpers\Debug::class,
        'draggablelist'   => Helpers\DraggableList::class,
        'dropdown'        => Helpers\Dropdown::class,
        'email'           => Helpers\Email::class,
        'form'            => Helpers\Form::class,
        'formbehavior'    => Helpers\FormBehavior::class,
        'grid'            => Helpers\Grid::class,
        'icons'           => Helpers\Icons::class,
        'jgrid'           => Helpers\JGrid::class,
        'jquery'          => Helpers\Jquery::class,
        'links'           => Helpers\Links::class,
        'list'            => Helpers\ListHelper::class,
        'menu'            => Helpers\Menu::class,
        'number'          => Helpers\Number::class,
        'searchtools'     => Helpers\SearchTools::class,
        'select'          => Helpers\Select::class,
        'sidebar'         => Helpers\Sidebar::class,
        'sortablelist'    => Helpers\SortableList::class,
        'string'          => Helpers\StringHelper::class,
        'tag'             => Helpers\Tag::class,
        'tel'             => Helpers\Telephone::class,
        'uitab'           => Helpers\UiTab::class,
        'user'            => Helpers\User::class,
        'workflowstage'   => Helpers\WorkflowStage::class,
    ];

    /**
     * Get the service for a given key
     *
     * @param   string  $key  The service key to look up
     *
     * @return  string|object
     *
     * @since   4.0.0
     */
    public function getService(string $key)
    {
        if (!$this->hasService($key)) {
            throw new \InvalidArgumentException("The '$key' service key is not registered.");
        }

        return $this->serviceMap[$key];
    }

    /**
     * Check if the registry has a service for the given key
     *
     * @param   string  $key  The service key to look up
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function hasService(string $key): bool
    {
        return isset($this->serviceMap[$key]);
    }

    /**
     * Register a service
     *
     * @param   string         $key      The service key to be registered
     * @param   string|object  $handler  The handler for the service as either a PHP class name or class object
     * @param   boolean        $replace  Flag indicating the service key may replace an existing definition
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function register(string $key, $handler, bool $replace = false)
    {
        // If the key exists already and we aren't instructed to replace existing services, bail early
        if (isset($this->serviceMap[$key]) && !$replace) {
            throw new \RuntimeException("The '$key' service key is already registered.");
        }

        // If the handler is a string, it must be a class that exists
        if (\is_string($handler) && !class_exists($handler)) {
            throw new \RuntimeException("The '$handler' class for service key '$key' does not exist.");
        }

        // Otherwise the handler must be a class object
        if (!\is_string($handler) && !\is_object($handler)) {
            throw new \RuntimeException(
                \sprintf(
                    'The handler for service key %1$s must be a PHP class name or class object, a %2$s was given.',
                    $key,
                    \gettype($handler)
                )
            );
        }

        $this->serviceMap[$key] = $handler;
    }
}
