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
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Rejects an automation rule whose cron expression cannot be parsed.
 *
 * @since  __DEPLOY_VERSION__
 */
class CronExpressionRule extends FormRule
{
    /**
     * @param   \SimpleXMLElement  $element  The field's XML definition.
     * @param   mixed              $value    The submitted expression.
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
        // showon hides this field for a delay rule but still submits it, so it is empty there and fine.
        if ($input === null || $input->get('rule_type', 'delay') !== 'cron') {
            return true;
        }

        $expression = trim((string) $value);

        return $expression !== '' && CronExpression::isValidExpression($expression);
    }
}
