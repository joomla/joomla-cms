<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Component\Categories\Administrator\Helper\CategoryAssociationHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content Component Association Helper
 *
 * @since  3.0
 */
abstract class AssociationHelper extends CategoryAssociationHelper
{
    /**
     * Method to get the associations for a given item
     *
     * @param   integer  $id      Id of the item
     * @param   string   $view    Name of the view
     * @param   string   $layout  View layout
     *
     * @return  array   Array of associations for the item
     *
     * @since  3.0
     */
    public static function getAssociations($id = 0, $view = null, $layout = null)
    {
        $jinput    = Factory::getApplication()->getInput();
        $view      ??= $jinput->get('view');
        $component = $jinput->getCmd('option');
        $id        = empty($id) ? $jinput->getInt('id') : $id;

        if ($layout === null && $jinput->get('view') == $view && $component == 'com_content') {
            $layout = $jinput->get('layout', '', 'string');
        }

        if ($view === 'article') {
            if ($id) {
                $user      = Factory::getUser();
                $groups    = implode(',', $user->getAuthorisedViewLevels());
                $db        = Factory::getDbo();
                $advClause = [];

                // Filter by user groups
                $advClause[] = 'c2.access IN (' . $groups . ')';

                // Filter by current language
                $advClause[] = 'c2.language != ' . $db->quote(Factory::getLanguage()->getTag());

                if (!$user->authorise('core.edit.state', 'com_content') && !$user->authorise('core.edit', 'com_content')) {
                    // Filter by start and end dates.
                    $date = Factory::getDate();

                    $nowDate = $db->quote($date->toSql());

                    $advClause[] = '(c2.publish_up IS NULL OR c2.publish_up <= ' . $nowDate . ')';
                    $advClause[] = '(c2.publish_down IS NULL OR c2.publish_down >= ' . $nowDate . ')';

                    // Filter by published
                    $advClause[] = 'c2.state = 1';
                }

                $associations = Associations::getAssociations(
                    'com_content',
                    '#__content',
                    'com_content.item',
                    $id,
                    'id',
                    'alias',
                    'catid',
                    $advClause
                );

                $return = [];

                foreach ($associations as $tag => $item) {
                    $return[$tag] = RouteHelper::getArticleRoute($item->id, (int) $item->catid, $item->language, $layout);
                }

                return $return;
            }
        }

        if ($view === 'category' || $view === 'categories') {
            return self::getCategoryAssociations($id, 'com_content', $layout);
        }

        return [];
    }

    /**
     * Method to display in frontend the associations for a given article
     *
     * @param   integer  $id  Id of the article
     *
     * @return  array  An array containing the association URL and the related language object
     *
     * @since  3.7.0
     */
    public static function displayAssociations($id)
    {
        $return = [];

        if ($associations = self::getAssociations($id, 'article')) {
            $levels    = Factory::getUser()->getAuthorisedViewLevels();
            $languages = LanguageHelper::getLanguages();

            foreach ($languages as $language) {
                // Do not display language when no association
                if (empty($associations[$language->lang_code])) {
                    continue;
                }

                // Do not display language without frontend UI
                if (!\array_key_exists($language->lang_code, LanguageHelper::getInstalledLanguages(0))) {
                    continue;
                }

                // Do not display language without specific home menu
                if (!\array_key_exists($language->lang_code, Multilanguage::getSiteHomePages())) {
                    continue;
                }

                // Do not display language without authorized access level
                if (isset($language->access) && $language->access && !\in_array($language->access, $levels)) {
                    continue;
                }

                $return[$language->lang_code] = ['item' => $associations[$language->lang_code], 'language' => $language];
            }
        }

        return $return;
    }
}
