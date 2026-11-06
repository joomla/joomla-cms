<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Automation;

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Formats how long until an automated transition fires, as a short translated phrase.
 *
 * @since  __DEPLOY_VERSION__
 */
final class RelativeTime
{
    /**
     * Returns a translated "In X units" phrase for a future time, or "Due now" for a past one.
     *
     * @param \DateTime $target The moment to count down to (UTC).
     * @param \DateTime|null $now The reference now (UTC); defaults to the current time
     *
     * @return string
     *
     * @since __DEPLOY_VERSION__
     */
    public static function until(\DateTime $target, ?\DateTime $now = null): string
    {
        $now     = $now ?? new \DateTime('now', new \DateTimeZone('UTC'));
        $seconds = $target->getTimestamp() - $now->getTimestamp();

        if ($seconds <= 0) {
            return Text::_('COM_WORKFLOW_UPCOMING_DUE_NOW');
        }

        $minutes = (int) round($seconds / 60);

        if ($minutes < 60) {
            return Text::plural('COM_WORKFLOW_UPCOMING_IN_MINUTES', max(1, $minutes));
        }

        $hours = (int) round($seconds / 3600);

        if ($hours < 24) {
            return Text::plural('COM_WORKFLOW_UPCOMING_IN_HOURS', $hours);
        }

        $days = (int) round($seconds / 86400);

        if ($days < 30) {
            return Text::plural('COM_WORKFLOW_UPCOMING_IN_DAYS', $days);
        }

        return Text::plural('COM_WORKFLOW_UPCOMING_IN_MONTHS', max(1, (int) round($seconds / 2592000)));
    }
}
