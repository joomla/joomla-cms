<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Library helper class
 *
 * @since  3.2
 */
class LibraryHelper
{
    /**
     * The component list cache
     *
     * @var    array
     * @since  3.2
     */
    protected static $libraries = [];

    /**
     * Get the library information.
     *
     * @param   string   $element  Element of the library in the extensions table.
     * @param   boolean  $strict   If set and the library does not exist, the enabled attribute will be set to false.
     *
     * @return  \stdClass   An object with the library's information.
     *
     * @since   3.2
     */
    public static function getLibrary($element, $strict = false)
    {
        // Is already cached?
        if (isset(static::$libraries[$element]) || static::loadLibrary($element)) {
            $result = static::$libraries[$element];

            // Convert the params to an object.
            if (\is_string($result->params)) {
                $result->params = new Registry($result->params);
            }
        } else {
            $result          = new \stdClass();
            $result->enabled = !$strict;
            $result->params  = new Registry();
        }

        return $result;
    }

    /**
     * Checks if a library is enabled
     *
     * @param   string  $element  Element of the library in the extensions table.
     *
     * @return  boolean
     *
     * @since   3.2
     */
    public static function isEnabled($element)
    {
        return static::getLibrary($element, true)->enabled;
    }

    /**
     * Gets the parameter object for the library
     *
     * @param   string   $element  Element of the library in the extensions table.
     * @param   boolean  $strict   If set and the library does not exist, false will be returned
     *
     * @return  Registry  A Registry object.
     *
     * @see     Registry
     * @since   3.2
     */
    public static function getParams($element, $strict = false)
    {
        return static::getLibrary($element, $strict)->params;
    }

    /**
     * Save the parameters object for the library
     *
     * @param   string    $element  Element of the library in the extensions table.
     * @param   Registry  $params   Params to save
     *
     * @return  Registry|boolean  A Registry object.
     *
     * @see     Registry
     * @since   3.2
     */
    public static function saveParams($element, $params)
    {
        if (static::isEnabled($element)) {
            // Save params in DB
            $db           = Factory::getContainer()->get(DatabaseInterface::class);
            $paramsString = $params->toString();
            $query        = $db->createQuery()
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('params') . ' = :params')
                ->where($db->quoteName('type') . ' = ' . $db->quote('library'))
                ->where($db->quoteName('element') . ' = :element')
                ->bind(':params', $paramsString)
                ->bind(':element', $element);
            $db->setQuery($query);

            $result = $db->execute();

            // Update params in libraries cache
            if ($result && isset(static::$libraries[$element])) {
                static::$libraries[$element]->params = $params;
            }

            return $result;
        }

        return false;
    }

    /**
     * Load the installed library into the libraries property.
     *
     * @param   string  $element  The element value for the extension
     *
     * @return  boolean  True on success
     *
     * @since   3.7.0
     */
    protected static function loadLibrary($element)
    {
        $loader = function ($element) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->select($db->quoteName(['extension_id', 'element', 'params', 'enabled'], ['id', 'option', null, null]))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('library'))
                ->where($db->quoteName('element') . ' = :element')
                ->bind(':element', $element);
            $db->setQuery($query);

            return $db->loadObject();
        };

        /** @var CallbackController $cache */
        $cache = Factory::getContainer()->get(CacheControllerFactoryInterface::class)->createCacheController('callback', ['defaultgroup' => '_system']);

        try {
            static::$libraries[$element] = $cache->get($loader, [$element], __METHOD__ . $element);
        } catch (CacheExceptionInterface) {
            static::$libraries[$element] = $loader($element);
        }

        if (empty(static::$libraries[$element])) {
            // Fatal error.
            $error = Text::_('JLIB_APPLICATION_ERROR_LIBRARY_NOT_FOUND');
            Log::add(Text::sprintf('JLIB_APPLICATION_ERROR_LIBRARY_NOT_LOADING', $element, $error), Log::WARNING, 'jerror');

            return false;
        }

        return true;
    }
}
