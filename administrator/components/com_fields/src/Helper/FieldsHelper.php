<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Fields\FieldsServiceInterface;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Fields\Administrator\Model\FieldModel;
use Joomla\Component\Fields\Administrator\Model\FieldsModel;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * FieldsHelper
 *
 * @since  3.7.0
 */
class FieldsHelper
{
    /**
     * @var    FieldsModel
     */
    private static $fieldsCache = null;

    /**
     * @var    FieldModel
     */
    private static $fieldCache = null;

    /**
     * Extracts the component and section from the context string which has to
     * be in the format component.context.
     *
     * @param   string  $contextString  contextString
     * @param   object  $item           optional item object
     *
     * @return  array|null
     *
     * @since   3.7.0
     */
    public static function extract($contextString, $item = null)
    {
        if ($contextString === null) {
            return null;
        }

        $parts = explode('.', $contextString, 2);

        if (count($parts) < 2) {
            return null;
        }

        $newSection = '';

        $component = Factory::getApplication()->bootComponent($parts[0]);

        if ($component instanceof FieldsServiceInterface) {
            $newSection = $component->validateSection($parts[1], $item);
        }

        if ($newSection) {
            $parts[1] = $newSection;
        }

        return $parts;
    }

    /**
     * Returns the fields for the given context.
     * If the item is an object the returned fields do have an additional field
     * "value" which represents the value for the given item. If the item has an
     * assigned_cat_ids field, then additionally fields which belong to that
     * category will be returned.
     * Should the value being prepared to be shown in an HTML context then
     * prepareValue must be set to true. No further escaping needs to be done.
     * The values of the fields can be overridden by an associative array where the keys
     * have to be a name and its corresponding value.
     *
     * @param   string             $context              The context of the content passed to the helper
     * @param   object|array|null  $item                 The item being edited in the form
     * @param   int|bool           $prepareValue         (if int is display event): 1 - AfterTitle, 2 - BeforeDisplay, 3 - AfterDisplay, 0 - OFF
     * @param   array|null         $valuesToOverride     The values to override
     * @param   bool               $includeSubformFields Should I include fields marked as Only Use In Subform?
     *
     * @return  array
     *
     * @throws \Exception
     * @since   3.7.0
     */
    public static function getFields(
        $context,
        $item = null,
        $prepareValue = false,
        array $valuesToOverride = null,
        bool $includeSubformFields = false
    ) {
        if (self::$fieldsCache === null) {
            // Load the model
            self::$fieldsCache = Factory::getApplication()->bootComponent('com_fields')
                ->getMVCFactory()->createModel('Fields', 'Administrator', ['ignore_request' => true]);

            self::$fieldsCache->setState('filter.state', 1);
            self::$fieldsCache->setState('list.limit', 0);
        }

        if ($includeSubformFields) {
            self::$fieldsCache->setState('filter.only_use_in_subform', '');
        } else {
            self::$fieldsCache->setState('filter.only_use_in_subform', 0);
        }

        if (is_array($item)) {
            $item = (object) $item;
        }

        if (Multilanguage::isEnabled() && isset($item->language) && $item->language != '*') {
            self::$fieldsCache->setState('filter.language', ['*', $item->language]);
        }

        self::$fieldsCache->setState('filter.context', $context);
        self::$fieldsCache->setState('filter.assigned_cat_ids', []);

        /*
         * If item has assigned_cat_ids parameter display only fields which
         * belong to the category
         */
        if ($item && (isset($item->catid) || isset($item->fieldscatid))) {
            $assignedCatIds = $item->catid ?? $item->fieldscatid;

            if (!is_array($assignedCatIds)) {
                $assignedCatIds = explode(',', $assignedCatIds);
            }

            // Fields without any category assigned should show as well
            $assignedCatIds[] = 0;

            self::$fieldsCache->setState('filter.assigned_cat_ids', $assignedCatIds);
        }

        $fields = self::$fieldsCache->getItems();

        if ($fields === false) {
            return [];
        }

        if ($item && isset($item->id)) {
            if (self::$fieldCache === null) {
                self::$fieldCache = Factory::getApplication()->bootComponent('com_fields')
                    ->getMVCFactory()->createModel('Field', 'Administrator', ['ignore_request' => true]);
            }

            $fieldIds = array_map(
                function ($f) {
                    return $f->id;
                },
                $fields
            );

            $fieldValues = self::$fieldCache->getFieldValues($fieldIds, $item->id);

            $new = [];

            foreach ($fields as $key => $original) {
                /*
                 * Doing a clone, otherwise fields for different items will
                 * always reference to the same object
                 */
                $field = clone $original;

                if ($valuesToOverride && array_key_exists($field->name, $valuesToOverride)) {
                    $field->value = $valuesToOverride[$field->name];
                } elseif ($valuesToOverride && array_key_exists($field->id, $valuesToOverride)) {
                    $field->value = $valuesToOverride[$field->id];
                } elseif (array_key_exists($field->id, $fieldValues)) {
                    $field->value = $fieldValues[$field->id];
                }

                if (!isset($field->value) || $field->value === '') {
                    $field->value = $field->default_value;
                }

                $field->rawvalue = $field->value;

                // If boolean prepare, if int, it is the event type: 1 - After Title, 2 - Before Display Content, 3 - After Display Content, 0 - Do not prepare
                if ($prepareValue && (is_bool($prepareValue) || $prepareValue === (int) $field->params->get('display', '2'))) {
                    PluginHelper::importPlugin('fields');

                    /*
                     * On before field prepare
                     * Event allow plugins to modify the output of the field before it is prepared
                     */
                    Factory::getApplication()->triggerEvent('onCustomFieldsBeforePrepareField', [$context, $item, &$field]);

                    // Gathering the value for the field
                    $value = Factory::getApplication()->triggerEvent('onCustomFieldsPrepareField', [$context, $item, &$field]);

                    if (is_array($value)) {
                        $value = implode(' ', $value);
                    }

                    /*
                     * On after field render
                     * Event allows plugins to modify the output of the prepared field
                     */
                    Factory::getApplication()->triggerEvent('onCustomFieldsAfterPrepareField', [$context, $item, $field, &$value]);

                    // Assign the value
                    $field->value = $value;
                }

                $new[$key] = $field;
            }

            $fields = $new;
        }

        return $fields;
    }

    /**
     * Renders the layout file and data on the context and does a fall back to
     * Fields afterwards.
     *
     * @param   string  $context      The context of the content passed to the helper
     * @param   string  $layoutFile   layoutFile
     * @param   array   $displayData  displayData
     *
     * @return  NULL|string
     *
     * @since  3.7.0
     */
    public static function render($context, $layoutFile, $displayData)
    {
        $value = '';

        /*
         * Because the layout refreshes the paths before the render function is
         * called, so there is no way to load the layout overrides in the order
         * template -> context -> fields.
         * If there is no override in the context then we need to call the
         * layout from Fields.
         */
        if ($parts = self::extract($context)) {
            // Trying to render the layout on the component from the context
            $value = LayoutHelper::render($layoutFile, $displayData, null, ['component' => $parts[0], 'client' => 0]);
        }

        if ($value == '') {
            // Trying to render the layout on Fields itself
            $value = LayoutHelper::render($layoutFile, $displayData, null, ['component' => 'com_fields','client' => 0]);
        }

        return $value;
    }

    /**
     * PrepareForm
     *
     * @param   string  $context  The context of the content passed to the helper
     * @param   Form    $form     form
     * @param   object  $data     data.
     *
     * @return  boolean
     *
     * @since   3.7.0
     */
    public static function prepareForm($context, Form $form, $data)
    {
        // Extracting the component and section
        $parts = self::extract($context);

        if (! $parts) {
            return true;
        }

        $context = $parts[0] . '.' . $parts[1];

        // When no fields available return here
        $fields = self::getFields($parts[0] . '.' . $parts[1], new CMSObject());

        if (! $fields) {
            return true;
        }

        $component = $parts[0];
        $section   = $parts[1];

        $assignedCatids = $data->catid ?? $data->fieldscatid ?? $form->getValue('catid');

        // Account for case that a submitted form has a multi-value category id field (e.g. a filtering form), just use the first category
        $assignedCatids = is_array($assignedCatids)
            ? (int) reset($assignedCatids)
            : (int) $assignedCatids;

        if (!$assignedCatids && $formField = $form->getField('catid')) {
            $assignedCatids = $formField->getAttribute('default', null);

            if (!$assignedCatids) {
                // Choose the first category available
                $catOptions = $formField->options;

                if ($catOptions && !empty($catOptions[0]->value)) {
                    $assignedCatids = (int) $catOptions[0]->value;
                }
            }

            $data->fieldscatid = $assignedCatids;
        }

        /*
         * If there is a catid field we need to reload the page when the catid
         * is changed
         */
        if ($form->getField('catid') && $parts[0] != 'com_fields') {
            /*
             * Setting some parameters for the category field
             */
            $form->setFieldAttribute('catid', 'refresh-enabled', true);
            $form->setFieldAttribute('catid', 'refresh-cat-id', $assignedCatids);
            $form->setFieldAttribute('catid', 'refresh-section', $section);
        }

        // Getting the fields
        $fields = self::getFields($parts[0] . '.' . $parts[1], $data);

        if (!$fields) {
            return true;
        }

        $fieldTypes = self::getFieldTypes();

        // Creating the dom
        $xml        = new \DOMDocument('1.0', 'UTF-8');
        $fieldsNode = $xml->appendChild(new \DOMElement('form'))->appendChild(new \DOMElement('fields'));
        $fieldsNode->setAttribute('name', 'com_fields');

        // Organizing the fields according to their group
        $fieldsPerGroup = [0 => []];

        foreach ($fields as $field) {
            if (!array_key_exists($field->type, $fieldTypes)) {
                // Field type is not available
                continue;
            }

            if (!array_key_exists($field->group_id, $fieldsPerGroup)) {
                $fieldsPerGroup[$field->group_id] = [];
            }

            if ($path = $fieldTypes[$field->type]['path']) {
                // Add the lookup path for the field
                FormHelper::addFieldPath($path);
            }

            if ($path = $fieldTypes[$field->type]['rules']) {
                // Add the lookup path for the rule
                FormHelper::addRulePath($path);
            }

            $fieldsPerGroup[$field->group_id][] = $field;
        }

        $model = Factory::getApplication()->bootComponent('com_fields')
            ->getMVCFactory()->createModel('Groups', 'Administrator', ['ignore_request' => true]);
        $model->setState('filter.context', $context);

        /**
         * $model->getItems() would only return existing groups, but we also
         * have the 'default' group with id 0 which is not in the database,
         * so we create it virtually here.
         */
        $defaultGroup              = new \stdClass();
        $defaultGroup->id          = 0;
        $defaultGroup->title       = '';
        $defaultGroup->description = '';
        $iterateGroups             = array_merge([$defaultGroup], $model->getItems());

        // Looping through the groups
        foreach ($iterateGroups as $group) {
            if (empty($fieldsPerGroup[$group->id])) {
                continue;
            }

            // Defining the field set
            /** @var \DOMElement $fieldset */
            $fieldset = $fieldsNode->appendChild(new \DOMElement('fieldset'));
            $fieldset->setAttribute('name', 'fields-' . $group->id);
            $fieldset->setAttribute('addfieldpath', '/administrator/components/' . $component . '/models/fields');
            $fieldset->setAttribute('addrulepath', '/administrator/components/' . $component . '/models/rules');

            $label       = $group->title;
            $description = $group->description;

            if (!$label) {
                $key = strtoupper($component . '_FIELDS_' . $section . '_LABEL');

                if (!Factory::getLanguage()->hasKey($key)) {
                    $key = 'JGLOBAL_FIELDS';
                }

                $label = $key;
            }

            if (!$description) {
                $key = strtoupper($component . '_FIELDS_' . $section . '_DESC');

                if (Factory::getLanguage()->hasKey($key)) {
                    $description = $key;
                }
            }

            $fieldset->setAttribute('label', $label);
            $fieldset->setAttribute('description', strip_tags($description));

            // Looping through the fields for that context
            foreach ($fieldsPerGroup[$group->id] as $field) {
                try {
                    Factory::getApplication()->triggerEvent('onCustomFieldsPrepareDom', [$field, $fieldset, $form]);

                    /*
                     * If the field belongs to an assigned_cat_id but the assigned_cat_ids in the data
                     * is not known, set the required flag to false on any circumstance.
                     */
                    if (!$assignedCatids && !empty($field->assigned_cat_ids) && $form->getField($field->name)) {
                        $form->setFieldAttribute($field->name, 'required', 'false');
                    }
                } catch (\Exception $e) {
                    Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                }
            }

            // When the field set is empty, then remove it
            if (!$fieldset->hasChildNodes()) {
                $fieldsNode->removeChild($fieldset);
            }
        }

        // Loading the XML fields string into the form
        $form->load($xml->saveXML());

        $model = Factory::getApplication()->bootComponent('com_fields')
            ->getMVCFactory()->createModel('Field', 'Administrator', ['ignore_request' => true]);

        if (
            (!isset($data->id) || !$data->id) && Factory::getApplication()->getInput()->getCmd('controller') == 'modules'
            && Factory::getApplication()->isClient('site')
        ) {
            // Modules on front end editing don't have data and an id set
            $data->id = Factory::getApplication()->getInput()->getInt('id');
        }

        // Looping through the fields again to set the value
        if (!isset($data->id) || !$data->id) {
            return true;
        }

        foreach ($fields as $field) {
            $value = $model->getFieldValue($field->id, $data->id);

            if ($value === null) {
                continue;
            }

            if (!is_array($value) && $value !== '') {
                // Function getField doesn't cache the fields, so we try to do it only when necessary
                $formField = $form->getField($field->name, 'com_fields');

                if ($formField && $formField->forceMultiple) {
                    $value = (array) $value;
                }
            }

            // Setting the value on the field
            $form->setValue($field->name, 'com_fields', $value);
        }

        return true;
    }

    /**
     * Return a boolean if the actual logged in user can edit the given field value.
     *
     * @param   \stdClass  $field  The field
     *
     * @return  boolean
     *
     * @since   3.7.0
     */
    public static function canEditFieldValue($field)
    {
        $parts = self::extract($field->context);

        return Factory::getUser()->authorise('core.edit.value', $parts[0] . '.field.' . (int) $field->id);
    }

    /**
     * Return a boolean based on field (and field group) display / show_on settings
     *
     * @param   \stdClass  $field  The field
     *
     * @return  boolean
     *
     * @since   3.8.7
     */
    public static function displayFieldOnForm($field)
    {
        $app = Factory::getApplication();

        // Detect if the field should be shown at all
        if ($field->params->get('show_on') == 1 && $app->isClient('administrator')) {
            return false;
        } elseif ($field->params->get('show_on') == 2 && $app->isClient('site')) {
            return false;
        }

        if (!self::canEditFieldValue($field)) {
            $fieldDisplayReadOnly = $field->params->get('display_readonly', '2');

            if ($fieldDisplayReadOnly == '2') {
                // Inherit from field group display read-only setting
                $groupModel = $app->bootComponent('com_fields')
                    ->getMVCFactory()->createModel('Group', 'Administrator', ['ignore_request' => true]);
                $groupDisplayReadOnly = $groupModel->getItem($field->group_id)->params->get('display_readonly', '1');
                $fieldDisplayReadOnly = $groupDisplayReadOnly;
            }

            if ($fieldDisplayReadOnly == '0') {
                // Do not display field on form when field is read-only
                return false;
            }
        }

        // Display field on form
        return true;
    }

    /**
     * Gets assigned categories ids for a field
     *
     * @param   \stdClass[]  $fieldId  The field ID
     *
     * @return  array  Array with the assigned category ids
     *
     * @since   4.0.0
     */
    public static function getAssignedCategoriesIds($fieldId)
    {
        $fieldId = (int) $fieldId;

        if (!$fieldId) {
            return [];
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('a.category_id'))
            ->from($db->quoteName('#__fields_categories', 'a'))
            ->where('a.field_id = ' . $fieldId);

        $db->setQuery($query);

        return $db->loadColumn();
    }

    /**
     * Gets assigned categories titles for a field
     *
     * @param   \stdClass[]  $fieldId  The field ID
     *
     * @return  array  Array with the assigned categories
     *
     * @since   3.7.0
     */
    public static function getAssignedCategoriesTitles($fieldId)
    {
        $fieldId = (int) $fieldId;

        if (!$fieldId) {
            return [];
        }

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('c.title'))
            ->from($db->quoteName('#__fields_categories', 'a'))
            ->join('INNER', $db->quoteName('#__categories', 'c') . ' ON a.category_id = c.id')
            ->where($db->quoteName('field_id') . ' = :fieldid')
            ->bind(':fieldid', $fieldId, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadColumn();
    }

    /**
     * Gets the fields system plugin extension id.
     *
     * @return  integer  The fields system plugin extension id.
     *
     * @since   3.7.0
     */
    public static function getFieldsPluginId()
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('fields'));
        $db->setQuery($query);

        try {
            $result = (int) $db->loadResult();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            $result = 0;
        }

        return $result;
    }

    /**
     * Loads the fields plugins and returns an array of field types from the plugins.
     *
     * The returned array contains arrays with the following keys:
     * - label: The label of the field
     * - type:  The type of the field
     * - path:  The path of the folder where the field can be found
     *
     * @return  array
     *
     * @since   3.7.0
     */
    public static function getFieldTypes()
    {
        PluginHelper::importPlugin('fields');
        $eventData = Factory::getApplication()->triggerEvent('onCustomFieldsGetTypes');

        $data = [];

        foreach ($eventData as $fields) {
            foreach ($fields as $fieldDescription) {
                if (!array_key_exists('path', $fieldDescription)) {
                    $fieldDescription['path'] = null;
                }

                if (!array_key_exists('rules', $fieldDescription)) {
                    $fieldDescription['rules'] = null;
                }

                $data[$fieldDescription['type']] = $fieldDescription;
            }
        }

        return $data;
    }

    /**
     * Clears the internal cache for the custom fields.
     *
     * @return  void
     *
     * @since   3.8.0
     */
    public static function clearFieldsCache()
    {
        self::$fieldCache  = null;
        self::$fieldsCache = null;
    }
}
