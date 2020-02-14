<?php

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldFiletype extends JFormFieldText
{
    /**
     * The form field type.
     *
     * @var string
     *
     * @since  11.1
     */
    protected $type = 'Filetype';

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

    private static function array_flatten($array, $return)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $return = self::array_flatten($value, $return);
            } else {
                $return[] = $value;
            }
        }

        return $return;
    }

    private function mapValue($value)
    {
        $data = array();

        // no grouping
        if (strpos($value, '=') === false) {
            return array(explode(',', $value));
        }

        foreach (explode(';', $value) as $group) {
            $items = explode('=', $group);
            $name = $items[0];
            $values = explode(',', $items[1]);

            array_walk($values, function (&$item, $name) {
                if ($name{0} === '-') {
                    $item = '-' . $item;
                }
            }, $name);

            $data[$name] = $values;
        }

        return $data;
    }

    private function cleanValue($value)
    {
        $data = $this->mapValue($value);
        // get array values only
        $values = self::array_flatten($data, array());
        // convert to string
        $string = implode(',', $values);
        // return single array
        return explode(',', $string);
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
        // cleanup string
        $value = htmlspecialchars_decode($this->value);

        // map default values to groups
        $default = $this->mapValue($this->default);

        // remove leading = if any
        if ($value && $value{0} === '=') {
            $value = substr($value, 1);
        }

        // map values to groups
        $data = $this->mapValue($value);

        $html = array();

        $html[] = '<div class="filetype">';
        $html[] = ' <div class="input-append input-group">';

        $html[] = '     <input type="text" value="' . $value . '" disabled class="form-control" />';
        $html[] = '     <input type="hidden" name="' . $this->name . '" value="' . $value . '" />';
        $html[] = '     <div class="input-group-append">';
        $html[] = '         <a class="btn filetype-edit add-on input-group-text" role="button"><i class="icon-edit icon-apply"></i><span role="none">Edit</span></a>';
        $html[] = '     </div>';
        $html[] = ' </div>';

        foreach ($data as $group => $items) {
            $custom = array();

            $html[] = '<dl class="filetype-list">';

            if (is_string($group)) {
                $checked = '';

                $is_default = isset($default[$group]);

                if (empty($value) || $is_default || (!$is_default && $group{0} !== '-')) {
                    $checked = ' checked="checked"';
                }

                // clear minus sign
                $group = str_replace('-', '', $group);

                $groupKey = 'WF_FILEGROUP_' . strtoupper($group);
                $groupName = JText::_('WF_FILEGROUP_' . strtoupper($group));

                // create simple label if there is no translation
                if ($groupName === $groupKey) {
                    $groupName = ucfirst($group);
                }

                $html[] = '<dt class="filetype-group" data-filetype-group="' . $group . '"><label><input type="checkbox" value="' . $group . '"' . $checked . ' />' . $groupName . '</label></dt>';
            }

            foreach ($items as $item) {
                $checked = '';

                $item = strtolower($item);

                // clear minus sign
                $mod = str_replace('-', '', $item);

                $is_default = !empty($default[$group]) && in_array($item, $default[$group]);

                if (empty($value) || $is_default || (!$is_default && $mod === $item)) {
                    $checked = ' checked="checked"';
                }

                $html[] = '<dd class="filetype-item"><label><input type="checkbox" value="' . $mod . '"' . $checked . ' /><span class="file ' . $mod . '"></span>&nbsp;' . $mod . '</label>';

                if (!$is_default) {
                    $html[] = '<button class="btn btn-link filetype-remove"><span class="icon-trash"></span></button>';
                }

                $html[] = '</dd>';
            }

            $html[] = '<dd class="filetype-item filetype-custom row form-row"><div class="file"></div><input type="text" class="span8 col-md-8 form-control" value="" placeholder="' . JText::_('WF_EXTENSION_MAPPER_TYPE_NEW') . '" /><button class="pull-right float-right btn btn-link filetype-add"><span class="icon-plus"></span></button><button class="pull-right float-right btn btn-link filetype-remove"><span class="icon-trash"></span></button></dd>';

            $html[] = '</dl>';
        }

        $html[] = ' </div>';

        return implode("\n", $html);
    }
}
