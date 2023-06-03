<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Plugin;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Component\Privacy\Administrator\Export\Domain;
use Joomla\Component\Privacy\Administrator\Export\Field;
use Joomla\Component\Privacy\Administrator\Export\Item;
use Joomla\Database\DatabaseAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for privacy plugins
 *
 * @since  3.9.0
 */
abstract class PrivacyPlugin extends CMSPlugin
{
    use DatabaseAwareTrait;

    /**
     * Database object
     *
     * @var    \Joomla\Database\DatabaseDriver
     * @since  3.9.0
     * @deprecated  4.4.0 will be removed in 6.0 use $this->getDatabase() instead
     */
    protected $db;

    /**
     * Affects constructor behaviour. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.9.0
     */
    protected $autoloadLanguage = true;

    /**
     * Create a new domain object
     *
     * @param   string  $name         The domain's name
     * @param   string  $description  The domain's description
     *
     * @return  Domain
     *
     * @since   3.9.0
     */
    protected function createDomain($name, $description = '')
    {
        $domain              = new Domain();
        $domain->name        = $name;
        $domain->description = $description;

        return $domain;
    }

    /**
     * Create an item object for an array
     *
     * @param   array         $data    The array data to convert
     * @param   integer|null  $itemId  The ID of this item
     *
     * @return  Item
     *
     * @since   3.9.0
     */
    protected function createItemFromArray(array $data, $itemId = null)
    {
        $item     = new Item();
        $item->id = $itemId;

        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $value = (array) $value;
            }

            if (is_array($value)) {
                $value = print_r($value, true);
            }

            $field        = new Field();
            $field->name  = $key;
            $field->value = $value;

            $item->addField($field);
        }

        return $item;
    }

    /**
     * Create an item object for a Table object
     *
     * @param   Table  $table  The Table object to convert
     *
     * @return  Item
     *
     * @since   3.9.0
     */
    protected function createItemForTable($table)
    {
        $data = [];

        foreach (array_keys($table->getFields()) as $fieldName) {
            $data[$fieldName] = $table->$fieldName;
        }

        return $this->createItemFromArray($data, $table->{$table->getKeyName(false)});
    }

    /**
     * Helper function to create the domain for the items custom fields.
     *
     * @param   string  $context  The context
     * @param   array   $items    The items
     *
     * @return  Domain
     *
     * @since   3.9.0
     */
    protected function createCustomFieldsDomain($context, $items = [])
    {
        if (!is_array($items)) {
            $items = [$items];
        }

        $parts = FieldsHelper::extract($context);

        if (!$parts) {
            return [];
        }

        $type = str_replace('com_', '', $parts[0]);

        $domain = $this->createDomain($type . '_' . $parts[1] . '_custom_fields', 'joomla_' . $type . '_' . $parts[1] . '_custom_fields_data');

        foreach ($items as $item) {
            // Get item's fields, also preparing their value property for manual display
            $fields = FieldsHelper::getFields($parts[0] . '.' . $parts[1], $item);

            foreach ($fields as $field) {
                $fieldValue = is_array($field->value) ? implode(', ', $field->value) : $field->value;

                $data = [
                    $type . '_id' => $item->id,
                    'field_name'  => $field->name,
                    'field_title' => $field->title,
                    'field_value' => $fieldValue,
                ];

                $domain->addItem($this->createItemFromArray($data));
            }
        }

        return $domain;
    }
}
