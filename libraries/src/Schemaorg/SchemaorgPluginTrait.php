<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Schemaorg;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Schemaorg\SchemaorgServiceInterface;
use Joomla\Event\EventInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for component schemaorg plugins.
 *
 * @since 5.0.0
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
     * @since   5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onSchemaPrepareForm' => 'onSchemaPrepareForm',
        ];
    }

    /**
     * Add a new option to schemaType list field in schema form
     *
     * @param   EventInterface $event
     *
     * @return  boolean
     *
     * @since   5.0.0
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
     * Check if the current plugin should execute schemaorg related activities
     *
     * @param   string  $context
     *
     * @return boolean
     *
     * @since   5.0.0
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

        $component = $this->getApplication()->bootComponent($parts[0]);

        return $component instanceof SchemaorgServiceInterface;
    }

    /**
     * Check if the context is listed in the allowed or forbidden lists and return the result.
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
