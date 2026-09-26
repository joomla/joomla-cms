<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Service\HTML;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Finder\Administrator\Helper\LanguageHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML behavior class for Finder.
 *
 * @since  2.5
 */
class Finder
{
    use DatabaseAwareTrait;

    /**
     * Creates a list of types to filter on.
     *
     * @return  array  An array containing the types that can be selected.
     *
     * @since   2.5
     */
    public function typeslist()
    {
        // Load the finder types.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('DISTINCT t.title AS text, t.id AS value')
            ->from($db->quoteName('#__finder_types') . ' AS t')
            ->join('LEFT', $db->quoteName('#__finder_links') . ' AS l ON l.type_id = t.id')
            ->order('t.title ASC');
        $db->setQuery($query);

        try {
            $rows = $db->loadObjectList();
        } catch (\RuntimeException) {
            return [];
        }

        // Compile the options.
        $options = [];

        $lang = Factory::getLanguage();

        foreach ($rows as $row) {
            $key       = $lang->hasKey(LanguageHelper::branchPlural($row->text)) ? LanguageHelper::branchPlural($row->text) : $row->text;
            $options[] = HTMLHelper::_('select.option', $row->value, Text::sprintf('COM_FINDER_ITEM_X_ONLY', Text::_($key)));
        }

        return $options;
    }

    /**
     * Creates a list of maps.
     *
     * @return  array  An array containing the maps that can be selected.
     *
     * @since   2.5
     */
    public function mapslist()
    {
        // Load the finder types.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('title', 'text'))
            ->select($db->quoteName('id', 'value'))
            ->from($db->quoteName('#__finder_taxonomy'))
            ->where($db->quoteName('parent_id') . ' = 1');
        $db->setQuery($query);

        try {
            $branches = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // Translate.
        $lang = Factory::getLanguage();

        foreach ($branches as $branch) {
            $key                    = LanguageHelper::branchPlural($branch->text);
            $branch->translatedText = $lang->hasKey($key) ? Text::_($key) : $branch->text;
        }

        // Order by title.
        $branches = ArrayHelper::sortObjects($branches, 'translatedText', 1, true, true);

        // Compile the options.
        $options   = [];
        $options[] = HTMLHelper::_('select.option', '', Text::_('COM_FINDER_MAPS_SELECT_BRANCH'));

        // Convert the values to options.
        foreach ($branches as $branch) {
            $options[] = HTMLHelper::_('select.option', $branch->value, $branch->translatedText);
        }

        return $options;
    }

    /**
     * Creates a list of published states.
     *
     * @return  array  An array containing the states that can be selected.
     *
     * @since   2.5
     */
    public static function statelist()
    {
        return [
            HTMLHelper::_('select.option', '1', Text::sprintf('COM_FINDER_ITEM_X_ONLY', Text::_('JPUBLISHED'))),
            HTMLHelper::_('select.option', '0', Text::sprintf('COM_FINDER_ITEM_X_ONLY', Text::_('JUNPUBLISHED'))),
        ];
    }
}
