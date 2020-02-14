<?php

defined('JPATH_PLATFORM') or die;

class JFormFieldPlugin extends JFormFieldFileList
{
    /**
     * The form field type.
     *
     * @var string
     *
     * @since  11.1
     */
    protected $type = 'Plugin';

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
            $type = $this->default;
            $path = '';
        }

        $plugins = $this->getPlugins();

        $html = '<div class="span9">';
        foreach ($plugins as $plugin) {
            $name = (string) str_replace($this->name.'-', '', $plugin->element);

            $form = JForm::getInstance('plg_jce_'.$plugin->element, $plugin->manifest, array('control' => $this->name.'['.$name.']'), true, '//extension');

            if ($form) {
                $html .= $form->renderFieldset('extension.'.$name.'.'.$name);
            }
        }

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
            $language = JFactory::getLanguage();

            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('name, element')
                ->from('#__extensions')
                ->where('enabled = 1')
                ->where('type ='.$db->quote('plugin'))
                ->where('state IN (0,1)')
                ->where('folder = '.$db->quote('jce'))
                ->where('element LIKE '.$db->quote($this->name.'-%'))
                ->order('ordering');

            $plugins = $db->setQuery($query)->loadObjectList();

            foreach ($plugins as $plugin) {
                $name = str_replace($this->name, '', $plugin->element);

                // load language file
                $language->load('plg_jce_'.$this->name.'_'.$name, JPATH_ADMINISTRATOR);

                // create manifest path
                $plugin->manifest = JPATH_PLUGINS.'/jce/'.$plugin->element.'/'.$plugin->element.'.xml';
            }
        }

        return $plugins;
    }
}
