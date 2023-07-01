<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Schemaorg;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Event\EventInterface;
use Joomla\Registry\Registry;

/**
 * Trait for component schemaorg plugins.
 *
 * @since __DEPLOY_VERSION__
 */
trait SchemaorgPluginTrait
{
    /**
     * Define all fields which are media type to clean them
     *
     * @var array
     */
    protected $imageFields = [
        'image',
    ];

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onSchemaPrepareForm' => 'onSchemaPrepareForm',
            'onSchemaPrepareSave' => 'onSchemaPrepareSave',
        ];
    }

    /**
     * Add a new option to schemaType list field in schema form
     *
     * @param   EventInterface $event
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function addSchemaType(EventInterface $event)
    {
        $form = $event->getArgument('subject');
        $name = $this->pluginName;

        if (!$form || !$name) {
            return false;
        }

        $schemaType = $form->getField('schemaType', 'schema');

        if ($schemaType instanceof ListField) {
            $schemaType->addOption($name, ['value' => $name]);
        }

        return true;
    }

    /**
     *  Add a new option to the schema type in the item editing page
     *
     *  @param   EventInterface  $event  The form to be altered.
     *
     *  @return  boolean
     */
    public function onSchemaPrepareForm(EventInterface $event)
    {
        $form    = $event->getArgument('subject');
        $context = $form->getName();

        if (!$this->isSupported($context)) {
            return false;
        }

        $this->addSchemaType($event);

        //Load the form fields
        if (is_file(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms/schemaorg.xml')) {
            $form->loadFile(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms/schemaorg.xml');
        }

        return true;
    }

    /**
     *  Add content to the object
     *
     *  @param   EventInterface  $event  The form to be altered.
     *
     *  @return  boolean
     */
    public function onSchemaPrepareSave(EventInterface $event)
    {
        $entry   = $event->getArgument('subject');
        $context = $event->getArgument('context');
        $schema  = $event->getArgument('schema');

        if (!$this->isSupported($context) || empty($schema['schemaType']) || $schema['schemaType'] !== $this->pluginName) {
            return true;
        }

        $mySchema = $schema[$this->pluginName];

        $entry->schemaType = $this->pluginName;
        $entry->schemaForm = (new Registry($mySchema))->toString();

        $schema        = $this->cleanupSchema($mySchema);
        $entry->schema = (new Registry($schema))->toString();
    }

    /**
     * Removes empty fields and changes time duration to ISO format in schema form
     *
     * @param   array $data JSON object of the data stored in schema form
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function cleanupSchema(array $data)
    {
        // Let plugins implement their own cleanup
        $data = $this->customCleanup($data);

        $schema = [];

        foreach ($data as $key => $value) {
            // Subtypes need special handling
            if (is_array($value) && !empty($value['@type'])) {
                if ($value['@type'] === 'ImageObject') {
                    if (!empty($value['url'])) {
                        $value['url'] = $this->cleanupImage($value['url']);
                    }

                    if (empty($value['url'])) {
                        $value = [];
                    }
                }

                $value = $this->cleanupSchema($value);

                // We don't save when the array contains only the @type
                if (count($value) <= 1) {
                    $value = null;
                }
            }

            // Custom generic fields
            elseif (is_array($value) && $key == 'genericField') {
                foreach ($value as $field) {
                    $schema[$field['genericTitle']] = $field['genericValue'];
                }

                continue;
            }

            // No data, no pary
            if (empty($value)) {
                continue;
            }

            if (in_array($key, $this->imageFields)) {
                $value = $this->cleanupImage($value);
            }

            $schema[$key] = $value;
        }

        return $schema;
    }

    /**
     * Cleanup media image files
     *
     * @param string|array $image
     *
     * @return string|null
     */
    protected function cleanupImage($image)
    {
        if (is_array($image)) {
            $newImages = [];

            foreach ($image as $img) {
                $newImages[] = $this->cleanupImage($img);
            }

            return $newImages;
        }

        $img = HTMLHelper::_('cleanImageUrl', $image);

        return $img->url ?? null;
    }

    /**
     *  To normalize duration to ISO format
     *
     *  @param   array $schema Schema form
     *  @param   array $durationKeys Keys with duration fields
     *
     *  @return  array
     */
    protected function normalizeDurationsToISO(array $schema, array $durationKeys)
    {
        foreach ($durationKeys as $durationKey) {
            $duration = $schema[$durationKey] ?? [];

            if (empty($duration)) {
                continue;
            }

            $min  = $duration['min'] ?? 0;
            $hour = $duration['hour'] ?? 0;

            $newDuration = false;

            if ($hour && $min && $min < 60) {
                $newDuration = "PT" . $hour . "H" . $min . "M";
            } elseif ($hour) {
                $newDuration = "PT" . $hour . "H";
            } elseif ($min && $min < 60) {
                $newDuration = "PT" . $min . "M";
            }

            if ($newDuration === false) {
                unset($schema[$durationKey]);

                continue;
            }

            $schema[$durationKey] = $newDuration;
        }

        return $schema;
    }

    /**
     *  To create an array from repeatable text field data
     *
     *  @param   array $schema Schema form
     *  @param   array $repeatableFields Names of all the Repeatable fields
     *
     *  @return  array
     */
    protected function convertToArray(array $schema, array $repeatableFields)
    {
        foreach ($repeatableFields as $repeatableField) {
            $field = $schema[$repeatableField] ?? [];

            if (empty($field)) {
                continue;
            }

            $result = [];

            foreach ($field as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $result[] = $v;
                    }

                    continue;
                }

                $result[] = $value;
            }

            if (empty($result)) {
                unset($schema[$repeatableField]);

                continue;
            }

            $schema[$repeatableField] = $result;
        }

        return $schema;
    }

    /**
     *  To clean up the date fields in
     *
     *  @param   array $schema Schema form
     *  @param   array $dateKeys Keys with date fields
     *
     *  @return  boolean
     */
    protected function cleanupDate(array $schema, array $dateKeys)
    {
        foreach ($dateKeys as $dateKey) {
            $date = $schema[$dateKey] ?? [];

            if (empty($date)) {
                continue;
            }

            $schema[$dateKey] = $date = Factory::getDate($date)->format('Y-m-d');
        }

        return $schema;
    }

    /**
     *  To add plugin specific functions
     *
     *  @param   array $schema Schema form
     *
     *  @return  array
     */
    protected function customCleanup(array $schema)
    {
        //Write your code for extra filteration
        return $schema;
    }

    /**
     * Check if the current plugin should execute schemaorg related activities
     *
     * @param   string  $context
     *
     * @return boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function isSupported($context)
    {
        if (!$this->checkAllowedAndForbiddenlist($context)) {
            return false;
        }

        $parts = explode('.', $context);

        // We need at least the extension + view for loading the table fields
        if (count($parts) < 2) {
            return false;
        }

        $component = $this->app->bootComponent($parts[0]);

        return $component instanceof SchemaorgServiceInterface;
    }

    /**
     * Check if the context is listed in the allowed of forbidden lists and return the result.
     *
     * @param   string $context Context to check
     *
     * @return  boolean
     */
    protected function checkAllowedAndForbiddenlist($context)
    {
        $allowedlist   = \array_filter((array) $this->params->get('allowedlist', []));
        $forbiddenlist = \array_filter((array) $this->params->get('forbiddenlist', []));

        if (!empty($allowedlist)) {
            foreach ($allowedlist as $allowed) {
                if ($context === $allowed) {
                    return true;
                }
            }

            return false;
        }

        foreach ($forbiddenlist as $forbidden) {
            if ($context === $forbidden) {
                return false;
            }
        }

        return true;
    }
}
