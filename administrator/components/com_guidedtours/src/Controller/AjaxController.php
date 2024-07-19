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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Component\ComponentHelper;
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
        $jinput = $this->app->input;
        $user   = $this->app->getIdentity();

        $tourId  = $jinput->getInt('tid', 0);
        $stepId  = $jinput->getString('sid', '');
        $context = $jinput->getString('context', '');

        if ($user != null && $user->id > 0) {
            $actionState   = '';
            $relaunchDelay = 0;

            switch($context) {
                case "tour.complete":
                    $actionState = "completed";
                    break;
                case "tour.cancel":
                    $actionState   = "delayed";                
                    $params        = ComponentHelper::getParams('com_guidedtours');
                    $relaunchDelay = $params->get('delayed_time', '600');
                    break;
                case "tour.skip":
                    $actionState = "skipped";
                    break;
            }

            PluginHelper::importPlugin('guidedtours');

            // event onBeforeTourSaveState before save user tour state
            $this->app->triggerEvent('onBeforeTourSaveState', [$tourId, $stepId, $actionState]);
                
            $retSave = $this->saveTourUserState($user->id, $tourId, $stepId, $actionState, $relaunchDelay);
            if ($retSave) {
                $msgSave = "Tour #$tourId state '$actionState' (delay: $relaunchDelay) has been saved for user #$user->id."; // TODO translate
            } else {
                $msgSave = "Profile not saved for user #$user->id tour #$tourId"; // TODO translate
            }

            // Log the user tour state in the user action logs
            $this->app->triggerEvent('onTourRunSaveState', [$tourId, $actionState, $stepId]);

            // event onAfterTourSaveState after save user tour state (may override msgSave)
            $this->app->triggerEvent('onAfterTourSaveState', [$tourId, $stepId, $actionState, $retSave, &$msgSave]);

            // Construct the response data
            $data = [
                'tourId'  => $tourId,
                'stepId'  => $stepId,
                'context' => $context,
                'state'   => $actionState,
            ];
            echo new JsonResponse($data, $msgSave);
            jexit();
        } else {
            // Construct the response data
            $data = [
                'success' => false,
                'tourId'  => $tourId
            ];

            $msg = "Tour User state action is only for connected users."; // TODO translate
            $isError = true;
            echo new JsonResponse($data, $msg, $isError);
            jexit();
        }
    }

    /**
     * The profile data saving.
     *
     * @param   int      $userId         The ID of the user
     * @param   int      $tourId         The ID of the tour
     * @param   string   $stepNumber     The step number
     * @param   string   $state          The label of the state to be saved (completed, delayed or skipped)
     * @param   int      $relaunchDelay  The delay (in seconds) before relaunching the tour (only for 'delayed' state)
     *
     * @return  boolean
     *
     * @since  __DEPLOY_VERSION__
     */
    private function saveTourUserState($userId, $tourId, $stepNumber, $state, $relaunchDelay = 0)
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
