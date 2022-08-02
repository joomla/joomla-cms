<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML;

use Joomla\CMS\HTML\Helpers\Access;
use Joomla\CMS\HTML\Helpers\ActionsDropdown;
use Joomla\CMS\HTML\Helpers\AdminLanguage;
use Joomla\CMS\HTML\Helpers\Behavior;
use Joomla\CMS\HTML\Helpers\Bootstrap;
use Joomla\CMS\HTML\Helpers\Category;
use Joomla\CMS\HTML\Helpers\Content;
use Joomla\CMS\HTML\Helpers\ContentLanguage;
use Joomla\CMS\HTML\Helpers\Date;
use Joomla\CMS\HTML\Helpers\Debug;
use Joomla\CMS\HTML\Helpers\DraggableList;
use Joomla\CMS\HTML\Helpers\Dropdown;
use Joomla\CMS\HTML\Helpers\Email;
use Joomla\CMS\HTML\Helpers\Form;
use Joomla\CMS\HTML\Helpers\FormBehavior;
use Joomla\CMS\HTML\Helpers\Grid;
use Joomla\CMS\HTML\Helpers\Icons;
use Joomla\CMS\HTML\Helpers\JGrid;
use Joomla\CMS\HTML\Helpers\Jquery;
use Joomla\CMS\HTML\Helpers\Links;
use Joomla\CMS\HTML\Helpers\ListHelper;
use Joomla\CMS\HTML\Helpers\Menu;
use Joomla\CMS\HTML\Helpers\Number;
use Joomla\CMS\HTML\Helpers\SearchTools;
use Joomla\CMS\HTML\Helpers\Select;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\HTML\Helpers\SortableList;
use Joomla\CMS\HTML\Helpers\StringHelper;
use Joomla\CMS\HTML\Helpers\Tag;
use Joomla\CMS\HTML\Helpers\Telephone;
use Joomla\CMS\HTML\Helpers\UiTab;
use Joomla\CMS\HTML\Helpers\User;
use Joomla\CMS\HTML\Helpers\WorkflowStage;
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
     * @since  4.0.0
     */
    private array $serviceMap = [
        'access'          => Access::class,
        'actionsdropdown' => ActionsDropdown::class,
        'adminlanguage'   => AdminLanguage::class,
        'behavior'        => Behavior::class,
        'bootstrap'       => Bootstrap::class,
        'category'        => Category::class,
        'content'         => Content::class,
        'contentlanguage' => ContentLanguage::class,
        'date'            => Date::class,
        'debug'           => Debug::class,
        'draggablelist'   => DraggableList::class,
        'dropdown'        => Dropdown::class,
        'email'           => Email::class,
        'form'            => Form::class,
        'formbehavior'    => FormBehavior::class,
        'grid'            => Grid::class,
        'icons'           => Icons::class,
        'jgrid'           => JGrid::class,
        'jquery'          => Jquery::class,
        'links'           => Links::class,
        'list'            => ListHelper::class,
        'menu'            => Menu::class,
        'number'          => Number::class,
        'searchtools'     => SearchTools::class,
        'select'          => Select::class,
        'sidebar'         => Sidebar::class,
        'sortablelist'    => SortableList::class,
        'string'          => StringHelper::class,
        'tag'             => Tag::class,
        'tel'             => Telephone::class,
        'uitab'           => UiTab::class,
        'user'            => User::class,
        'workflowstage'   => WorkflowStage::class,
    ];

    /**
     * Get the service for a given key
     *
     * @param   string  $key  The service key to look up
     *
     *
     * @since   4.0.0
     */
    public function getService(string $key): string|object
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
                sprintf(
                    'The handler for service key %1$s must be a PHP class name or class object, a %2$s was given.',
                    $key,
                    \gettype($handler)
                )
            );
        }

        $this->serviceMap[$key] = $handler;
    }
}
