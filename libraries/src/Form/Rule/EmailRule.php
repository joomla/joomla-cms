<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  1.7.0
 */
class EmailRule extends FormRule implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * The regular expression to use in testing a form field value.
     *
     * @var    string
     * @since  1.7.0
     * @link   https://www.w3.org/TR/html/sec-forms.html#email-state-typeemail
     */
    protected $regex = "^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])"
            . "?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$";

    /**
     * Method to test the email address and optionally check for uniqueness.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
     * @param   ?Form              $form     The form object for which the field is being tested.
     *
     * @return  mixed  Boolean true if field value is valid.
     *
     * @since   1.7.0
     * @throws  \UnexpectedValueException
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        // If the field is empty and not required, the field is valid.
        $required = ((string) $element['required'] === 'true' || (string) $element['required'] === 'required');

        if (!$required && empty($value)) {
            return true;
        }

        // If the tld attribute is present, change the regular expression to require at least 2 characters for it.
        $tld = ((string) $element['tld'] === 'tld' || (string) $element['tld'] === 'required');

        if ($tld) {
            $this->regex = "^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])"
                . '?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$';
        }

        // Determine if the multiple attribute is present
        $multiple = ((string) $element['multiple'] === 'true' || (string) $element['multiple'] === 'multiple');

        if (!$multiple) {
            // Handle idn email addresses by converting to punycode.
            $value = PunycodeHelper::emailToPunycode($value);

            // Test the value against the regular expression.
            if (!parent::test($element, $value, $group, $input, $form)) {
                throw new \UnexpectedValueException(Text::_('JLIB_DATABASE_ERROR_VALID_MAIL'));
            }
        } else {
            $values = explode(',', $value);

            foreach ($values as $value) {
                // Handle idn email addresses by converting to punycode.
                $value = PunycodeHelper::emailToPunycode($value);

                // Test the value against the regular expression.
                if (!parent::test($element, $value, $group, $input, $form)) {
                    throw new \UnexpectedValueException(Text::_('JLIB_DATABASE_ERROR_VALID_MAIL'));
                }
            }
        }

        /**
         * validDomains value should consist of component name and the name of domain list field in component's configuration, separated by a dot.
         * This allows different components and contexts to use different lists.
         * If value is incomplete, com_users.domains is used as fallback.
         */
        $validDomains = (string) $element['validDomains'] !== '' && (string) $element['validDomains'] !== 'false';

        if ($validDomains && !$multiple) {
            $config = explode('.', $element['validDomains'], 2);

            if (\count($config) > 1) {
                $domains = ComponentHelper::getParams($config[0])->get($config[1]);
            } else {
                $domains = ComponentHelper::getParams('com_users')->get('domains');
            }

            if ($domains) {
                $emailDomain = explode('@', $value);
                $emailDomain = $emailDomain[1];
                $emailParts  = array_reverse(explode('.', $emailDomain));
                $emailCount  = \count($emailParts);
                $allowed     = true;

                foreach ($domains as $domain) {
                    $domainParts = array_reverse(explode('.', $domain->name));
                    $status      = 0;

                    // Don't run if the email has less segments than the rule.
                    if ($emailCount < \count($domainParts)) {
                        continue;
                    }

                    foreach ($emailParts as $key => $emailPart) {
                        if (!isset($domainParts[$key]) || $domainParts[$key] == $emailPart || $domainParts[$key] == '*') {
                            $status++;
                        }
                    }

                    // All segments match, check whether to allow the domain or not.
                    if ($status === $emailCount) {
                        if ($domain->rule == 0) {
                            $allowed = false;
                        } else {
                            $allowed = true;
                        }
                    }
                }

                // If domain is not allowed, fail validation. Otherwise continue.
                if (!$allowed) {
                    throw new \UnexpectedValueException(Text::sprintf('JGLOBAL_EMAIL_DOMAIN_NOT_ALLOWED', $emailDomain));
                }
            }
        }

        // Check if we should test for uniqueness. This only can be used if multiple is not true
        $unique = ((string) $element['unique'] === 'true' || (string) $element['unique'] === 'unique');

        if ($unique && !$multiple) {
            // Get the database object and a new query object.
            $db    = $this->getDatabase();
            $query = $db->getQuery(true);

            // Get the extra field check attribute.
            $userId = ($form instanceof Form) ? (int) $form->getValue('id') : 0;

            // Build the query.
            $query->select('COUNT(*)')
                ->from($db->quoteName('#__users'))
                ->where(
                    [
                        $db->quoteName('email') . ' = :email',
                        $db->quoteName('id') . ' <> :userId',
                    ]
                )
                ->bind(':email', $value)
                ->bind(':userId', $userId, ParameterType::INTEGER);

            // Set and query the database.
            $db->setQuery($query);
            $duplicate = (bool) $db->loadResult();

            if ($duplicate) {
                throw new \UnexpectedValueException(Text::_('JLIB_DATABASE_ERROR_EMAIL_INUSE'));
            }
        }

        return true;
    }
}
