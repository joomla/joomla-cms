<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Workflow\Administrator\Automation\RelativeTime;
use Joomla\Component\Workflow\Administrator\Automation\UpcomingTransition;
use Joomla\Component\Workflow\Administrator\Automation\UpcomingTransitionsCalculator;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Read-only field that shows an item's next automated transition on its edit screen.
 *
 * Outputs nothing for an unsaved item or one with no automated move pending.
 *
 * @since  __DEPLOY_VERSION__
 */
class UpcomingtransitionField extends FormField
{
    /**
     * The form field type.
     *
     * @var string
     * @since __DEPLOY_VERSION__
     */
    protected $type = 'Upcomingtransition';

    /**
     * Result of buildCard(), kept so renderField() and getInput() do not run the calculator twice.
     *
     * @var string|null
     * @since __DEPLOY_VERSION__
     */
    private $card;

    /**
     * @return string
     *
     * @since __DEPLOY_VERSION__
     */
    protected function getInput()
    {
        if ($this->card === null) {
            $this->card = $this->buildCard();
        }

        return $this->card;
    }

    /**
     * Drops the label and wrapper when there is nothing pending, the way SpacerField does, so the
     * sidebar does not show an empty row on unsaved items.
     *
     * @param   array  $options  Rendering options.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function renderField($options = [])
    {
        return $this->getInput() === '' ? '' : parent::renderField($options);
    }

    /**
     * @return string
     *
     * @since __DEPLOY_VERSION__
     */
    private function buildCard()
    {
        $itemId    = (int) $this->form->getValue('id');
        $extension = (string) $this->form->getName();

        if ($itemId <= 0) {
            return '';
        }

        Factory::getApplication()->getLanguage()->load('com_workflow', JPATH_ADMINISTRATOR);

        $database   = Factory::getContainer()->get(DatabaseInterface::class);
        $calculator = new UpcomingTransitionsCalculator($database);
        $upcoming   = $calculator->forItem($itemId, $extension);

        if ($upcoming === null) {
            return '';
        }

        return $this->renderCard($upcoming);
    }

    /**
     * Renders the block for one upcoming transition
     *
     * @param UpcomingTransition $upcoming The item's next move.
     *
     * @return string
     *
     * @since __DEPLOY_VERSION__
     */
    private function renderCard(UpcomingTransition $upcoming): string
    {
        $fires = match ($upcoming->status) {
            'needs_attention' => '<span class="badge bg-danger">' . Text::_('COM_WORKFLOW_UPCOMING_STATUS_ATTENTION') . '</span>',
            'rule_error'      => '<span class="badge bg-warning text-dark">' . Text::_('COM_WORKFLOW_UPCOMING_STATUS_RULE_ERROR') . '</span>',
            'not_scheduled'   => '<span class="badge bg-secondary">' . Text::_('COM_WORKFLOW_UPCOMING_STATUS_NOT_SCHEDULED') . '</span>',
            // Guarded anyway, so a future status without a fire time cannot break the edit screen.
            default => $upcoming->firesAt === null
                ? '<span class="badge bg-secondary">' . Text::_('COM_WORKFLOW_UPCOMING_STATUS_NOT_SCHEDULED') . '</span>'
                : '<div>' . RelativeTime::until($upcoming->firesAt) . '</div>'
                . '<div class="small">' . HTMLHelper::_('date', $upcoming->firesAt->format('Y-m-d H:i:s'), Text::_('DATE_FORMAT_LC2')) . '</div>'
                . ($upcoming->hasCondition
                    ? '<span class="badge bg-info">' . Text::_('COM_WORKFLOW_UPCOMING_SUBJECT_CONDITION') . '</span>'
                    : ''),
        };

        // Shown whatever the status: the fault may belong to another rule on this stage.
        if ($upcoming->failureReason !== '') {
            $fires .= '<div class="small text-warning-emphasis mt-1">'
                . '<span class="icon-warning" aria-hidden="true"></span> '
                . htmlspecialchars($upcoming->failureReason, ENT_QUOTES, 'UTF-8')
                . '</div>';

            if ($upcoming->failedAt !== null) {
                $fires .= '<div class="small">'
                    . Text::sprintf(
                        'COM_WORKFLOW_UPCOMING_LAST_FAILED',
                        HTMLHelper::_('date', $upcoming->failedAt->format('Y-m-d H:i:s'), Text::_('DATE_FORMAT_LC2'))
                    )
                    . '</div>';
            }
        }

        if ($upcoming->ruleType === 'cron') {
            $trigger = Text::sprintf(
                'COM_WORKFLOW_UPCOMING_TRIGGER_CRON',
                '<code>' . htmlspecialchars((string) $upcoming->cronExpression, ENT_QUOTES, 'UTF-8') . '</code>'
            );
        } else {
            $unitKeys = [
                'minutes' => 'COM_WORKFLOW_AUTOMATION_UNIT_MINUTES',
                'hours'   => 'COM_WORKFLOW_AUTOMATION_UNIT_HOURS',
                'days'    => 'COM_WORKFLOW_AUTOMATION_UNIT_DAYS',
                'months'  => 'COM_WORKFLOW_AUTOMATION_UNIT_MONTHS',
            ];
            $unitKey = $unitKeys[$upcoming->delayUnit] ?? '';
            $unit    = $unitKey !== '' ? Text::_($unitKey) : (string) $upcoming->delayUnit;
            $trigger = Text::sprintf('COM_WORKFLOW_UPCOMING_TRIGGER_DELAY', (int) $upcoming->delayValue, $unit);
        }

        $arrow = Factory::getApplication()->getLanguage()->isRtl() ? 'arrow-left' : 'arrow-right';
        return '<div class="card mb-3">'
            . '<div class="card-body">'
            . '<h4 class="h6 text-uppercase mb-2">' . Text::_('COM_WORKFLOW_UPCOMING_ARTICLE_LABEL') . '</h4>'
            . '<div class="mb-2">'
            . '<span class="badge bg-secondary">' . htmlspecialchars(Text::_($upcoming->fromStage), ENT_QUOTES, 'UTF-8') . '</span> '
            . '<span class="icon-' . $arrow . '" aria-hidden="true"></span> '
            . '<span class="badge bg-secondary">' . htmlspecialchars(Text::_($upcoming->toStage), ENT_QUOTES, 'UTF-8') . '</span>'
            . '</div>'
            . '<div class="mb-1">' . $fires . '</div>'
            . '<div class="small">' . $trigger . '</div>'
            . '</div></div>';
    }
}
