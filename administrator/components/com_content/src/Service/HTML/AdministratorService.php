<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Service\HTML;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content HTML helper
 *
 * @since  3.0
 */
class AdministratorService
{
    /**
     * Render the list of associated items
     *
     * @param   integer  $articleid  The article item id
     *
     * @return  string  The language HTML
     *
     * @throws  \Exception
     */
    public function association($articleid)
    {
        // Defaults
        $html = '';

        // Get the associations
        if ($associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $articleid)) {
            foreach ($associations as $tag => $associated) {
                $associations[$tag] = (int) $associated->id;
            }

            // Get the associated menu items
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select(
                    [
                        'c.*',
                        $db->quoteName('l.sef', 'lang_sef'),
                        $db->quoteName('l.lang_code'),
                        $db->quoteName('cat.title', 'category_title'),
                        $db->quoteName('l.image'),
                        $db->quoteName('l.title', 'language_title'),
                    ]
                )
                ->from($db->quoteName('#__content', 'c'))
                ->join('LEFT', $db->quoteName('#__categories', 'cat'), $db->quoteName('cat.id') . ' = ' . $db->quoteName('c.catid'))
                ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('c.language') . ' = ' . $db->quoteName('l.lang_code'))
                ->whereIn($db->quoteName('c.id'), array_values($associations))
                ->where($db->quoteName('c.id') . ' != :articleId')
                ->bind(':articleId', $articleid, ParameterType::INTEGER);

            $db->setQuery($query);

            try {
                $items = $db->loadObjectList('id');
            } catch (\RuntimeException $e) {
                throw new \Exception($e->getMessage(), 500, $e);
            }

            if ($items) {
                $languages = LanguageHelper::getContentLanguages([0, 1]);
                $content_languages = array_column($languages, 'lang_code');

                foreach ($items as &$item) {
                    if (in_array($item->lang_code, $content_languages)) {
                        $text    = $item->lang_code;
                        $url     = Route::_('index.php?option=com_content&task=article.edit&id=' . (int) $item->id);
                        $tooltip = '<strong>' . htmlspecialchars($item->language_title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
                            . htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br>' . Text::sprintf('JCATEGORY_SPRINTF', $item->category_title);
                        $classes = 'badge bg-secondary';

                        $item->link = '<a href="' . $url . '" class="' . $classes . '">' . $text . '</a>'
                            . '<div role="tooltip" id="tip-' . (int) $articleid . '-' . (int) $item->id . '">' . $tooltip . '</div>';
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
}
