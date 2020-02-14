<?php

defined('JPATH_PLATFORM') or die;

class JFormFieldBlockformats extends JFormFieldCheckboxes
{
    /**
     * The form field type.
     *
     * @var string
     *
     * @since  11.1
     */
    protected $type = 'Blockformats';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.5
     */
    protected $layout = 'form.field.blockformats';

    protected static $blockformats = array(
        'p' => 'Paragraph',
        'div' => 'Div',
        'div_container' => 'Div Container',
        'h1' => 'Heading1',
        'h2' => 'Heading2',
        'h3' => 'Heading3',
        'h4' => 'Heading4',
        'h5' => 'Heading5',
        'h6' => 'Heading6',
        'blockquote' => 'Blockquote',
        'address' => 'Address',
        'code' => 'Code',
        'pre' => 'Preformatted',
        'samp' => 'Sample',
        'span' => 'Span',
        'section' => 'Section',
        'article' => 'Article',
        'aside' => 'Aside',
        'header' => 'Header',
        'footer' => 'Footer',
        'nav' => 'Nav',
        'figure' => 'Figure',
        'dt' => 'Definition Term',
        'dd' => 'Definition List'
    );

    /**
     * Allow to override renderer include paths in child fields
     *
     * @return  array
     *
     * @since   3.5
     */
    protected function getLayoutPaths()
    {
        return array(JPATH_ADMINISTRATOR . '/components/com_jce/layouts', JPATH_SITE . '/layouts');
    }

    protected function getOptions()
    {
        $fieldname = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
        $options = array();

        if (!empty($this->value)) {
            $this->value = is_array($this->value) ? $this->value : explode(',', $this->value);
        }

        foreach (self::$blockformats as $value => $text) {
            $tmp = array(
                'value' => $value,
                'text' => JText::alt($text, $fieldname),
                'checked' => empty($this->value) || in_array($value, $this->value),
            );

            $options[] = (object) $tmp;
        }

        return $options;
    }
}
