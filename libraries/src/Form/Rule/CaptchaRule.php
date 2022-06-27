<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

/**
 * Form Rule class for the Joomla Framework.
 *
 * @since  2.5
 */
class CaptchaRule extends FormRule
{
    /**
     * Method to test if the Captcha is correct.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     * @param   Registry           $input    An optional Registry object with the entire data set to validate against the entire form.
     * @param   Form               $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @since   2.5
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        $app    = Factory::getApplication();
        $plugin = $app->get('captcha');

        if ($app->isClient('site')) {
            $plugin = $app->getParams()->get('captcha', $plugin);
        }

        $namespace = $element['namespace'] ?: $form->getName();

        // Use 0 for none
        if ($plugin === 0 || $plugin === '0') {
            return true;
        }

        try {
            $captcha = Captcha::getInstance((string) $plugin, array('namespace' => (string) $namespace));

            return $captcha->checkAnswer($value);
        } catch (\RuntimeException $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
        }

        return false;
    }
}
