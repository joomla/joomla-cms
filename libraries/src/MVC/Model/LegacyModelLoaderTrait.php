<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\MVC\Model;

use Joomla\CMS\Extension\LegacyComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\Table\Table;

/**
 * Trait which contains the legacy getInstance functionality
 *
 * @since       4.0.0
 * @deprecated  5.0 Will be removed without replacement
 */
trait LegacyModelLoaderTrait
{
    /**
     * Create the filename for a resource
     *
     * @param   string  $type   The resource type to create the filename for.
     * @param   array   $parts  An associative array of filename information.
     *
     * @return  string  The filename
     *
     * @since       3.0
     * @deprecated  5.0 See getInstance
     */
    protected static function _createFileName($type, $parts = array())
    {
        return $type === 'model' ? strtolower($parts['name']) . '.php' : '';
    }

    /**
     * Returns a Model object, always creating it
     *
     * @param   string  $type    The model type to instantiate
     * @param   string  $prefix  Prefix for the model class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  self|boolean   A \JModelLegacy instance or false on failure
     *
     * @since       3.0
     * @deprecated  5.0 Get the model through the MVCFactory instead
     */
    public static function getInstance($type, $prefix = '', $config = array())
    {
        @trigger_error(
            sprintf(
                '%1$s::getInstance() is deprecated. Load it through the MVC factory.',
                self::class
            ),
            E_USER_DEPRECATED
        );

        $type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);

        if ($model = self::createModelFromComponent($type, $prefix, $config)) {
            return $model;
        }

        $modelClass = $prefix . ucfirst($type);

        if (!class_exists($modelClass)) {
            $path = Path::find(self::addIncludePath(null, $prefix), self::_createFileName('model', array('name' => $type)));

            if (!$path) {
                $path = Path::find(self::addIncludePath(null, ''), self::_createFileName('model', array('name' => $type)));
            }

            if (!$path) {
                return false;
            }

            require_once $path;

            if (!class_exists($modelClass)) {
                Log::add(Text::sprintf('JLIB_APPLICATION_ERROR_MODELCLASS_NOT_FOUND', $modelClass), Log::WARNING, 'jerror');

                return false;
            }
        }

        return new $modelClass($config);
    }

    /**
     * Adds to the stack of model table paths in LIFO order.
     *
     * @param   mixed  $path  The directory as a string or directories as an array to add.
     *
     * @return  void
     *
     * @since       3.0
     * @deprecated  5.0 See getInstance
     */
    public static function addTablePath($path)
    {
        Table::addIncludePath($path);
    }

    /**
     * Returns a Model object by loading the component from the prefix.
     *
     * @param   string  $type    The model type to instantiate
     * @param   string  $prefix  Prefix for the model class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  ModelInterface|null   A ModelInterface instance or null on failure
     *
     * @since       4.0.0
     * @deprecated  5.0 See getInstance
     */
    private static function createModelFromComponent($type, $prefix = '', $config = []): ?ModelInterface
    {
        // Do nothing when prefix is not given
        if (!$prefix) {
            return null;
        }

        // Boot the component
        $componentName = 'com_' . str_replace('model', '', strtolower($prefix));
        $component     = Factory::getApplication()->bootComponent($componentName);

        // When it is a legacy component or not a MVCFactoryService then ignore
        if ($component instanceof LegacyComponent || !$component instanceof MVCFactoryServiceInterface) {
            return null;
        }

        // Setup the client
        $client = Factory::getApplication()->getName();

        // Detect the client based on the include paths
        $adminPath = Path::clean(JPATH_ADMINISTRATOR . '/components/' . $componentName);
        $sitePath  = Path::clean(JPATH_SITE . '/components/' . $componentName);

        foreach (self::addIncludePath() as $path) {
            if (strpos($path, $adminPath) !== false) {
                $client = 'Administrator';
                break;
            }

            if (strpos($path, $sitePath) !== false) {
                $client = 'Site';
                break;
            }
        }

        // Create the model
        $model = $component->getMVCFactory()->createModel($type, $client, $config);

        // When the model can't be loaded, then return null
        if (!$model) {
            return null;
        }

        // Return the model instance
        return $model;
    }
}
