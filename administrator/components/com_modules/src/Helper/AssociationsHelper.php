<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Helper;

use Joomla\CMS\Association\AssociationExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Table\Module;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Module associations helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class AssociationsHelper extends AssociationExtensionHelper
{
    /**
     * The extension name
     *
     * @var     array   $extension
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $extension = 'com_modules';

    /**
     * Array of item types
     *
     * @var     array   $itemTypes
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $itemTypes = ['module'];

    /**
     * Has the extension association support
     *
     * @var     boolean   $associationsSupport
     *
     * @since   __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION_
     */
    public function getAssociationsForItem($id = 0, $view = null)
    {
        return $this->getAssociations('item', $id);
    }

    /**
     * Get the associated items for an item
     *
     * @param   string  $typeName  The item type
     * @param   int     $id        The id of item for which we need the associated items
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getAssociations($typeName, $id)
    {
        $type    = $this->getType($typeName);
        $context = $this->extension . '.item';

        // Get the associations.
        $associations = Associations::getAssociations(
            $this->extension,
            $type['tables']['a'],
            $context,
            $id,
            'id',
            '',
            ''
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
     * @since   __DEPLOY_VERSION__
     */
    public function getItem($typeName, $id)
    {
        if (empty($id)) {
            return null;
        }

        $table = new Module(Factory::getDbo());

        if (\is_null($table)) {
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
     * @since   __DEPLOY_VERSION__
     */
    public function getType($typeName = '')
    {
        $fields          = $this->getFieldsTemplate();
        $fields['state'] = 'a.published';
        $fields['catid'] = '';

        // Next line looks odd, and it is, it is because of a bug. I am going to make another PR to fix it.
        // We should be able to set the alias field to empty
        $fields['alias']           = 'a.title';
        $fields['created_user_id'] = '';

        $tables  = ['a' => '#__modules'];
        $joins   = [];

        $support              = $this->getSupportTemplate();
        $support['state']     = true;
        $support['acl']       = true;
        $support['checkout']  = true;
        $support['save2copy'] = false;

        $title   = 'module';

        $urlOptions = $this->getUrlOptions();

        return [
            'fields'     => $fields,
            'support'    => $support,
            'tables'     => $tables,
            'joins'      => $joins,
            'title'      => $title,
            'urlOptions' => $urlOptions,
        ];
    }

    /**
     * Get options we need for the associations side by side view
     *
     * @param   string  $type   The item type
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getUrlOptions($type = '')
    {
        return [
            'eid' => [
                'functionName' => 'getExtensionId',
                'params'       => [
                    'id',
                ],
            ],
        ];
    }

    /**
     * Method to retrieve the title of selected item.
     *
     * @return string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getExtensionId($id)
    {
        $data   = $this->getItem('module', $id);
        $module = $data->module;
        $eid    = 0;

        if ($module) {
            try {
                $db    = Factory::getDbo();
                $query = $db->createQuery()
                    ->select($db->quoteName('extension_id'))
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('element') . ' = :module')
                    ->bind(':module', $module, ParameterType::STRING);
                $db->setQuery($query);

                $eid = $db->loadResult();
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $eid;
    }
}
