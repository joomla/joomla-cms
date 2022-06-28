<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Service\HTML;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Menus HTML helper class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 * @since       1.7
 */
class Menus
{
    /**
     * Generate the markup to display the item associations
     *
     * @param   int  $itemid  The menu item id
     *
     * @return  string
     *
     * @since   3.0
     *
     * @throws \Exception If there is an error on the query
     */
    public function association($itemid)
    {
        // Defaults
        $html = '';

        // Get the associations
        if ($associations = MenusHelper::getAssociations($itemid)) {
            // Get the associated menu items
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('m.id'),
                        $db->quoteName('m.title'),
                        $db->quoteName('l.sef', 'lang_sef'),
                        $db->quoteName('l.lang_code'),
                        $db->quoteName('mt.title', 'menu_title'),
                        $db->quoteName('l.image'),
                        $db->quoteName('l.title', 'language_title'),
                    ]
                )
                ->from($db->quoteName('#__menu', 'm'))
                ->join('LEFT', $db->quoteName('#__menu_types', 'mt'), $db->quoteName('mt.menutype') . ' = ' . $db->quoteName('m.menutype'))
                ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('m.language') . ' = ' . $db->quoteName('l.lang_code'))
                ->whereIn($db->quoteName('m.id'), array_values($associations))
                ->where($db->quoteName('m.id') . ' != :itemid')
                ->bind(':itemid', $itemid, ParameterType::INTEGER);
            $db->setQuery($query);

            try {
                $items = $db->loadObjectList('id');
            } catch (\RuntimeException $e) {
                throw new \Exception($e->getMessage(), 500);
            }

            // Construct html
            if ($items) {
                $languages = LanguageHelper::getContentLanguages(array(0, 1));
                $content_languages = array_column($languages, 'lang_code');

                foreach ($items as &$item) {
                    if (in_array($item->lang_code, $content_languages)) {
                        $text    = $item->lang_code;
                        $url     = Route::_('index.php?option=com_menus&task=item.edit&id=' . (int) $item->id);
                        $tooltip = '<strong>' . htmlspecialchars($item->language_title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
                            . htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('COM_MENUS_MENU_SPRINTF', $item->menu_title);
                        $classes = 'badge bg-secondary';

                        $item->link = '<a href="' . $url . '" class="' . $classes . '">' . $text . '</a>'
                            . '<div role="tooltip" id="tip-' . (int) $itemid . '-' . (int) $item->id . '">' . $tooltip . '</div>';
                    } else {
                        // Display warning if Content Language is trashed or deleted
                        Factory::getApplication()->enqueueMessage(Text::sprintf('JGLOBAL_ASSOCIATIONS_CONTENTLANGUAGE_WARNING', $item->lang_code), 'warning');
                    }
                }
            }

            $html = LayoutHelper::render('joomla.content.associations', $items);
        }

        return $html;
    }

    /**
     * Returns a visibility state on a grid
     *
     * @param   integer  $params  Params of item.
     *
     * @return  string  The Html code
     *
     * @since   3.7.0
     */
    public function visibility($params)
    {
        $registry = new Registry();

        try {
            $registry->loadString($params);
        } catch (\Exception $e) {
            // Invalid JSON
        }

        $show_menu = $registry->get('menu_show');

        return ($show_menu === 0) ? '<span class="badge bg-secondary">' . Text::_('COM_MENUS_LABEL_HIDDEN') . '</span>' : '';
    }
}
