<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Module Order field.
 *
 * @since  1.6
 */
class ModuleorderField extends FormField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'ModuleOrder';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.6.3
     */
    protected $layout = 'joomla.form.field.moduleorder';

    /**
     * The linked property
     *
     * @var    string
     * @since  __DEPLOY_VERSION_
     */
    protected $linked;

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.6.3
     */
    public function __get($name)
    {
        if ($name === 'linked') {
            return $this->linked;
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
     * @since   3.6.3
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'linked':
                $this->$name = (string) $value;
                break;

            default:
                parent::__set($name, $value);
        }
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
     * @since   3.6.3
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
            $this->linked    = isset($this->element['linked']) ? (int) $this->element['linked'] : 'position';
        }

        return $return;
    }

    /**
     * Method to get the field input markup for the moduleorder field.
     *
     * @return  string  The field input markup.
     *
     * @since   1.6
     */
    protected function getInput()
    {
        return $this->getRenderer($this->layout)->render($this->getLayoutData());
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since  3.6.3
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        $extraData = [
            'ordering' => $this->form->getValue('ordering'),
            'clientId' => $this->form->getValue('client_id'),
            'moduleId' => $this->form->getValue('id'),
            'name'     => $this->name,
            'token'    => Session::getFormToken() . '=1',
            'element'  => $this->form->getName() . '_' . $this->linked
        ];

        return array_merge($data, $extraData);
    }
}
