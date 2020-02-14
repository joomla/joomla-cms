<?php

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldPopups extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var string
     *
     * @since  11.1
     */
    protected $type = 'Popups';

    /**
     * Method to get a list of options for a list input.
     *
     * @return array An array of JHtml options
     *
     * @since   11.4
     */
    protected function getOptions()
    {
        $extensions = JcePluginsHelper::getExtensions('popups');

        $options = array();

        foreach ($extensions as $item) {
            $option = new StdClass;

            $option->text = JText::_($item->title, true);
            $option->disable = '';
            $option->value = $item->name;

            $options[] = $option;
        }

        // Merge any additional options in the XML definition.
        return array_merge(parent::getOptions(), $options);
    }
}
