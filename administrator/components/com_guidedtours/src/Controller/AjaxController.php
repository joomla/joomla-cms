<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_guidedtours
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Controller;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The guided tours controller for ajax requests.
 *
 * @since  __DEPLOY_VERSION__
 */
class AjaxController extends BaseController
{
    /**
     * Ajax call used when cancelling, skipping or completing a tour.
     * It allows:
     * - the trigering of before and after events the user state is recorded
     * - the recording of the user behavior in the action logs
     */
    public function fetchUserState()
    {
        $user   = $this->app->getIdentity();

        $tourId     = $this->app->input->getInt('tid', 0);
        $stepNumber = $this->app->input->getString('sid', '');
        $context    = $this->app->input->getString('context', '');

        if ($user != null && $user->id > 0) {
            $actionState   = '';

            switch($context) {
                case "tour.complete":
                    $actionState = "completed";
                    break;
                case "tour.cancel":
                    $actionState = "delayed";
                    break;
                case "tour.skip":
                    $actionState = "skipped";
                    break;
            }

            PluginHelper::importPlugin('guidedtours');

            // event onBeforeTourSaveState before save user tour state
            $this->app->triggerEvent('onBeforeTourRunSaveState', [$tourId, $actionState, $stepNumber]);

            // Log the user tour state in the user action logs
            $this->app->triggerEvent('onTourRunSaveState', [$tourId, $actionState, $stepNumber]);

            $result = $this->saveTourUserState($user->id, $tourId, $actionState);
            if ($result) {
                $message = Text::sprintf('COM_GUIDEDTOURS_USERSTATE_STATESAVED', $user->id, $tourId);
            } else {
                $message = Text::sprintf('COM_GUIDEDTOURS_USERSTATE_STATENOTSAVED', $user->id, $tourId);
            }

            // event onAfterTourSaveState after save user tour state (may override msgSave)
            $this->app->triggerEvent('onAfterTourRunSaveState', [$tourId, $actionState, $stepNumber, $result, &$message]);

            // Construct the response data
            $data = [
                'tourId'  => $tourId,
                'stepId'  => $stepNumber,
                'context' => $context,
                'state'   => $actionState,
            ];
            echo new JsonResponse($data, $message);
            $this->app->close();
        } else {
            // Construct the response data
            $data = [
                'success' => false,
                'tourId'  => $tourId
            ];

            $message = Text::_('COM_GUIDEDTOURS_USERSTATE_STATESAVE_CONNECTEDONLY');
            echo new JsonResponse($data, $message, true);
            $this->app->close();
        }
    }

    /**
     * The profile data saving.
     *
     * @param   int      $userId         The ID of the user
     * @param   int      $tourId         The ID of the tour
     * @param   string   $state          The label of the state to be saved (completed, delayed or skipped)
     *
     * @return  boolean
     *
     * @since  __DEPLOY_VERSION__
     */
    private function saveTourUserState($userId, $tourId, $state)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Check if the tour is set to autostart
        $query = $db->getQuery(true)
            ->select($db->quoteName('autostart'))
            ->from($db->quoteName('#__guidedtours'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $tourId, ParameterType::INTEGER);

        try {
            $autoStartResult = $db->setQuery($query)->loadResult();
        } catch (\Exception $e) {
            return false;
        }

        // The tour state is only saved in the user profile if the tour is set to autostart.
        if ($autoStartResult) {
            $profileKey = 'guidedtour.id.' . $tourId;
    
            // Check if the profile key already exists.
            $query = $db->getQuery(true)
                ->select($db->quoteName('profile_value'))
                ->from($db->quoteName('#__user_profiles'))
                ->where($db->quoteName('user_id') . ' = :user_id')
                ->where($db->quoteName('profile_key') . ' = :profileKey')
                ->bind(':user_id', $userId, ParameterType::INTEGER)
                ->bind(':profileKey', $profileKey, ParameterType::STRING);
    
            try {
                $result = $db->setQuery($query)->loadResult();
            } catch (\Exception $e) {
                return false;
            }

            $tourState = [];

            $tourState['state'] = $state;
            if ($state === 'delayed') {
                $tourState['time'] = Date::getInstance();
            }

            $profileObject = (object)[
                'user_id' => $userId,
                'profile_key' => $profileKey,
                'profile_value' => json_encode($tourState),
                'ordering' => 0,
            ];

            if (!is_null($result)) {
                $values = json_decode($result, true);

                if (!empty($values)) {
                    // The profile is updated only when delayed. 'Completed' and 'Skipped' are final
                    if ($values['state'] === 'delayed') {
                        $db->updateObject('#__user_profiles', $profileObject, ['user_id', 'profile_key']);
                    }
                }
            } else {
                $db->insertObject('#__user_profiles', $profileObject);
            }
        }
        
        return true;
    }
}
