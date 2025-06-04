<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\Rule;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\Rule\EmailRule;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * FormRule for com_contact to make sure the email address is not blocked.
 *
 * @since  1.6
 */
class ContactEmailRule extends EmailRule
{
    /**
     * Method to test for banned email addresses
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
     * @param   ?Form              $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
    {
        if (!parent::test($element, $value, $group, $input, $form)) {
            return false;
        }

        $params = ComponentHelper::getParams('com_contact');
        $banned = $params->get('banned_email');

        if ($banned) {
            foreach (explode(';', $banned) as $item) {
                $item = trim($item);
                if ($item != '' && StringHelper::stristr($value, $item) !== false) {
                    return false;
                }
            }
        }

        return true;
    }
}
