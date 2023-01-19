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
use Joomla\Database\ParameterType;

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
     * @param   integer  $pk         Content item key.
     * @param   string   $extension  Optional extension name.
     *
     * @return  array of associations.
     */
    public static function getAssociations($pk, $extension = 'com_content')
    {
        $langAssociations = Associations::getAssociations($extension, '#__categories', 'com_categories.item', $pk, 'id', 'alias', '');
        $associations     = [];
        $user             = Factory::getUser();
        $groups           = $user->getAuthorisedViewLevels();

        foreach ($langAssociations as $langAssociation) {
            // Include only published categories with user access
            $arrId   = explode(':', $langAssociation->id);
            $assocId = (int) $arrId[0];
            $db      = Factory::getDbo();

            $query = $db->getQuery(true)
                ->select($db->quoteName('published'))
                ->from($db->quoteName('#__categories'))
                ->whereIn($db->quoteName('access'), $groups)
                ->where($db->quoteName('id') . ' = :associd')
                ->bind(':associd', $assocId, ParameterType::INTEGER);

            $result = (int) $db->setQuery($query)->loadResult();

            if ($result === 1) {
                $associations[$langAssociation->language] = $langAssociation->id;
            }
        }

        return $associations;
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

        $data = [];
        $data['id'] = $catid;
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
