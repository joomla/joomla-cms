<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_guidedtours
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Controller;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Response\JsonResponse;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The guided tours controller for ajax requests.
 *
 * @since  5.2.0
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
        $user = $this->app->getIdentity();

        $tourId     = $this->app->getInput()->getInt('tid', 0);
        $stepNumber = $this->app->getInput()->getInt('sid', 0);
        $context    = $this->app->getInput()->getString('context', '');

        if ($user == null || $user->id <= 0) {
            echo new JsonResponse(['success' => false, 'tourId' => $tourId], Text::_('COM_GUIDEDTOURS_USERSTATE_CONNECTEDONLY'), true);
            $this->app->close();
        }

        if (!\in_array($context, ['tour.complete', 'tour.cancel', 'tour.skip'])) {
            echo new JsonResponse(['success' => false, 'tourId' => $tourId], Text::_('COM_GUIDEDTOURS_USERSTATE_WRONGCONTEXT'), true);
            $this->app->close();
        }

        if ($tourId <= 0) {
            echo new JsonResponse(['success' => false, 'tourId' => $tourId], Text::_('COM_GUIDEDTOURS_USERSTATE_BADTOURID'), true);
            $this->app->close();
        }

        $actionState = '';

        switch ($context) {
            case 'tour.complete':
                $actionState = 'completed';
                break;
            case 'tour.cancel':
                $actionState = 'delayed';
                break;
            case 'tour.skip':
                $actionState = 'skipped';
                break;
        }

        PluginHelper::importPlugin('guidedtours');

        // event onBeforeTourSaveUserState before save user tour state
        $beforeEvent = AbstractEvent::create(
            'onBeforeTourSaveUserState',
            [
                'subject'     => new \stdClass(),
                'tourId'      => $tourId,
                'actionState' => $actionState,
                'stepNumber'  => $stepNumber,
            ]
        );

        $this->app->getDispatcher()->dispatch('onBeforeTourSaveUserState', $beforeEvent);

        // Save the tour state only when the tour auto-starts.
        $tourModel = $this->getModel('Tour', 'Administrator');
        if ($tourModel->isAutostart($tourId)) {
            $result = $tourModel->saveTourUserState($tourId, $actionState);
            if ($result) {
                $message = Text::sprintf('COM_GUIDEDTOURS_USERSTATE_STATESAVED', $user->id, $tourId);
            } else {
                $message = Text::sprintf('COM_GUIDEDTOURS_USERSTATE_STATENOTSAVED', $user->id, $tourId);
            }
        } else {
            $result  = false;
            $message = Text::sprintf('COM_GUIDEDTOURS_USERSTATE_STATENOTSAVED', $user->id, $tourId);
        }

        // event onAfterTourSaveUserState after save user tour state (may override message)
        $afterEvent = AbstractEvent::create(
            'onAfterTourSaveUserState',
            [
                'subject'     => new \stdClass(),
                'tourId'      => $tourId,
                'actionState' => $actionState,
                'stepNumber'  => $stepNumber,
                'result'      => $result,
                'message'     => &$message,
            ]
        );

        $this->app->getDispatcher()->dispatch('onAfterTourSaveUserState', $afterEvent);

        // Construct the response data
        $data = [
            'tourId'  => $tourId,
            'stepId'  => $stepNumber,
            'context' => $context,
            'state'   => $actionState,
        ];
        echo new JsonResponse($data, $message);
        $this->app->close();
    }
}
