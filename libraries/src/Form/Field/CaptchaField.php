<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Captcha\CaptchaPluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

/**
 * Captcha field.
 *
 * @since  2.5
 */
class CaptchaField extends FormField
{
    /**
     * The field type.
     *
     * @var    string
     * @since  2.5
     */
    protected $type = 'Captcha';

    /**
     * The captcha base instance of our type.
     *
     * @var Captcha
     *
     * @deprecated 5.0 Will be removed without replacement as we do not need to cache it
     */
    protected $_captcha;

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.2
     */
    public function __get($name)
    {
        switch ($name) {
            case 'plugin':
                return $this->$name;
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
     * @since   3.2
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'plugin':
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
     * @since   2.5
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        $app = Factory::getApplication();

        $default = $app->get('captcha');

        if ($app->isClient('site')) {
            $default = $app->getParams()->get('captcha', $default);
        }

        $plugin = $this->element['plugin'] ?
            (string) $this->element['plugin'] :
            $default;

        $this->plugin = $plugin;

        if ($plugin === 0 || $plugin === '0' || $plugin === '' || $plugin === null) {
            $this->hidden = true;

            return false;
        } else {
            // Force field to be required. There's no reason to have a captcha if it is not required.
            // Obs: Don't put required="required" in the xml file, you just need to have validate="captcha"
            $this->required = true;

            if (strpos($this->class, 'required') === false) {
                $this->class .= ' required';
            }
        }

        try {
            // Exists for BC
            $this->_captcha = Captcha::getInstance($this->plugin, array('namespace' => $this->namespace));

            // Get an instance of the captcha class that we are using
            $captcha = $app->bootPlugin($this->plugin, 'captcha');

            /**
             * Give the captcha instance a possibility to react on the setup-process,
             * e.g. by altering the XML structure of the field, for example hiding the label
             * when using invisible captchas.
             */
            if ($captcha instanceof CaptchaPluginInterface) {
                $captcha->setupField($this, $element);

                return $result;
            }

            @trigger_error('Implement the CaptchaPluginInterface.', E_USER_DEPRECATED);
            if ($captcha && method_exists($captcha, 'onSetupField')) {
                $captcha->onSetupField($this, $element);
            }
        } catch (\RuntimeException $e) {
            $this->_captcha = null;
            $app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        return $result;
    }

    /**
     * Method to get the field input.
     *
     * @return  string  The field input.
     *
     * @since   2.5
     */
    protected function getInput()
    {
        if ($this->hidden || $this->_captcha == null) {
            return '';
        }

        try {
             // Get an instance of the captcha class that we are using
             $captcha = Factory::getApplication()->bootPlugin($this->plugin, 'captcha');

            if ($captcha instanceof CaptchaPluginInterface) {
                return $captcha->display($this->id ?: $this->name, $this->class);
            }

            @trigger_error('Implement the CaptchaPluginInterface.', E_USER_DEPRECATED);
            if (method_exists($captcha, 'onInit')) {
                $captcha->onInit($this->id);
            }

            return $captcha->onDisplay($this->name, $this->id ?: $this->name, $this->class);
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return '';
    }
}
