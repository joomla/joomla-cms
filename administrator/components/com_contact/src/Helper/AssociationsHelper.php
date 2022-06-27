<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Administrator\Helper;

use Joomla\CMS\Association\AssociationExtensionHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Table\Table;
use Joomla\Component\Contact\Site\Helper\AssociationHelper;

/**
 * Content associations helper.
 *
 * @since  3.7.0
 */
class AssociationsHelper extends AssociationExtensionHelper
{
    /**
     * The extension name
     *
     * @var     array   $extension
     *
     * @since   3.7.0
     */
    protected $extension = 'com_contact';

    /**
     * Array of item types
     *
     * @var     array   $itemTypes
     *
     * @since   3.7.0
     */
    protected $itemTypes = array('contact', 'category');

    /**
     * Has the extension association support
     *
     * @var     boolean   $associationsSupport
     *
     * @since   3.7.0
     */
    protected $associationsSupport = true;

    /**
     * Method to get the associations for a given item.
     *
     * @param   integer  $id    Id of the item
     * @param   string   $view  Name of the view
     *
     * @return  array   Array of associations for the item
     *
     * @since  4.0.0
     */
    public function getAssociationsForItem($id = 0, $view = null)
    {
        return AssociationHelper::getAssociations($id, $view);
    }

    /**
     * Get the associated items for an item
     *
     * @param   string  $typeName  The item type
     * @param   int     $id        The id of item for which we need the associated items
     *
     * @return  array
     *
     * @since   3.7.0
     */
    public function getAssociations($typeName, $id)
    {
        $type = $this->getType($typeName);

        $context    = $this->extension . '.item';
        $catidField = 'catid';

        if ($typeName === 'category') {
            $context    = 'com_categories.item';
            $catidField = '';
        }

        // Get the associations.
        $associations = Associations::getAssociations(
            $this->extension,
            $type['tables']['a'],
            $context,
            $id,
            'id',
            'alias',
            $catidField
        );

        return $associations;
    }

    /**
     * Get item information
     *
     * @param   string  $typeName  The item type
     * @param   int     $id        The id of item for which we need the associated items
     *
     * @return  Table|null
     *
     * @since   3.7.0
     */
    public function getItem($typeName, $id)
    {
        if (empty($id)) {
            return null;
        }

        $table = null;

        switch ($typeName) {
            case 'contact':
                $table = Table::getInstance('ContactTable', 'Joomla\\Component\\Contact\\Administrator\\Table\\');
                break;

            case 'category':
                $table = Table::getInstance('Category');
                break;
        }

        if (empty($table)) {
            return null;
        }

        $table->load($id);

        return $table;
    }

    /**
     * Get information about the type
     *
     * @param   string  $typeName  The item type
     *
     * @return  array  Array of item types
     *
     * @since   3.7.0
     */
    public function getType($typeName = '')
    {
        $fields  = $this->getFieldsTemplate();
        $tables  = array();
        $joins   = array();
        $support = $this->getSupportTemplate();
        $title   = '';

        if (in_array($typeName, $this->itemTypes)) {
            switch ($typeName) {
                case 'contact':
                    $fields['title'] = 'a.name';
                    $fields['state'] = 'a.published';

                    $support['state'] = true;
                    $support['acl'] = true;
                    $support['checkout'] = true;
                    $support['category'] = true;
                    $support['save2copy'] = true;

                    $tables = array(
                        'a' => '#__contact_details'
                    );

                    $title = 'contact';
                    break;

                case 'category':
                    $fields['created_user_id'] = 'a.created_user_id';
                    $fields['ordering'] = 'a.lft';
                    $fields['level'] = 'a.level';
                    $fields['catid'] = '';
                    $fields['state'] = 'a.published';

                    $support['state'] = true;
                    $support['acl'] = true;
                    $support['checkout'] = true;
                    $support['level'] = true;

                    $tables = array(
                        'a' => '#__categories'
                    );

                    $title = 'category';
                    break;
            }
        }

        return array(
            'fields'  => $fields,
            'support' => $support,
            'tables'  => $tables,
            'joins'   => $joins,
            'title'   => $title
        );
    }
}
