<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Menu;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Cache\CacheControllerFactoryAwareInterface;
use Joomla\CMS\Cache\CacheControllerFactoryAwareTrait;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Exception\ExecutionFailureException;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Menu class
 *
 * @since  1.5
 */
class SiteMenu extends AbstractMenu implements CacheControllerFactoryAwareInterface
{
    use CacheControllerFactoryAwareTrait;

    /**
     * Application object
     *
     * @var    CMSApplication
     * @since  3.5
     */
    protected $app;

    /**
     * Database driver
     *
     * @var    DatabaseDriver
     * @since  3.5
     */
    protected $db;

    /**
     * Language object
     *
     * @var    Language
     * @since  3.5
     */
    protected $language;

    /**
     * Class constructor
     *
     * @param   array  $options  An array of configuration options.
     *
     * @since   1.5
     */
    public function __construct($options = [])
    {
        // Extract the internal dependencies before calling the parent constructor since it calls $this->load()
        $this->app      = isset($options['app']) && $options['app'] instanceof CMSApplication ? $options['app'] : Factory::getApplication();
        $this->language = isset($options['language']) && $options['language'] instanceof Language ? $options['language'] : Factory::getLanguage();

        if (!isset($options['db']) || !($options['db'] instanceof DatabaseDriver)) {
            @trigger_error(sprintf('Database will be mandatory in 5.0.'), E_USER_DEPRECATED);
            $options['db'] = Factory::getContainer()->get(DatabaseDriver::class);
        }

        $this->db = $options['db'];

        parent::__construct($options);
    }

    /**
     * Loads the entire menu table into memory.
     *
     * @return  boolean  True on success, false on failure
     *
     * @since   1.5
     */
    public function load()
    {
        $loader = function () {
            $currentDate = Factory::getDate()->toSql();

            $query = $this->db->getQuery(true)
                ->select(
                    $this->db->quoteName(
                        [
                            'm.id',
                            'm.menutype',
                            'm.title',
                            'm.alias',
                            'm.note',
                            'm.link',
                            'm.type',
                            'm.level',
                            'm.language',
                            'm.browserNav',
                            'm.access',
                            'm.params',
                            'm.home',
                            'm.img',
                            'm.template_style_id',
                            'm.component_id',
                            'm.parent_id',
                        ]
                    )
                )
                ->select(
                    $this->db->quoteName(
                        [
                            'm.path',
                            'e.element',
                        ],
                        [
                            'route',
                            'component',
                        ]
                    )
                )
                ->from($this->db->quoteName('#__menu', 'm'))
                ->join(
                    'LEFT',
                    $this->db->quoteName('#__extensions', 'e'),
                    $this->db->quoteName('m.component_id') . ' = ' . $this->db->quoteName('e.extension_id')
                )
                ->where(
                    [
                        $this->db->quoteName('m.published') . ' = 1',
                        $this->db->quoteName('m.parent_id') . ' > 0',
                        $this->db->quoteName('m.client_id') . ' = 0',
                    ]
                )
                ->extendWhere(
                    'AND',
                    [
                        $this->db->quoteName('m.publish_up') . ' IS NULL',
                        $this->db->quoteName('m.publish_up') . ' <= :currentDate1',
                    ],
                    'OR'
                )
                ->bind(':currentDate1', $currentDate)
                ->extendWhere(
                    'AND',
                    [
                        $this->db->quoteName('m.publish_down') . ' IS NULL',
                        $this->db->quoteName('m.publish_down') . ' >= :currentDate2',
                    ],
                    'OR'
                )
                ->bind(':currentDate2', $currentDate)
                ->order($this->db->quoteName('m.lft'));

            $items    = [];
            $iterator = $this->db->setQuery($query)->getIterator();

            foreach ($iterator as $item) {
                $items[$item->id] = new MenuItem((array) $item);
            }

            return $items;
        };

        try {
            /** @var CallbackController $cache */
            $cache = $this->getCacheControllerFactory()->createCacheController('callback', ['defaultgroup' => 'com_menus']);

            $this->items = $cache->get($loader, [], md5(\get_class($this)), false);
        } catch (CacheExceptionInterface $e) {
            try {
                $this->items = $loader();
            } catch (ExecutionFailureException $databaseException) {
                $this->app->enqueueMessage(Text::sprintf('JERROR_LOADING_MENUS', $databaseException->getMessage()), 'warning');

                return false;
            }
        } catch (ExecutionFailureException $e) {
            $this->app->enqueueMessage(Text::sprintf('JERROR_LOADING_MENUS', $e->getMessage()), 'warning');

            return false;
        }

        foreach ($this->items as &$item) {
            // Get parent information.
            $parent_tree = [];

            if (isset($this->items[$item->parent_id])) {
                $item->setParent($this->items[$item->parent_id]);
                $parent_tree  = $this->items[$item->parent_id]->tree;
            }

            // Create tree.
            $parent_tree[] = $item->id;
            $item->tree    = $parent_tree;

            // Create the query array.
            $url = str_replace('index.php?', '', $item->link);
            $url = str_replace('&amp;', '&', $url);

            parse_str($url, $item->query);
        }

        return true;
    }

    /**
     * Gets menu items by attribute
     *
     * @param   string   $attributes  The field name
     * @param   string   $values      The value of the field
     * @param   boolean  $firstonly   If true, only returns the first item found
     *
     * @return  MenuItem|MenuItem[]  An array of menu item objects or a single object if the $firstonly parameter is true
     *
     * @since   1.6
     */
    public function getItems($attributes, $values, $firstonly = false)
    {
        $attributes = (array) $attributes;
        $values     = (array) $values;

        if ($this->app->isClient('site')) {
            // Filter by language if not set
            if (($key = array_search('language', $attributes)) === false) {
                if (Multilanguage::isEnabled()) {
                    $attributes[] = 'language';
                    $values[]     = [Factory::getLanguage()->getTag(), '*'];
                }
            } elseif ($values[$key] === null) {
                unset($attributes[$key], $values[$key]);
            }

            // Filter by access level if not set
            if (($key = array_search('access', $attributes)) === false) {
                $attributes[] = 'access';
                $values[]     = $this->user->getAuthorisedViewLevels();
            } elseif ($values[$key] === null) {
                unset($attributes[$key], $values[$key]);
            }
        }

        // Reset arrays or we get a notice if some values were unset
        $attributes = array_values($attributes);
        $values     = array_values($values);

        return parent::getItems($attributes, $values, $firstonly);
    }

    /**
     * Get menu item by id
     *
     * @param   string  $language  The language code.
     *
     * @return  MenuItem|null  The item object or null when not found for given language
     *
     * @since   1.6
     */
    public function getDefault($language = '*')
    {
        // Get menu items first to ensure defaults have been populated
        $items = $this->getMenu();

        if (\array_key_exists($language, $this->default) && $this->app->isClient('site') && $this->app->getLanguageFilter()) {
            return $items[$this->default[$language]];
        }

        if (\array_key_exists('*', $this->default)) {
            return $items[$this->default['*']];
        }
    }
}
