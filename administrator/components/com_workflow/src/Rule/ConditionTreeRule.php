<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Rejects a filter or condition holding a check with no field, operator or value.
 *
 * @since  __DEPLOY_VERSION__
 */
class ConditionTreeRule extends FormRule
{
    /**
     * @param   \SimpleXMLElement  $element  The field's XML definition.
     * @param   mixed              $value    The submitted expression, as JSON.
     * @param   ?string            $group    The field's group.
     * @param   ?Registry          $input    Every value submitted with the form.
     * @param   ?Form              $form     The form being validated.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null): bool
    {
        if (!$this->hasIncompleteCheck(json_decode((string) $value, true))) {
            return true;
        }

        // Thrown rather than returned so the message can name which builder needs attention.
        throw new \RuntimeException(
            Text::sprintf('COM_WORKFLOW_AUTOMATION_ERROR_INCOMPLETE_CHECK', Text::_((string) $element['label']))
        );
    }

    /**
     * Whether an expression holds a check with no field, operator or value.
     *
     * The builder submits such checks rather than dropping them, so a save rejected here returns
     * the expression to the editor to finish instead of losing it.
     *
     * @param   mixed  $node  A decoded expression or check, or null when there is none.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function hasIncompleteCheck(mixed $node): bool
    {
        if (!\is_array($node)) {
            return false;
        }

        if (\array_key_exists('items', $node)) {
            foreach ((array) $node['items'] as $child) {
                if ($this->hasIncompleteCheck($child)) {
                    return true;
                }
            }

            return false;
        }

        $value = $node['value'] ?? '';

        return ($node['field'] ?? '') === ''
            || ($node['operator'] ?? '') === ''
            || $value === ''
            || $value === [];
    }
}
