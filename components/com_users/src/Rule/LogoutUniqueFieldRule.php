<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

/**
 * FormRule for com_users to be sure only one redirect logout field has a value
 *
 * @since  3.6
 */
class LogoutUniqueFieldRule extends FormRule
{
    /**
     * Method to test if two fields have a value in order to use only one field.
     * To use this rule, the form
     * XML needs a validate attribute of logoutuniquefield and a field attribute
     * that is equal to the field to test against.
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
     * @since   3.6
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        $logoutRedirectUrl      = $input['params']->logout_redirect_url;
        $logoutRedirectMenuitem = $input['params']->logout_redirect_menuitem;

        if ($form === null) {
            throw new \InvalidArgumentException(sprintf('The value for $form must not be null in %s', get_class($this)));
        }

        if ($input === null) {
            throw new \InvalidArgumentException(sprintf('The value for $input must not be null in %s', get_class($this)));
        }

        // Test the input values for logout.
        if ($logoutRedirectUrl != '' && $logoutRedirectMenuitem != '') {
            return false;
        }

        return true;
    }
}
