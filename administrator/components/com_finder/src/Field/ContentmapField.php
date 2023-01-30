<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Finder\Administrator\Helper\LanguageHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports a select grouped list of finder content map.
 *
 * @since  3.6.0
 */
class ContentmapField extends GroupedlistField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.6.0
     */
    public $type = 'ContentMap';

    /**
     * Method to get the list of content map options grouped by first level.
     *
     * @return  array  The field option objects as a nested array in groups.
     *
     * @since   3.6.0
     */
    protected function getGroups()
    {
        $groups = [];

        // Get the database object and a new query object.
        $db = $this->getDatabase();

        // Main query.
        $query = $db->getQuery(true)
            ->select($db->quoteName('a.title', 'text'))
            ->select($db->quoteName('a.id', 'value'))
            ->select($db->quoteName('a.parent_id'))
            ->select($db->quoteName('a.level'))
            ->from($db->quoteName('#__finder_taxonomy', 'a'))
            ->where($db->quoteName('a.parent_id') . ' <> 0')
            ->order('a.title ASC');

        $db->setQuery($query);

        try {
            $contentMap = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            return [];
        }

        // Build the grouped list array.
        if ($contentMap) {
            $parents = [];

            foreach ($contentMap as $item) {
                if (!isset($parents[$item->parent_id])) {
                    $parents[$item->parent_id] = [];
                }

                $parents[$item->parent_id][] = $item;
            }

            foreach ($parents[1] as $branch) {
                $text = Text::_(LanguageHelper::branchSingular($branch->text));
                $groups[$text] = $this->prepareLevel($branch->value, $parents);
            }
        }

        // Merge any additional groups in the XML definition.
        $groups = array_merge(parent::getGroups(), $groups);

        return $groups;
    }

    /**
     * Indenting and translating options for the list
     *
     * @param   int    $parent   Parent ID to process
     * @param   array  $parents  Array of arrays of items with parent IDs as keys
     *
     * @return  array  The indented list of entries for this branch
     *
     * @since   4.1.5
     */
    private function prepareLevel($parent, $parents)
    {
        $lang = Factory::getLanguage();
        $entries = [];

        foreach ($parents[$parent] as $item) {
            $levelPrefix = str_repeat('- ', $item->level - 1);

            if (trim($item->text, '*') === 'Language') {
                $text = LanguageHelper::branchLanguageTitle($item->text);
            } else {
                $key = LanguageHelper::branchSingular($item->text);
                $text = $lang->hasKey($key) ? Text::_($key) : $item->text;
            }

            $entries[] = HTMLHelper::_('select.option', $item->value, $levelPrefix . $text);

            if (isset($parents[$item->value])) {
                $entries = array_merge($entries, $this->prepareLevel($item->value, $parents));
            }
        }

        return $entries;
    }
}
