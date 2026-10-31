<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\Rule;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The DisallowQuotesRule Class.
 * Validates that non-superadmins can not use quotes or doublequotes in i18n overrides
 *
 * @since  joomla 6.1.2
 */
class DisallowQuotesRule extends FormRule
{
    /**
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form
     *                                       field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   ?string            $group    The field name group control value. This acts as an array container for the
     *                                       field. For example if the field has `name="foo"` and the group value is set
     *                                       to "bar" then the full field name would end up being "bar[foo]".
     * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against
     *                                       the entire form.
     * @param   ?Form              $form     The form object for which the field is being tested.
     *
     * @return boolean
     *
     * @since  4.1.0
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null): bool
    {
        // User with core.admin can use quotes
        if (Factory::getApplication()->getIdentity()->authorise('core.admin')) {
            return true;
        }

        // Disallow double and single quotes in overrides
        if (str_contains($value, '"') || str_contains($value, '\'')) {
            Factory::getApplication()->enqueueMessage(
                Text::_('COM_LANGUAGES_ERROR_QUOTES_IN_TEXT'),
                'error'
            );

            return false;
        }

        return true;
    }
}
