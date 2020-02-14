<?php

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldFilesystem extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var string
     *
     * @since  11.1
     */
    protected $type = 'Filesystem';

    /**
     * Method to attach a JForm object to the field.
     *
     * @param SimpleXMLElement $element The SimpleXMLElement object representing the <field /> tag for the form field object
     * @param mixed            $value   The form field value to validate
     * @param string           $group   The field name group control value. This acts as as an array container for the field.
     *                                  For example if the field has name="foo" and the group value is set to "bar" then the
     *                                  full field name would end up being "bar[foo]"
     *
     * @return bool True on success
     *
     * @since   11.1
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        return $return;
    }

    /**
     * Method to get the field input markup.
     *
     * @return string The field input markup
     *
     * @since   11.1
     */
    protected function getInput()
    {
        $value = $this->value;

        // decode json string
        if (!empty($value) && is_string($value)) {
            $value = json_decode($value, true);
        }

        // default
        if (empty($value)) {
            $value = array('name' => $this->default);
        } else {
            if (!isset($value['name'])) {
                $value['name'] = $this->default;
            }
        }

        $plugins = $this->getPlugins();
        $options = $this->getOptions();

        $html = '';
        $html .= '<div class="controls-row">';

        $html .= '<div class="control-group">';
        $html .= JHtml::_('select.genericlist', $options, $this->name . '[name]', 'data-toggle="filesystem-options"', 'value', 'text', $value['name']);
        $html .= '</div>';

        $html .= '<div class="filesystem-options clearfix">';

        foreach ($plugins as $plugin) {            
            $form = JForm::getInstance('plg_jce_' . $this->name . '_' . $plugin->name, $plugin->manifest, array('control' => $this->name . '[' . $plugin->name . ']'), true, '//extension');

            if ($form) {
                // get the data for this form, if set
                $data = isset($value[$plugin->name]) ? $value[$plugin->name] : array();
                
                // bind data to form
                $form->bind($data);
                
                $html .= '<div class="well well-small card card-body" data-toggle-target="filesystem-options-' . $plugin->name . '">';

                $fields = $form->getFieldset('filesystem.' . $plugin->name);

                foreach ($fields as $field) {
                     $html .= $field->renderField();
                }

                $html .= '</div>';
            }
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Method to get the field options.
     *
     * @return array The field option objects
     *
     * @since   11.1
     */
    protected function getPlugins()
    {
        static $plugins;

        if (!isset($plugins)) {
            $plugins = JcePluginsHelper::getExtensions('filesystem');
        }

        return $plugins;
    }

    /**
     * Method to get the field options.
     *
     * @return array The field option objects
     *
     * @since   11.1
     */
    protected function getOptions()
    {
        $fieldname = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);

        $options = parent::getOptions();

        /*$options[] = array(
            'value' => '',
            'text' => JText::_('WF_OPTION_NOT_SET'),
        );*/

        $plugins = $this->getPlugins();

        foreach ($plugins as $plugin) {
            $value = (string)$plugin->name;
            $text = (string)$plugin->title;

            $tmp = array(
                'value' => $value,
                'text' => JText::alt($text, $fieldname),
                'disable' => false,
                'class' => '',
                'selected' => false,
            );

            // Add the option object to the result set.
            $options[] = (object)$tmp;
        }

        reset($options);

        return $options;
    }
}
