<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Route Helper
 *
 * A class providing basic routing for urls that are for content types found in
 * the #__content_types table and rows found in the #__ucm_content table.
 *
 * @since  3.1
 */
class RouteHelper
{
    /**
     * @var    array  Holds the reverse lookup
     * @since  3.1
     */
    protected static $lookup;

    /**
     * @var    string  Option for the extension (such as com_content)
     * @since  3.1
     */
    protected $extension;

    /**
     * @var    string  Value of the primary key in the content type table
     * @since  3.1
     */
    protected $id;

    /**
     * @var    string  Name of the view for the url
     * @since  3.1
     */
    protected $view;

    /**
     * A method to get the route for a specific item
     *
     * @param   integer  $id         Value of the primary key for the item in its content table
     * @param   string   $typealias  The type_alias for the item being routed. Of the form extension.view.
     * @param   string   $link       The link to be routed
     * @param   string   $language   The language of the content for multilingual sites
     * @param   integer  $catid      Optional category id
     *
     * @return  string  The route of the item
     *
     * @since   3.1
     */
    public function getRoute($id, $typealias, $link = '', $language = null, $catid = null)
    {
        $typeExploded = explode('.', $typealias);

        if (isset($typeExploded[1])) {
            $this->view      = $typeExploded[1];
            $this->extension = $typeExploded[0];
        } else {
            $this->view      = Factory::getApplication()->getInput()->getString('view');
            $this->extension = Factory::getApplication()->getInput()->getCmd('option');
        }

        $name = ucfirst(substr_replace($this->extension, '', 0, 4));

        $needles = [];

        if (isset($this->view)) {
            $needles[$this->view] = [(int) $id];
        }

        if (empty($link)) {
            // Create the link
            $link = 'index.php?option=' . $this->extension . '&view=' . $this->view . '&id=' . $id;
        }

        if ($catid > 1) {
            $categories = Categories::getInstance($name);

            if ($categories) {
                $category = $categories->get((int) $catid);

                if ($category) {
                    $needles['category']   = array_reverse($category->getPath());
                    $needles['categories'] = $needles['category'];
                    $link .= '&catid=' . $catid;
                }
            }
        }

        // Deal with languages only if needed
        if (!empty($language) && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
            $needles['language'] = $language;
        }

        if ($item = $this->findItem($needles)) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }

    /**
     * Method to find the item in the menu structure
     *
     * @param   array  $needles  Array of lookup values
     *
     * @return  mixed
     *
     * @since   3.1
     */
    protected function findItem($needles = [])
    {
        $app      = Factory::getApplication();
        $menus    = $app->getMenu('site');
        $language = $needles['language'] ?? '*';

        // $this->extension may not be set if coming from a static method, check it
        if ($this->extension === null) {
            $this->extension = $app->getInput()->getCmd('option');
        }

        // Prepare the reverse lookup array.
        if (!isset(static::$lookup[$this->extension][$language])) {
            static::$lookup[$this->extension][$language] = [];

            $component = ComponentHelper::getComponent($this->extension);

            $attributes = ['component_id'];
            $values     = [$component->id];

            if ($language !== '*') {
                $attributes[] = 'language';
                $values[]     = [$needles['language'], '*'];
            }

            $items = $menus->getItems($attributes, $values);

            foreach ($items as $item) {
                if (isset($item->query['view'])) {
                    $view = $item->query['view'];

                    if (!isset(static::$lookup[$this->extension][$language][$view])) {
                        static::$lookup[$this->extension][$language][$view] = [];
                    }

                    if (isset($item->query['id'])) {
                        if (\is_array($item->query['id'])) {
                            $item->query['id'] = $item->query['id'][0];
                        }

                        /*
                         * Here it will become a bit tricky
                         * $language != * can override existing entries
                         * $language == * cannot override existing entries
                         */
                        if ($item->language !== '*' || !isset(static::$lookup[$this->extension][$language][$view][$item->query['id']])) {
                            static::$lookup[$this->extension][$language][$view][$item->query['id']] = $item->id;
                        }
                    }
                }
            }
        }

        if ($needles) {
            foreach ($needles as $view => $ids) {
                if (isset(static::$lookup[$this->extension][$language][$view])) {
                    foreach ($ids as $id) {
                        if (isset(static::$lookup[$this->extension][$language][$view][(int) $id])) {
                            return static::$lookup[$this->extension][$language][$view][(int) $id];
                        }
                    }
                }
            }
        }

        $active = $menus->getActive();

        if ($active && $active->component === $this->extension && ($active->language === '*' || !Multilanguage::isEnabled())) {
            return $active->id;
        }

        // If not found, return language specific home link
        $default = $menus->getDefault($language);

        return !empty($default->id) ? $default->id : null;
    }

    /**
     * Fetches the category route
     *
     * @param   mixed   $catid      Category ID or CategoryNode instance
     * @param   mixed   $language   Language code
     * @param   string  $extension  Extension to lookup
     *
     * @return  string
     *
     * @since   3.2
     *
     * @throws  \InvalidArgumentException
     */
    public static function getCategoryRoute($catid, $language = 0, $extension = '')
    {
        // Note: $extension is required but has to be an optional argument in the function call due to argument order
        if (empty($extension)) {
            throw new \InvalidArgumentException(\sprintf('$extension is a required argument in %s()', __METHOD__));
        }

        if ($catid instanceof CategoryNode) {
            $id       = $catid->id;
            $category = $catid;
        } else {
            $extensionName = ucfirst(substr($extension, 4));
            $id            = (int) $catid;
            $category      = Categories::getInstance($extensionName)->get($id);
        }

        if ($id < 1) {
            $link = '';
        } else {
            $link = 'index.php?option=' . $extension . '&view=category&id=' . $id;

            $needles = [
                'category' => [$id],
            ];

            if ($language && $language !== '*' && Multilanguage::isEnabled()) {
                $link .= '&lang=' . $language;
                $needles['language'] = $language;
            }

            // Create the link
            if ($category) {
                $catids                = array_reverse($category->getPath());
                $needles['category']   = $catids;
                $needles['categories'] = $catids;
            }

            if ($item = static::lookupItem($needles)) {
                $link .= '&Itemid=' . $item;
            }
        }

        return $link;
    }

    /**
     * Static alias to findItem() used to find the item in the menu structure
     *
     * @param   array  $needles  Array of lookup values
     *
     * @return  mixed
     *
     * @since   3.2
     */
    protected static function lookupItem($needles = [])
    {
        $instance = new static();

        return $instance->findItem($needles);
    }
}
