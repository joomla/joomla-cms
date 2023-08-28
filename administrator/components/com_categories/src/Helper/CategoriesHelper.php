<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Table\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Categories helper.
 *
 * @since  1.6
 */
class CategoriesHelper
{
    /**
     * Gets a list of associations for a given item.
     *
     * @param   int|int[]  $pk         Category ID or array of IDs
     * @param   string     $extension  Optional extension name.
     *
     * @return  array of associations, optionally grouped by ID
     */
    public static function getAssociations($pk, $extension = 'com_content')
    {
        // To avoid doing duplicate database queries.
        static $multilanguageAssociations = [];

        // Multilanguage association array key. If the key is already in the array we don't need to run the query again, just return it.
        $queryKey = md5(serialize(array_merge([$extension, print_r($pk, true)])));

        if (!isset($multilanguageAssociations[$queryKey])) {
            $multilanguageAssociations[$queryKey] = [];

            $user   = Factory::getUser();
            $groups = implode(',', $user->getAuthorisedViewLevels());

            $advClause = [];

            // Filter by user groups
            $advClause[] = 'c2.access IN (' . $groups . ')';

            // Filter by published categories
            $advClause[] = 'c2.published = 1';

            $associations = Associations::getAssociations($extension, '#__categories', 'com_categories.item', (array) $pk, 'id', 'alias', '', $advClause);

            foreach ($associations as $itemId => $langAssociations) {
                foreach ($langAssociations as $langAssociation) {
                    $multilanguageAssociations[$queryKey][$itemId][$langAssociation->language] = $langAssociation->id;
                }
            }
        }

        return is_array($pk) ? $multilanguageAssociations[$queryKey] : ($multilanguageAssociations[$queryKey][$pk] ?? []);
    }

    /**
     * Check if Category ID exists otherwise assign to ROOT category.
     *
     * @param   mixed   $catid      Name or ID of category.
     * @param   string  $extension  Extension that triggers this function
     *
     * @return  integer  $catid  Category ID.
     */
    public static function validateCategoryId($catid, $extension)
    {
        $categoryTable = Table::getInstance('CategoryTable', '\\Joomla\\Component\\Categories\\Administrator\\Table\\');

        $data              = [];
        $data['id']        = $catid;
        $data['extension'] = $extension;

        if (!$categoryTable->load($data)) {
            $catid = 0;
        }

        return (int) $catid;
    }

    /**
     * Create new Category from within item view.
     *
     * @param   array  $data  Array of data for new category.
     *
     * @return  integer
     */
    public static function createCategory($data)
    {
        $categoryModel = Factory::getApplication()->bootComponent('com_categories')
            ->getMVCFactory()->createModel('Category', 'Administrator', ['ignore_request' => true]);
        $categoryModel->save($data);

        $catid = $categoryModel->getState('category.id');

        return $catid;
    }
}
