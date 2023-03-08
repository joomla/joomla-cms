<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Controller;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Component\Guidedtours\Administrator\Extension\GuidedtoursComponent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for a single Tour
 *
 * @since 4.3.0
 */
class TourController extends FormController
{
    /**
     * A mapping for the step types
     *
     * @var    string[]
     * @since  4.3.0
     */
    protected $stepType = [
        GuidedtoursComponent::STEP_NEXT        => 'next',
        GuidedtoursComponent::STEP_REDIRECT    => 'redirect',
        GuidedtoursComponent::STEP_INTERACTIVE => 'interactive',
    ];

    /**
     * A mapping for the step interactive types
     *
     * @var    string[]
     * @since  4.3.0
     */
    protected $stepInteractiveType = [
        GuidedtoursComponent::STEP_INTERACTIVETYPE_FORM_SUBMIT => 'submit',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_TEXT        => 'text',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_OTHER       => 'other',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_BUTTON      => 'button',
    ];

    /**
     * Retrieve and starts a tour and its steps through Ajax.
     *
     * @return null|object
     *
     * @since   4.3.0
     */
    public function start()
    {
        $tour = $this->getTour($this->input->getInt('id'));

        if (!$tour) {
            echo new JsonResponse($this->app->getLanguage()->_('COM_GUIDEDTOURS_ERROR_TOUR_NOT_FOUND'), true);
            return;
        }

        echo new JsonResponse($tour);
    }

    /**
     * Get a tour and its steps or null if not found.
     *
     * @param   integer  $tourId  The ID of the tour to load
     *
     * @return null|object
     *
     * @since   4.3.0
     */
    private function getTour(int $tourId)
    {
        $app = $this->app;

        $tourModel = $this->getModel('Tour', 'Administrator', ['ignore_request' => true]);

        $item = $tourModel->getItem($tourId);

        if (empty($item->id) || $item->published < 1 || !in_array($item->access, $app->getIdentity()->getAuthorisedViewLevels())) {
            return null;
        }

        // We don't want to show all parameters, so take only a subset of the tour attributes
        $tour = new \stdClass();

        $tour->id = $item->id;

        $stepsModel = $this->getModel('Steps', 'Administrator', ['ignore_request' => true]);

        $stepsModel->setState('filter.tour_id', $item->id);
        $stepsModel->setState('filter.published', 1);
        $stepsModel->setState('list.ordering', 'a.ordering');
        $stepsModel->setState('list.direction', 'ASC');

        $steps = $stepsModel->getItems();

        $tour->steps = [];

        $temp = new \stdClass();

        $temp->id          = 0;
        $temp->title       = $this->app->getLanguage()->_($item->title);
        $temp->description = $this->app->getLanguage()->_($item->description);
        $temp->url         = $item->url;

        // Replace 'images/' to '../images/' when using an image from /images in backend.
        $temp->description = preg_replace('*src\=\"(?!administrator\/)images/*', 'src="../images/', $temp->description);

        $tour->steps[] = $temp;

        foreach ($steps as $i => $step) {
            $temp = new \stdClass();

            $temp->id               = $i + 1;
            $temp->title            = $this->app->getLanguage()->_($step->title);
            $temp->description      = $this->app->getLanguage()->_($step->description);
            $temp->position         = $step->position;
            $temp->target           = $step->target;
            $temp->type             = $this->stepType[$step->type];
            $temp->interactive_type = $this->stepInteractiveType[$step->interactive_type];
            $temp->url              = $step->url;

            // Replace 'images/' to '../images/' when using an image from /images in backend.
            $temp->description = preg_replace('*src\=\"(?!administrator\/)images/*', 'src="../images/', $temp->description);

            $tour->steps[] = $temp;
        }

        return $tour;
    }
}
