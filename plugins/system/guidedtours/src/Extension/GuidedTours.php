<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\GuidedTours\Extension;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Guidedtours\Administrator\Extension\GuidedtoursComponent;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Guided Tours plugin to add interactive tours to the administrator interface.
 *
 * @since  __DEPLOY_VERSION__
 */
final class GuidedTours extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * A mapping for the step types
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
     */
    protected $stepInteractiveType = [
        GuidedtoursComponent::STEP_INTERACTIVETYPE_FORM_SUBMIT => 'submit',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_TEXT        => 'text',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_OTHER       => 'other',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_BUTTON      => 'button',
    ];

    /**
     * function for getSubscribedEvents : new Joomla 4 feature
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAjaxGuidedtours'   => 'startTour',
            'onBeforeCompileHead' => 'onBeforeCompileHead',
        ];
    }

    /**
     * Retrieve and starts a tour and its steps through Ajax.
     *
     * @return null|object
     *
     * @since   __DEPLOY_VERSION__
     */
    public function startTour(Event $event)
    {
        $app = $this->getApplication();

        if (!$app->isClient('administrator')) {
            return null;
        }

        $tourId = (int) $app->getInput()->getInt('id');

        $activeTourId = null;
        $tour         = null;

        if ($tourId > 0) {
            $tour = $this->getTour($tourId);

            if (!empty($tour->id)) {
                $activeTourId = $tour->id;
            }
        }

        $event->setArgument('result', $tour ?? new \stdClass());

        return $tour;
    }

    /**
     * Listener for the `onBeforeCompileHead` event
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onBeforeCompileHead()
    {
        $app = $this->getApplication();

        if ($app->isClient('administrator')) {
            Text::script('JCANCEL');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_START');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_COMPLETE');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_NEXT');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_BACK');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_COULD_NOT_LOAD_THE_TOUR');

            // Load required assets
            $app->getDocument()->getWebAssetManager()
                ->usePreset('plg_system_guidedtours.guidedtours');
        }
    }

    /**
     * Get a tour and its steps or null if not found
     *
     * @param   integer  $tourId  The ID of the tour to load
     *
     * @return null|object
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getTour(int $tourId)
    {
        $app = $this->getApplication();

        $user = $app->getIdentity();

        $factory = $app->bootComponent('com_guidedtours')->getMVCFactory();

        $tourModel = $factory->createModel(
            'Tour',
            'Administrator',
            ['ignore_request' => true]
        );

        $item = $tourModel->getItem($tourId);

        if (empty($item->id) || $item->published < 1 || !in_array($item->access, $user->getAuthorisedViewLevels())) {
            return null;
        }

        // We don't want to show all parameters, so take only a subset of the tour attributes
        $tour = new \stdClass();

        $tour->id = $item->id;

        $stepsModel = $factory->createModel(
            'Steps',
            'Administrator',
            ['ignore_request' => true]
        );

        $stepsModel->setState('filter.tour_id', $item->id);
        $stepsModel->setState('filter.published', 1);
        $stepsModel->setState('list.ordering', 'a.ordering');
        $stepsModel->setState('list.direction', 'ASC');

        $steps = $stepsModel->getItems();

        $tour->steps = [];

        $temp = new \stdClass();

        $temp->id          = 0;
        $temp->title       = Text::_($item->title);
        $temp->description = Text::_($item->description);
        $temp->url         = $item->url;

        // Replace 'images/' to '../images/' when using an image from /images in backend.
        $temp->description = preg_replace('*src\=\"(?!administrator\/)images/*', 'src="../images/', $temp->description);

        $tour->steps[] = $temp;

        foreach ($steps as $i => $step) {
            $temp = new \stdClass();

            $temp->id               = $i + 1;
            $temp->title            = Text::_($step->title);
            $temp->description      = Text::_($step->description);
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
