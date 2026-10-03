<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Rule;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Decides whether the current user may make an automation rule run as the chosen account.
 *
 * @since  __DEPLOY_VERSION__
 */
class RunAsUserRule extends FormRule implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * @param   \SimpleXMLElement  $element  The field's XML definition.
     * @param   mixed              $value    The chosen user id.
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
        if ($input === null || (int) $input->get('automation.automation_enabled', 0) !== 1) {
            return true;
        }

        $candidate = (int) $value;

        if ($candidate === 0) {
            throw new \RuntimeException(Text::_('COM_WORKFLOW_AUTOMATION_ERROR_NO_RUN_AS'));
        }

        $user = Factory::getApplication()->getIdentity();

        // A super user may choose anyone. Anyone else may choose themselves, or keep the account
        // the rule already runs as, so an existing rule survives being edited by a lesser user.
        if ($user->authorise('core.admin') || $candidate === (int) $user->id) {
            return true;
        }

        if ($candidate === $this->storedRunAsUserId((int) $input->get('id', 0))) {
            return true;
        }

        throw new \RuntimeException(Text::_('COM_WORKFLOW_AUTOMATION_ERROR_RUN_AS_NOT_ALLOWED'));
    }

    /**
     * The run-as user already stored for this transition, or 0 when there is no rule yet.
     *
     * @param   integer  $transitionId  The transition being saved.
     *
     * @return  integer
     *
     * @since   __DEPLOY_VERSION__
     */
    private function storedRunAsUserId(int $transitionId): int
    {
        if ($transitionId <= 0) {
            return 0;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('run_as_user_id'))
            ->from($db->quoteName('#__workflow_automation_rules'))
            ->where($db->quoteName('transition_id') . ' = :transitionId')
            ->bind(':transitionId', $transitionId, ParameterType::INTEGER);

        return (int) $db->setQuery($query)->loadResult();
    }
}
