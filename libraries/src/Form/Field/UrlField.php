<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Supports a URL text field
 *
 * @link   https://html.spec.whatwg.org/multipage/input.html#url-state-(type=url)
 * @see    \Joomla\CMS\Form\Rule\UrlRule for validation of full urls
 * @since  1.7.0
 */
class UrlField extends TextField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Url';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.7
     */
    protected $layout = 'joomla.form.field.url';

    /**
     * Whether relative URLs are allowed.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $relative = false;

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __get($name)
    {
        if ($name === 'relative') {
            return $this->relative;
        }

        return parent::__get($name);
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to set the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __set($name, $value)
    {
        if ($name === 'relative') {
            $value          = (string) $value;
            $this->relative = ($value === 'true' || $value === 'relative' || $value === '1');

            return;
        }

        parent::__set($name, $value);
    }

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   __DEPLOY_VERSION__
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
            $relative       = (string) $this->element['relative'];
            $this->relative = ($relative === 'true' || $relative === 'relative' || $relative === '1');
        }

        return $return;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   3.1.2 (CMS)
     */
    protected function getInput()
    {
        // Trim the trailing line in the layout file
        return rtrim($this->getRenderer($this->layout)->render($this->collectLayoutData()), PHP_EOL);
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since 3.7
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        // Initialize some field attributes.
        $maxLength    = !empty($this->maxLength) ? ' maxlength="' . $this->maxLength . '"' : '';

        // Note that the input type "url" is suitable only for external URLs, so if internal URLs are allowed
        // we have to use the input type "text" instead.
        $inputType    = $this->relative ? 'type="text"' : 'type="url"';

        $extraData = [
            'maxLength' => $maxLength,
            'inputType' => $inputType,
        ];

        return array_merge($data, $extraData);
    }
}
