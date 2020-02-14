<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_BASE') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Renders a select element.
 */
class JFormFieldStyleFormat extends JFormField
{
    protected $wrapper = array();
    protected $merge = array();

    protected $sections = array('section', 'nav', 'article', 'aside', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'footer', 'address', 'main');
    protected $grouping = array('p', 'pre', 'blockquote', 'figure', 'figcaption', 'div');
    protected $textlevel = array('em', 'strong', 'small', 's', 'cite', 'q', 'dfn', 'abbr', 'data', 'time', 'code', 'var', 'samp', 'kbd', 'sub', 'i', 'b', 'u', 'mark', 'ruby', 'rt', 'rp', 'bdi', 'bdo', 'span', 'wbr');
    protected $formelements = array('form', 'input', 'button', 'fieldset', 'legend');
    /*
     * Element type
     *
     * @access    protected
     * @var        string
     */
    protected $type = 'StyleFormat';

    protected function getInput()
    {
        $wf = WFApplication::getInstance();

        $output = array();

        // default item list (remove "attributes" for now)
        $default = array('title' => '', 'element' => '', 'selector' => '', 'classes' => '', 'styles' => '', 'attributes' => '');

        // pass to items
        $items = $this->value;

        if (is_string($items)) {
            $items = json_decode(htmlspecialchars_decode($this->value), true);
        }

        // cast to array
        $items = (array) $items;

        /* Convert legacy styles */
        $theme_advanced_styles = $wf->getParam('editor.theme_advanced_styles', '');

        if (!empty($theme_advanced_styles)) {
            foreach (explode(',', $theme_advanced_styles) as $styles) {
                $style = json_decode('{' . preg_replace('#([^=]+)=([^=]+)#', '"title":"$1","classes":"$2"', $styles) . '}', true);

                if ($style) {
                    $items[] = $style;
                }
            }
        }

        // create default array if no items
        if (empty($items)) {
            $items = array($default);
        }

        // store element options
        $this->elements = $this->getElementOptions();

        $output[] = '<div class="styleformat-list">';

        foreach ($items as $item) {
            $elements = array('<div class="styleformat">');

            foreach ($default as $key => $value) {
                if (array_key_exists($key, $item)) {
                    $value = $item[$key];
                }

                $elements[] = '<div class="styleformat-item-' . $key . '">' . $this->getField($key, $value) . '</div>';
            }

            $elements[] = '<div class="styleformat-header">';

            // handle
            $elements[] = '<span class="styleformat-item-handle"></span>';
            // delete button
            $elements[] = '<button class="styleformat-item-trash btn btn-link pull-right float-right"><i class="icon icon-trash"></i></button>';
            // collapse
            $elements[] = '<button class="close collapse"><span class="icon-chevron-up"></span><span class="icon-chevron-down"></span></button>';

            $elements[] = '</div>';

            $elements[] = '</div>';

            $output[] = implode('', $elements);
        }

        $output[] = '<button class="btn btn-link styleformat-item-plus"><span class="span10 col-md-10 text-left">' . JText::_('WF_STYLEFORMAT_NEW') . '</span><i class="icon icon-plus pull-right float-right"></i></button>';

        // hidden field
        $output[] = '<input type="hidden" name="' . $this->name . '" value="" />';

        if (!empty($theme_advanced_styles)) {
            $output[] = '<input type="hidden" name="' . $this->getName('theme_advanced_styles') . '" value="" class="isdirty" />';
        }

        $output[] = '</div>';

        return implode("\n", $output);
    }

    protected function getElementOptions()
    {
        // create elements list
        $options = array(
            JHTML::_('select.option', '', JText::_('WF_OPTION_SELECTED_ELEMENT')),
        );

        $options[] = JHTML::_('select.option', '<OPTGROUP>', JText::_('WF_OPTION_SECTION_ELEMENTS'));

        foreach ($this->sections as $item) {
            $options[] = JHTML::_('select.option', $item, $item);
        }

        $options[] = JHTML::_('select.option', '</OPTGROUP>');

        $options[] = JHTML::_('select.option', '<OPTGROUP>', JText::_('WF_OPTION_GROUPING_ELEMENTS'));

        foreach ($this->grouping as $item) {
            $options[] = JHTML::_('select.option', $item, $item);
        }

        $options[] = JHTML::_('select.option', '</OPTGROUP>');

        $options[] = JHTML::_('select.option', '<OPTGROUP>', JText::_('WF_OPTION_TEXT_LEVEL_ELEMENTS'));

        foreach ($this->textlevel as $item) {
            $options[] = JHTML::_('select.option', $item, $item);
        }

        $options[] = JHTML::_('select.option', '</OPTGROUP>');

        $options[] = JHTML::_('select.option', '<OPTGROUP>', JText::_('WF_OPTION_FORM_ELEMENTS', 'Form Elements'));

        foreach ($this->formelements as $item) {
            $options[] = JHTML::_('select.option', $item, $item);
        }

        $options[] = JHTML::_('select.option', '</OPTGROUP>');

        return $options;
    }

    protected function getField($key, $value)
    {
        $item = array();

        if ($key !== 'title') {
            $item[] = '<label for="' . $key . '">' . JText::_('WF_STYLEFORMAT_' . strtoupper($key)) . '</label>';
        }

        // encode value
        $value = htmlspecialchars($value);

        $attribs = array(
            "class" => "form-control",
            "data-key" => $key
        );

        switch ($key) {
            case 'inline':
            case 'block':
            case 'element':

                $item[] = JHTML::_('select.genericlist', $this->elements, null, ArrayHelper::toString($attribs), 'value', 'text', $value);

                break;
            case 'title':
                $item[] = '<input type="text" ' . ArrayHelper::toString($attribs) . ' placeholder="' . JText::_('WF_STYLEFORMAT_' . strtoupper($key)) . '" value="' . $value . '" />';
                break;
            case 'styles':
            case 'attributes':
            case 'selector':
            case 'classes':

                $item[] = '<input type="text" ' . ArrayHelper::toString($attribs) . ' value="' . $value . '" />';

                break;
        }
        if ($key !== 'title') {
            $item[] = '<small class="help-block">' . JText::_('WF_STYLEFORMAT_' . strtoupper($key) . '_DESC') . '</small>';
        }

        return implode('', $item);
    }
}
