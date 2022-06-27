<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Utility class for tags
 *
 * @since  3.1
 */
abstract class Tag
{
    /**
     * Cached array of the tag items.
     *
     * @var    array
     * @since  3.1
     */
    protected static $items = array();

    /**
     * Returns an array of tags.
     *
     * @param   array  $config  An array of configuration options. By default, only
     *                          published and unpublished categories are returned.
     *
     * @return  array
     *
     * @since   3.1
     */
    public static function options($config = array('filter.published' => array(0, 1)))
    {
        $hash = md5(serialize($config));

        if (!isset(static::$items[$hash])) {
            $config = (array) $config;
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('a.id'),
                        $db->quoteName('a.title'),
                        $db->quoteName('a.level'),
                    ]
                )
                ->from($db->quoteName('#__tags', 'a'))
                ->where($db->quoteName('a.parent_id') . ' > 0');

            // Filter on the published state
            if (isset($config['filter.published'])) {
                if (is_numeric($config['filter.published'])) {
                    $query->where('a.published = :published')
                        ->bind(':published', $config['filter.published'], ParameterType::INTEGER);
                } elseif (is_array($config['filter.published'])) {
                    $config['filter.published'] = ArrayHelper::toInteger($config['filter.published']);
                    $query->whereIn($db->quoteName('a.published'), $config['filter.published']);
                }
            }

            // Filter on the language
            if (isset($config['filter.language'])) {
                if (is_string($config['filter.language'])) {
                    $query->where($db->quoteName('a.language') . ' = :language')
                        ->bind(':language', $config['filter.language']);
                } elseif (is_array($config['filter.language'])) {
                    $query->whereIn($db->quoteName('a.language'), $config['filter.language'], ParameterType::STRING);
                }
            }

            $query->order($db->quoteName('a.lft'));

            $db->setQuery($query);
            $items = $db->loadObjectList();

            // Assemble the list options.
            static::$items[$hash] = array();

            foreach ($items as &$item) {
                $repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
                $item->title = str_repeat('- ', $repeat) . $item->title;
                static::$items[$hash][] = HTMLHelper::_('select.option', $item->id, $item->title);
            }
        }

        return static::$items[$hash];
    }

    /**
     * Returns an array of tags.
     *
     * @param   array  $config  An array of configuration options. By default, only published and unpublished tags are returned.
     *
     * @return  array  Tag data
     *
     * @since   3.1
     */
    public static function tags($config = array('filter.published' => array(0, 1)))
    {
        $hash = md5(serialize($config));
        $config = (array) $config;
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.title'),
                    $db->quoteName('a.level'),
                    $db->quoteName('a.parent_id'),
                ]
            )
            ->from($db->quoteName('#__tags', 'a'))
            ->where($db->quoteName('a.parent_id') . ' > 0');

        // Filter on the published state
        if (isset($config['filter.published'])) {
            if (is_numeric($config['filter.published'])) {
                $query->where($db->quoteName('a.published') . ' = :published')
                    ->bind(':published', $config['filter.published'], ParameterType::INTEGER);
            } elseif (is_array($config['filter.published'])) {
                $config['filter.published'] = ArrayHelper::toInteger($config['filter.published']);
                $query->whereIn($db->quoteName('a.published'), $config['filter.published']);
            }
        }

        $query->order($db->quoteName('a.lft'));

        $db->setQuery($query);
        $items = $db->loadObjectList();

        // Assemble the list options.
        static::$items[$hash] = array();

        foreach ($items as &$item) {
            $repeat = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
            $item->title = str_repeat('- ', $repeat) . $item->title;
            static::$items[$hash][] = HTMLHelper::_('select.option', $item->id, $item->title);
        }

        return static::$items[$hash];
    }

    /**
     * This is just a proxy for the formbehavior.ajaxchosen method
     *
     * @param   string   $selector     DOM id of the tag field
     * @param   boolean  $allowCustom  Flag to allow custom values
     *
     * @return  void
     *
     * @since   3.1
     *
     * @deprecated  5.0  Without replacement
     */
    public static function ajaxfield($selector = '#jform_tags', $allowCustom = true)
    {
        // Get the component parameters
        $params = ComponentHelper::getParams('com_tags');
        $minTermLength = (int) $params->get('min_term_length', 3);

        Text::script('JGLOBAL_KEEP_TYPING');
        Text::script('JGLOBAL_LOOKING_FOR');

        // Include scripts
        HTMLHelper::_('behavior.core');
        HTMLHelper::_('jquery.framework');
        HTMLHelper::_('formbehavior.chosen');
        HTMLHelper::_('script', 'legacy/ajax-chosen.min.js', array('version' => 'auto', 'relative' => true));

        Factory::getDocument()->addScriptOptions(
            'ajax-chosen',
            array(
                'url'            => Uri::root() . 'index.php?option=com_tags&task=tags.searchAjax',
                'debug'          => JDEBUG,
                'selector'       => $selector,
                'type'           => 'GET',
                'dataType'       => 'json',
                'jsonTermKey'    => 'like',
                'afterTypeDelay' => 500,
                'minTermLength'  => $minTermLength
            )
        );

        // Allow custom values ?
        if ($allowCustom) {
            HTMLHelper::_('script', 'system/fields/tag.min.js', array('version' => 'auto', 'relative' => true));
            Factory::getDocument()->addScriptOptions(
                'field-tag-custom',
                array(
                    'minTermLength' => $minTermLength,
                    'selector'      => $selector,
                    'allowCustom'   => Factory::getUser()->authorise('core.create', 'com_tags') ? $allowCustom : false,
                )
            );
        }
    }
}
