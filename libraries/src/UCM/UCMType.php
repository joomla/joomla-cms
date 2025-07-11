<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\UCM;

use Joomla\Application\AbstractApplication;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * UCM Class for handling content types
 *
 * @property-read  string  $core_content_id
 * @property-read  string  $core_type_alias
 * @property-read  string  $core_title
 * @property-read  string  $core_alias
 * @property-read  string  $core_body
 * @property-read  string  $core_state
 *
 * @property-read  string  $core_checked_out_time
 * @property-read  string  $core_checked_out_user_id
 * @property-read  string  $core_access
 * @property-read  string  $core_params
 * @property-read  string  $core_featured
 * @property-read  string  $core_metadata
 * @property-read  string  $core_created_user_id
 * @property-read  string  $core_created_by_alias
 * @property-read  string  $core_created_time
 * @property-read  string  $core_modified_user_id
 * @property-read  string  $core_modified_time
 * @property-read  string  $core_language
 * @property-read  string  $core_publish_up
 * @property-read  string  $core_publish_down
 * @property-read  string  $core_content_item_id
 * @property-read  string  $asset_id
 * @property-read  string  $core_images
 * @property-read  string  $core_urls
 * @property-read  string  $core_hits
 * @property-read  string  $core_version
 * @property-read  string  $core_ordering
 * @property-read  string  $core_metakey
 * @property-read  string  $core_metadesc
 * @property-read  string  $core_catid
 * @property-read  string  $core_typeid
 *
 * @since  3.1
 * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0 without replacement
 */
class UCMType implements UCM
{
    /**
     * The UCM Type
     *
     * @var    UCMType
     * @since  3.1
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0 without replacement
     */
    public $type;

    /**
     * The Database object
     *
     * @var    DatabaseDriver
     * @since  3.1
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0 without replacement
     */
    protected $db;

    /**
     * The alias for the content type
     *
     * @var    string
     * @since  3.1
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0 without replacement
     */
    protected $alias;

    /**
     * Class constructor
     *
     * @param   string                $alias        The alias for the item
     * @param   ?DatabaseDriver       $database     The database object
     * @param   ?AbstractApplication  $application  The application object
     *
     * @since   3.1
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0 without replacement
     */
    public function __construct($alias = null, ?DatabaseDriver $database = null, ?AbstractApplication $application = null)
    {
        $this->db = $database ?: Factory::getDbo();
        $app      = $application ?: Factory::getApplication();

        // Make the best guess we can in the absence of information.
        $this->alias = $alias ?: $app->getInput()->get('option') . '.' . $app->getInput()->get('view');
        $this->type  = $this->getTypeByAlias($this->alias);
    }

    /**
     * Get the Content Type
     *
     * @param   integer  $pk  The primary key of the alias type
     *
     * @return  object  The UCM Type data
     *
     * @since   3.1
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0 without replacement
     */
    public function getType($pk = null)
    {
        if (!$pk) {
            return $this->getTypeByAlias($this->alias);
        }

        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('ct') . '.*')
            ->from($this->db->quoteName('#__content_types', 'ct'))
            ->where($this->db->quoteName('ct.type_id') . ' = :pk')
            ->bind(':pk', $pk, ParameterType::INTEGER);

        $this->db->setQuery($query);

        return $this->db->loadObject();
    }

    /**
     * Get the Content Type from the alias
     *
     * @param   string  $typeAlias  The alias for the type
     *
     * @return  object  The UCM Type data
     *
     * @since   3.2
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0 without replacement
     */
    public function getTypeByAlias($typeAlias = null)
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('ct') . '.*')
            ->from($this->db->quoteName('#__content_types', 'ct'))
            ->where($this->db->quoteName('ct.type_alias') . ' = :alias')
            ->bind(':alias', $typeAlias);

        $this->db->setQuery($query);

        return $this->db->loadObject();
    }

    /**
     * Get the Content Type from the table class name
     *
     * @param   string  $tableName  The table for the type
     *
     * @return  mixed  The UCM Type data if found, false if no match is found
     *
     * @since   3.2
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0 without replacement
     */
    public function getTypeByTable($tableName)
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('ct') . '.*')
            ->from($this->db->quoteName('#__content_types', 'ct'));

        $this->db->setQuery($query);
        $types = $this->db->loadObjectList();

        foreach ($types as $type) {
            $tableFromType     = json_decode($type->table);
            $tableNameFromType = $tableFromType->special->prefix . $tableFromType->special->type;

            if ($tableNameFromType === $tableName) {
                return $type;
            }
        }

        return false;
    }

    /**
     * Retrieves the UCM type ID
     *
     * @param   string  $alias  The string of the type alias
     *
     * @return  mixed  The ID of the requested type or false if type is not found
     *
     * @since   3.1
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0 without replacement
     */
    public function getTypeId($alias = null)
    {
        if (!$alias) {
            $alias = $this->alias;
        }

        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('ct.type_id'))
            ->from($this->db->quoteName('#__content_types', 'ct'))
            ->where($this->db->quoteName('ct.type_alias') . ' = :alias')
            ->bind(':alias', $alias);

        $this->db->setQuery($query);

        $id = $this->db->loadResult();

        if (!$id) {
            return false;
        }

        return $id;
    }

    /**
     * Method to expand the field mapping
     *
     * @param   boolean  $assoc  True to return an associative array.
     *
     * @return  mixed  Array or object with field mappings. Defaults to object.
     *
     * @since   3.2
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0 without replacement
     */
    public function fieldmapExpand($assoc = false)
    {
        if (!empty($this->type->field_mappings)) {
            return $this->fieldmap = json_decode($this->type->field_mappings, $assoc);
        }

        return false;
    }

    /**
     * Magic method to get the name of the field mapped to an ucm field (core_something).
     *
     * @param   string  $ucmField  The name of the field in \Joomla\CMS\Table\CoreContent
     *
     * @return  string  The name mapped to the $ucmField for a given content type
     *
     * @since   3.2
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0 without replacement
     */
    public function __get($ucmField)
    {
        if (!isset($this->fieldmap)) {
            $this->fieldmapExpand(false);
        }

        return $this->fieldmap->common->$ucmField ?? null;
    }
}
