<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Automation;

use Cron\CronExpression;
use DateTime;
use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Works out when an automation rule fires for a given stage-entry time.
 *
 * Shared by the scheduler and the upcoming transitions views, so both compute a deadline the
 * same way.
 *
 * @since  __DEPLOY_VERSION__
 */
final class DeadlineCalculator
{
    /**
     * Computes the datetime a rule fires for a given entry time.
     *
     * @param   string  $enteredAt  When the item entered the stage (SQL datetime, UTC).
     * @param   object  $rule       The rule row (rule_type, delay_value, delay_unit, cron_expression).
     *
     * @return  \DateTime|null  The fire time in UTC, or null if it cannot be computed.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function forRule(string $enteredAt, object $rule): ?\DateTime
    {
        // Every value parsed here comes from the database. The form validates a rule on save, so a
        // row that fails below was written by something else: a restored backup, direct SQL, a
        // partial import.
        try {
            if ($rule->rule_type === 'cron') {
                if (empty($rule->cron_expression)) {
                    return null;
                }

                $cronExpression = new CronExpression($rule->cron_expression);
                $deadline       = $cronExpression->getNextRunDate(
                    $enteredAt,
                    0,
                    false,
                    Factory::getApplication()->get('offset', 'UTC')
                );
                $deadline->setTimezone(new \DateTimeZone('UTC'));

                return $deadline;
            }

            $date  = new \DateTime($enteredAt, new \DateTimeZone('UTC'));
            $delay = match ($rule->delay_unit) {
                'minutes' => new \DateInterval('PT' . $rule->delay_value . 'M'),
                'hours'   => new \DateInterval('PT' . $rule->delay_value . 'H'),
                'days'    => new \DateInterval('P' . $rule->delay_value . 'D'),
                'months'  => new \DateInterval('P' . $rule->delay_value . 'M'),
                default   => null,
            };

            return $delay ? $date->add($delay) : null;
        } catch (\Throwable) {
            // Null tells the caller this rule cannot be scheduled: the scheduler records that
            // against the item, a view leaves the row out.
            return null;
        }
    }
}
