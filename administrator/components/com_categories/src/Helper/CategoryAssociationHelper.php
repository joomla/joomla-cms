<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Category Component Association Helper
 *
 * @since  3.0
 */
abstract class CategoryAssociationHelper
{
    /**
     * Flag if associations are present for categories
     *
     * @var    boolean
     * @since  3.0
     */
    public static $category_association = true;

    /**
     * Method to get the associations for a given category
     *
     * @param   int|int[]    $id         ID of the item or array of IDs
     * @param   string       $extension  Name of the component
     * @param   string|null  $layout     Category layout
     *
     * @return  array    Array of associations for the component categories, optionally grouped by ID
     *
     * @since  3.0
     */
    public static function getCategoryAssociations($id, $extension = 'com_content', $layout = null)
    {
        $return = [];

        if ($id) {
            $helperClassname = ucfirst(substr($extension, 4)) . 'HelperRoute';

            $associations = CategoriesHelper::getAssociations((array) $id, $extension);

            foreach ($associations as $itemId => $itemAssociations) {
                foreach ($itemAssociations as $tag => $item) {
                    if (class_exists($helperClassname) && \is_callable([$helperClassname, 'getCategoryRoute'])) {
                        $return[$itemId][$tag] = $helperClassname::getCategoryRoute($item, $tag, $layout);
                    } else {
                        $link = 'index.php?option=' . $extension . '&view=category&id=' . $item;

                        if ($tag && $tag !== '*') {
                            $link .= '&lang=' . $tag;
                        }

                        if ($layout) {
                            $link .= '&layout=' . $layout;
                        }

                        $return[$itemId][$tag] = $link;
                    }
                }
            }
        }

        return \is_array($id) ? $return : ($return[$id] ?? []);
    }
}
