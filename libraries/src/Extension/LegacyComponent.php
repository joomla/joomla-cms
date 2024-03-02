<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Categories\CategoryInterface;
use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Categories\SectionNotFoundException;
use Joomla\CMS\Component\Router\RouterInterface;
use Joomla\CMS\Component\Router\RouterLegacy;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\Dispatcher\LegacyComponentDispatcher;
use Joomla\CMS\Fields\FieldsFormServiceInterface;
use Joomla\CMS\Fields\FieldsServiceTrait;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\MVC\Factory\LegacyFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\Tag\TagServiceInterface;
use Joomla\CMS\Tag\TagServiceTrait;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Access to component specific services.
 *
 * @since  4.0.0
 */
class LegacyComponent implements
    ComponentInterface,
    MVCFactoryServiceInterface,
    CategoryServiceInterface,
    FieldsFormServiceInterface,
    RouterServiceInterface,
    TagServiceInterface
{
    use CategoryServiceTrait, TagServiceTrait, FieldsServiceTrait {
        CategoryServiceTrait::getTableNameForSection insteadof TagServiceTrait;
        CategoryServiceTrait::getStateColumnForSection insteadof TagServiceTrait;
        CategoryServiceTrait::prepareForm insteadof FieldsServiceTrait;
    }

    /**
     * @var string
     *
     * @since  4.0.0
     */
    private $component;

    /**
     * LegacyComponentContainer constructor.
     *
     * @param   string  $component  The component
     *
     * @since  4.0.0
     */
    public function __construct(string $component)
    {
        $this->component = str_replace('com_', '', $component);
    }

    /**
     * Returns the dispatcher for the given application.
     *
     * @param   CMSApplicationInterface  $application  The application
     *
     * @return  DispatcherInterface
     *
     * @since   4.0.0
     */
    public function getDispatcher(CMSApplicationInterface $application): DispatcherInterface
    {
        return new LegacyComponentDispatcher($application);
    }

    /**
     * Get the factory.
     *
     * @return  MVCFactoryInterface
     *
     * @since   4.0.0
     * @throws  \UnexpectedValueException May be thrown if the factory has not been set.
     */
    public function getMVCFactory(): MVCFactoryInterface
    {
        return new LegacyFactory();
    }

    /**
     * Returns the category service.
     *
     * @param   array   $options  The options
     * @param   string  $section  The section
     *
     * @return  CategoryInterface
     *
     * @since   4.0.0
     * @throws  SectionNotFoundException
     */
    public function getCategory(array $options = [], $section = ''): CategoryInterface
    {
        $classname = ucfirst($this->component) . ucfirst($section) . 'Categories';

        if (!class_exists($classname)) {
            $path = JPATH_SITE . '/components/com_' . $this->component . '/helpers/category.php';

            if (!is_file($path)) {
                throw new SectionNotFoundException();
            }

            include_once $path;
        }

        if (!class_exists($classname)) {
            throw new SectionNotFoundException();
        }

        return new $classname($options);
    }

    /**
     * Adds Count Items for Category Manager.
     *
     * @param   \stdClass[]  $items    The category objects
     * @param   string       $section  The section
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function countItems(array $items, string $section)
    {
        $helper = $this->loadHelper();

        if (!$helper || !\is_callable([$helper, 'countItems'])) {
            return;
        }

        $helper::countItems($items, $section);
    }

    /**
     * Adds Count Items for Tag Manager.
     *
     * @param   \stdClass[]  $items      The content objects
     * @param   string       $extension  The name of the active view.
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function countTagItems(array $items, string $extension)
    {
        $helper = $this->loadHelper();

        if (!$helper || !\is_callable([$helper, 'countTagItems'])) {
            return;
        }

        $helper::countTagItems($items, $extension);
    }

    /**
     * Returns a valid section for articles. If it is not valid then null
     * is returned.
     *
     * @param   string  $section  The section to get the mapping for
     * @param   object  $item     The item
     *
     * @return  string|null  The new section
     *
     * @since   4.0.0
     */
    public function validateSection($section, $item = null)
    {
        $helper = $this->loadHelper();

        if (!$helper || !\is_callable([$helper, 'validateSection'])) {
            return $section;
        }

        return $helper::validateSection($section, $item);
    }

    /**
     * Returns valid contexts.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getContexts(): array
    {
        $helper = $this->loadHelper();

        if (!$helper || !\is_callable([$helper, 'getContexts'])) {
            return [];
        }

        return $helper::getContexts();
    }

    /**
     * Returns the router.
     *
     * @param   CMSApplicationInterface  $application  The application object
     * @param   AbstractMenu             $menu         The menu object to work with
     *
     * @return  RouterInterface
     *
     * @since  4.0.0
     */
    public function createRouter(CMSApplicationInterface $application, AbstractMenu $menu): RouterInterface
    {
        $compname = ucfirst($this->component);
        $class    = $compname . 'Router';

        if (!class_exists($class)) {
            // Use the component routing handler if it exists
            $path = JPATH_SITE . '/components/com_' . $this->component . '/router.php';

            // Use the custom routing handler if it exists
            if (is_file($path)) {
                require_once $path;
            }
        }

        if (class_exists($class)) {
            $reflection = new \ReflectionClass($class);

            if (\in_array('Joomla\\CMS\\Component\\Router\\RouterInterface', $reflection->getInterfaceNames())) {
                return new $class($application, $menu);
            }
        }

        return new RouterLegacy($compname);
    }

    /**
     * Returns the classname of the legacy helper class. If none is found it returns false.
     *
     * @return  boolean|string
     *
     * @since   4.0.0
     */
    private function loadHelper()
    {
        $className = ucfirst($this->component) . 'Helper';

        if (class_exists($className)) {
            return $className;
        }

        $file = Path::clean(JPATH_ADMINISTRATOR . '/components/com_' . $this->component . '/helpers/' . $this->component . '.php');

        if (!is_file($file)) {
            return false;
        }

        \JLoader::register($className, $file);

        if (!class_exists($className)) {
            return false;
        }

        return $className;
    }
}
