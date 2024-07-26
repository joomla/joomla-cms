<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Tags Component Association Helper
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class AssociationHelper
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
     * @since  __DEPLOY_VERSION__
     */
    public static function getAssociations($id = 0, $view = null, $layout = null)
    {
        $jinput     = Factory::getApplication()->getInput();
        $view       = $view ?? $jinput->get('view');
        $component  = $jinput->getCmd('option');
        $id         = empty($id) ? $jinput->getInt('id') : $id;
        $clanguages = LanguageHelper::getContentLanguages();

        if ($layout === null && $jinput->get('view') == $view && $component == 'com_tags') {
            $layout = $jinput->get('layout', '', 'string');
        }

        if ($view === 'tag') {
            if ($id) {
                $temp = [];

                foreach ($id as $i) {
                    $associations = Associations::getAssociations(
                        'com_tags',
                        '#__tags',
                        'com_tags.tag',
                        $i,
                        'id',
                        'alias',
                        null
                    );

                    foreach (array_keys($clanguages) as $lang) {
                        if (!isset($temp[$lang])) {
                            $temp[$lang] = [];
                        }

                        if (isset($associations[$lang])) {
                            $temp[$lang][] = $associations[$lang]->id;
                        } else {
                            $temp[$lang][] = $i;
                        }
                    }
                }

                $return = [];

                foreach ($temp as $tag => $item) {
                    $return[$tag] = RouteHelper::getComponentTagRoute($item, $tag);
                }

                return $return;
            }
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
     * @since  __DEPLOY_VERSION__
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
