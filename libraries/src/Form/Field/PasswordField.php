<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Text field for passwords
 *
 * @link   https://html.spec.whatwg.org/multipage/input.html#password-state-(type=password)
 * @note   Two password fields may be validated as matching using \Joomla\CMS\Form\Rule\EqualsRule
 * @since  1.7.0
 */
class PasswordField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Password';

    /**
     * The threshold of password field.
     *
     * @var    integer
     * @since  3.2
     */
    protected $threshold = 66;

    /**
     * The allowable minimum length of password.
     *
     * @var    integer
     * @since  4.3.0
     */
    protected $minLength;

    /**
     * The allowable maxlength of password.
     *
     * @var    integer
     * @since  3.2
     */
    protected $maxLength;

    /**
     * The allowable minimum length of integers.
     *
     * @var    integer
     * @since  4.3.0
     */
    protected $minIntegers;

    /**
     * The allowable minimum length of symbols.
     *
     * @var    integer
     * @since  4.3.0
     */
    protected $minSymbols;

    /**
     * The allowable minimum length of upper case characters.
     *
     * @var    integer
     * @since  4.3.0
     */
    protected $minUppercase;

    /**
     * The allowable minimum length of lower case characters.
     *
     * @var    integer
     * @since  4.3.0
     */
    protected $minLowercase;

    /**
     * Whether to attach a password strength meter or not.
     *
     * @var    boolean
     * @since  3.2
     */
    protected $meter = false;

    /**
     * Whether to attach a password strength meter or not.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $force = false;

    /**
     * The rules flag.
     *
     * @var    bool
     * @since  4.3.0
     */
    protected $rules = false;

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.7
     */
    protected $layout = 'joomla.form.field.password';

    /**
     * Attach an unlock button and disable the input field,
     * also remove the value from the output.
     *
     * @var    boolean
     * @since  3.9.24
     */
    protected $lock = false;

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
            case 'lock':
            case 'threshold':
            case 'maxLength':
            case 'meter':
            case 'force':
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
        $value = (string) $value;

        switch ($name) {
            case 'maxLength':
            case 'threshold':
                $this->$name = $value;
                break;

            case 'lock':
            case 'meter':
            case 'force':
                $this->$name = ($value === 'true' || $value === $name || $value === '1');
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
     * @since   3.2
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
            $lock               = (string) $this->element['lock'];
            $this->lock         = ($lock === 'true' || $lock === 'on' || $lock === '1');
            $this->maxLength    = $this->element['maxlength'] ? (int) $this->element['maxlength'] : 99;
            $this->threshold    = $this->element['threshold'] ? (int) $this->element['threshold'] : 66;
            $meter              = (string) $this->element['strengthmeter'];
            $this->meter        = ($meter === 'true' || $meter === 'on' || $meter === '1');
            $force              = (string) $this->element['forcePassword'];
            $this->force        = (($force === 'true' || $force === 'on' || $force === '1') && $this->meter === true);
            $rules              = (string) $this->element['rules'];
            $this->rules        = (($rules === 'true' || $rules === 'on' || $rules === '1') && $this->meter === true);

            // Set some initial values
            $this->minLength    = 12;
            $this->minIntegers  = 0;
            $this->minSymbols   = 0;
            $this->minUppercase = 0;
            $this->minLowercase = 0;

            if (Factory::getApplication()->get('db') != '' && !Factory::getApplication()->isClient('cli_installation')) {
                $this->minLength    = (int) ComponentHelper::getParams('com_users')->get('minimum_length', 12);
                $this->minIntegers  = (int) ComponentHelper::getParams('com_users')->get('minimum_integers', 0);
                $this->minSymbols   = (int) ComponentHelper::getParams('com_users')->get('minimum_symbols', 0);
                $this->minUppercase = (int) ComponentHelper::getParams('com_users')->get('minimum_uppercase', 0);
                $this->minLowercase = (int) ComponentHelper::getParams('com_users')->get('minimum_lowercase', 0);
            }
        }

        return $return;
    }

    /**
     * Method to get the field input markup for password.
     *
     * @return  string  The field input markup.
     *
     * @since   1.7.0
     */
    protected function getInput()
    {
        // Trim the trailing line in the layout file
        return rtrim($this->getRenderer($this->layout)->render($this->getLayoutData()), PHP_EOL);
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
        $extraData = [
            'lock'          => $this->lock,
            'maxLength'     => $this->maxLength,
            'meter'         => $this->meter,
            'threshold'     => $this->threshold,
            'minLength'     => $this->minLength,
            'minIntegers'   => $this->minIntegers,
            'minSymbols'    => $this->minSymbols,
            'minUppercase'  => $this->minUppercase,
            'minLowercase'  => $this->minLowercase,
            'forcePassword' => $this->force,
            'rules'         => $this->rules,
        ];

        return array_merge($data, $extraData);
    }
}
