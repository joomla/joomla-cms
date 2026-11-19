<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Rule;

use Cron\CronExpression;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Validates the whole automation rule at once.
 *
 * The fields live in a subform, and core's SubformRule returns only the first error its child form
 * produced, so validating them individually can report one problem per save however many there are.
 * Checking the row here means every problem is named in one message.
 *
 * @since  __DEPLOY_VERSION__
 */
class AutomationRule extends FormRule
{
    /**
     * @param   \SimpleXMLElement  $element  The field's XML definition.
     * @param   mixed              $value    The submitted automation row.
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
        $row = (array) $value;

        if ($row === []) {
            return true;
        }

        $problems = [];
        $ruleType = $row['rule_type'] ?? 'delay';

        if ($ruleType === 'cron') {
            $expression = trim((string) ($row['cron_expression'] ?? ''));

            if ($expression === '' || !CronExpression::isValidExpression($expression)) {
                $problems[] = Text::_('COM_WORKFLOW_AUTOMATION_ERROR_CRON');
            }
        } elseif ((int) ($row['delay_value'] ?? 0) < 0) {
            $problems[] = Text::_('COM_WORKFLOW_AUTOMATION_ERROR_DELAY_VALUE');
        }

        $builders = [
            'item_filter'    => 'COM_WORKFLOW_AUTOMATION_FILTER_LABEL',
            'fire_condition' => 'COM_WORKFLOW_AUTOMATION_CONDITION_LABEL',
        ];

        $incomplete = [];

        foreach ($builders as $key => $label) {
            if ($this->hasIncompleteCheck(json_decode((string) ($row[$key] ?? ''), true))) {
                $incomplete[] = Text::_($label);
            }
        }

        if (\count($incomplete) === 2) {
            $problems[] = Text::sprintf('COM_WORKFLOW_AUTOMATION_ERROR_INCOMPLETE_CHECKS', $incomplete[0], $incomplete[1]);
        } elseif ($incomplete !== []) {
            $problems[] = Text::sprintf('COM_WORKFLOW_AUTOMATION_ERROR_INCOMPLETE_CHECK', $incomplete[0]);
        }

        if ($problems === []) {
            return true;
        }

        throw new \RuntimeException(implode(' ', $problems));
    }

    /**
     * Whether an expression holds a check with no field, operator or value.
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
