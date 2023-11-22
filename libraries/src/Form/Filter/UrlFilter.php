<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Filter;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFilterInterface;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Filter class for URLs
 *
 * @since  4.0.0
 */
class UrlFilter implements FormFilterInterface
{
    /**
     * Method to filter a field value.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
     * @param   ?Form              $form     The form object for which the field is being tested.
     *
     * @return  mixed   The filtered value.
     *
     * @since   4.0.0
     */
    public function filter(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        if (empty($value)) {
            return false;
        }

        // This cleans some of the more dangerous characters but leaves special characters that are valid.
        $value = InputFilter::getInstance()->clean($value, 'html');
        $value = trim($value);

        // <>" are never valid in a uri see https://www.ietf.org/rfc/rfc1738.txt
        $value = str_replace(['<', '>', '"'], '', $value);

        // Check for a protocol
        $protocol = parse_url($value, PHP_URL_SCHEME);

        // If there is no protocol and the relative option is not specified,
        // we assume that it is an external URL and prepend http://
        if (
            ((string) $element['type'] === 'url' && !$protocol && !$element['relative'])
            || ((string) $element['type'] !== 'url' && !$protocol)
        ) {
            $protocol = 'http';

            // If it looks like an internal link, then add the root.
            if (substr($value, 0, 9) === 'index.php') {
                $value = Uri::root() . $value;
            } else {
                // Otherwise we treat it as an external link.
                // Put the url back together.
                $value = $protocol . '://' . $value;
            }
        } elseif (!$protocol && $element['relative']) {
            // If relative URLS are allowed we assume that URLs without protocols are internal.
            $host = Uri::getInstance('SERVER')->getHost();

            // If it starts with the host string, just prepend the protocol.
            if (substr($value, 0) === $host) {
                $value = 'http://' . $value;
            } elseif (substr($value, 0, 1) !== '/') {
                // Otherwise if it doesn't start with "/" prepend the prefix of the current site.
                $value = Uri::root(true) . '/' . $value;
            }
        }

        $value = PunycodeHelper::urlToPunycode($value);

        return $value;
    }
}
