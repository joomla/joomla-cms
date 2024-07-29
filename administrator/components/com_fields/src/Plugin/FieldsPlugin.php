<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\Plugin;

use Joomla\CMS\Event\CustomFields\GetTypesEvent;
use Joomla\CMS\Event\CustomFields\PrepareDomEvent;
use Joomla\CMS\Event\CustomFields\PrepareFieldEvent;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Abstract Fields Plugin
 *
 * @since  3.7.0
 */
abstract class FieldsPlugin extends CMSPlugin
{
    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.7.0
     */
    protected $autoloadLanguage = true;

    /**
     * Application object.
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  4.0.0
     */
    protected $app;

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
            'onCustomFieldsGetTypes'     => 'getFieldTypes',
            'onCustomFieldsPrepareField' => 'prepareField',
            'onCustomFieldsPrepareDom'   => 'prepareDom',
            'onContentPrepareForm'       => 'prepareForm',
        ];
    }

    /**
     * Returns the custom fields types.
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function getFieldTypes(GetTypesEvent $event)
    {
        $result = $this->onCustomFieldsGetTypes();

        if ($result) {
            $event->addResult($result);
        }
    }

    /**
     * Prepares the field value.
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function prepareField(PrepareFieldEvent $event)
    {
        $result = $this->onCustomFieldsPrepareField($event->getContext(), $event->getItem(), $event->getField());

        if ($result !== '' && $result !== null) {
            $event->addResult($result);
        }
    }

    /**
     * Transforms the field into a DOM XML element and appends it as a child on the given parent.
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function prepareDom(PrepareDomEvent $event)
    {
        $this->onCustomFieldsPrepareDom($event->getField(), $event->getFieldset(), $event->getForm());
    }

    /**
     * The form event. Load additional parameters when available into the field form.
     * Only when the type of the form is of interest.
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function prepareForm(PrepareFormEvent $event)
    {
        $this->onContentPrepareForm($event->getForm(), $event->getData());
    }

    /**
     * Returns the custom fields types.
     *
     * @return  string[][]
     *
     * @since   3.7.0
     */
    public function onCustomFieldsGetTypes()
    {
        // Cache filesystem access / checks
        static $types_cache = [];

        if (isset($types_cache[$this->_type . $this->_name])) {
            return $types_cache[$this->_type . $this->_name];
        }

        $types = [];

        // The root of the plugin
        $root = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name;

        foreach (Folder::files($root . '/tmpl', '.php') as $layout) {
            // Strip the extension
            $layout = str_replace('.php', '', $layout);

            // The data array
            $data = [];

            // The language key
            $key = strtoupper($layout);

            if ($key != strtoupper($this->_name)) {
                $key = strtoupper($this->_name) . '_' . $layout;
            }

            // Needed attributes
            $data['type'] = $layout;

            if ($this->app->getLanguage()->hasKey('PLG_FIELDS_' . $key . '_LABEL')) {
                $data['label'] = Text::sprintf('PLG_FIELDS_' . $key . '_LABEL', strtolower($key));

                // Fix wrongly set parentheses in RTL languages
                if ($this->app->getLanguage()->isRtl()) {
                    $data['label'] .= '&#x200E;';
                }
            } else {
                $data['label'] = $key;
            }

            $path = $root . '/fields';

            // Add the path when it exists
            if (file_exists($path)) {
                $data['path'] = $path;
            }

            $path = $root . '/rules';

            // Add the path when it exists
            if (file_exists($path)) {
                $data['rules'] = $path;
            }

            $types[] = $data;
        }

        // Add to cache and return the data
        $types_cache[$this->_type . $this->_name] = $types;

        return $types;
    }

    /**
     * Prepares the field value.
     *
     * @param   string     $context  The context.
     * @param   \stdclass  $item     The item.
     * @param   \stdclass  $field    The field.
     *
     * @return  ?string
     *
     * @since   3.7.0
     */
    public function onCustomFieldsPrepareField($context, $item, $field)
    {
        // Check if the field should be processed by us
        if (!$this->isTypeSupported($field->type)) {
            return '';
        }

        // Merge the params from the plugin and field which has precedence
        $fieldParams = clone $this->params;
        $fieldParams->merge($field->fieldparams);

        // Get the path for the layout file
        $path = PluginHelper::getLayoutPath('fields', $this->_name, $field->type);

        // Render the layout
        ob_start();
        include $path;
        $output = ob_get_clean();

        // Return the output
        return $output;
    }

    /**
     * Transforms the field into a DOM XML element and appends it as a child on the given parent.
     *
     * @param   \stdClass    $field   The field.
     * @param   \DOMElement  $parent  The field node parent.
     * @param   Form         $form    The form.
     *
     * @return  ?\DOMElement
     *
     * @since   3.7.0
     */
    public function onCustomFieldsPrepareDom($field, \DOMElement $parent, Form $form)
    {
        // Check if the field should be processed by us
        if (!$this->isTypeSupported($field->type)) {
            return null;
        }

        // Detect if the field is configured to be displayed on the form
        if (!FieldsHelper::displayFieldOnForm($field)) {
            return null;
        }

        // Create the node
        $node = $parent->appendChild(new \DOMElement('field'));

        // Set the attributes
        $node->setAttribute('name', $field->name);
        $node->setAttribute('type', $field->type);
        $node->setAttribute('label', $field->label);
        $node->setAttribute('labelclass', $field->params->get('label_class', ''));
        $node->setAttribute('description', $field->description);
        $node->setAttribute('class', $field->params->get('class', ''));
        $node->setAttribute('hint', $field->params->get('hint', ''));
        $node->setAttribute('required', $field->required ? 'true' : 'false');

        $showon_attribute = $field->params->get('showon', '');
        if ($showon_attribute) {
            $node->setAttribute('showon', $showon_attribute);
        }

        if ($field->default_value !== '') {
            $defaultNode = $node->appendChild(new \DOMElement('default'));
            $defaultNode->appendChild(new \DOMCdataSection($field->default_value));
        }

        // Combine the two params
        $params = clone $this->params;
        $params->merge($field->fieldparams);

        $layout = $field->params->get('form_layout', $this->params->get('form_layout', ''));

        if ($layout) {
            $node->setAttribute('layout', $layout);
        }

        // Set the specific field parameters
        foreach ($params->toArray() as $key => $param) {
            if (\is_array($param)) {
                // Multidimensional arrays (eg. list options) can't be transformed properly
                $param = \count($param) == \count($param, COUNT_RECURSIVE) ? implode(',', $param) : '';
            }

            if ($param === '' || (!\is_string($param) && !is_numeric($param))) {
                continue;
            }

            $node->setAttribute($key, $param);
        }

        // Check if it is allowed to edit the field
        if (!FieldsHelper::canEditFieldValue($field)) {
            $node->setAttribute('disabled', 'true');
        }

        // Return the node
        return $node;
    }

    /**
     * The form event. Load additional parameters when available into the field form.
     * Only when the type of the form is of interest.
     *
     * @param   Form       $form  The form
     * @param   \stdClass  $data  The data
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onContentPrepareForm(Form $form, $data)
    {
        $path = $this->getFormPath($form, $data);

        if ($path === null) {
            return;
        }

        // Load the specific plugin parameters
        $form->load(file_get_contents($path), true, '/form/*');
    }

    /**
     * Returns the path of the XML definition file for the field parameters
     *
     * @param   Form       $form  The form
     * @param   \stdClass  $data  The data
     *
     * @return  string
     *
     * @since   4.0.0
     */
    protected function getFormPath(Form $form, $data)
    {
        // Check if the field form is calling us
        if (strpos($form->getName(), 'com_fields.field') !== 0) {
            return null;
        }

        // Ensure it is an object
        $formData = (object) $data;

        // Gather the type
        $type = $form->getValue('type');

        if (!empty($formData->type)) {
            $type = $formData->type;
        }

        // Not us
        if (!$this->isTypeSupported($type)) {
            return null;
        }

        $path = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/params/' . $type . '.xml';

        // Check if params file exists
        if (!file_exists($path)) {
            return null;
        }

        return $path;
    }

    /**
     * Returns true if the given type is supported by the plugin.
     *
     * @param   string  $type  The type
     *
     * @return  boolean
     *
     * @since   3.7.0
     */
    protected function isTypeSupported($type)
    {
        foreach ($this->onCustomFieldsGetTypes() as $typeSpecification) {
            if ($type == $typeSpecification['type']) {
                return true;
            }
        }

        return false;
    }
}
