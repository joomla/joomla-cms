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
use Joomla\CMS\Session\Session;
use Joomla\Component\Guidedtours\Administrator\Extension\GuidedtoursComponent;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Guided Tours plugin to add interactive tours to the administrator interface.
 *
 * @since  4.3.0
 */
final class GuidedTours extends CMSPlugin implements SubscriberInterface
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
     * An internal flag whether plugin should listen any event.
     *
     * @var bool
     *
     * @since   4.3.0
     */
    protected static $enabled = false;

    /**
     * Constructor
     *
     * @param   DispatcherInterface  $subject  The object to observe
     * @param   array                $config   An optional associative array of configuration settings.
     * @param   boolean              $enabled  An internal flag whether plugin should listen any event.
     *
     * @since   4.3.0
     */
    public function __construct($subject, array $config = [], bool $enabled = false)
    {
        $this->autoloadLanguage = $enabled;
        self::$enabled          = $enabled;

        parent::__construct($subject, $config);
    }

    /**
     * function for getSubscribedEvents : new Joomla 4 feature
     *
     * @return array
     *
     * @since   4.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return self::$enabled ? [
            'onAjaxGuidedtours'   => 'startTour',
            'onBeforeCompileHead' => 'onBeforeCompileHead',
        ] : [];
    }

    /**
     * Retrieve and starts a tour and its steps through Ajax.
     *
     * @return null|object
     *
     * @since   4.3.0
     */
    public function startTour(Event $event)
    {
        $tourId = (int) $this->getApplication()->getInput()->getInt('id');

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
     * @since   4.3.0
     */
    public function onBeforeCompileHead()
    {
        $app  = $this->getApplication();
        $doc  = $app->getDocument();
        $user = $app->getIdentity();

        if ($user != null && $user->id > 0) {
            Text::script('JCANCEL');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_BACK');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_COMPLETE');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_COULD_NOT_LOAD_THE_TOUR');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_NEXT');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_START');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_STEP_NUMBER_OF');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_TOUR_ERROR');

            $doc->addScriptOptions('com_guidedtours.token', Session::getFormToken());

            // Load required assets
            $doc->getWebAssetManager()
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
     * @since   4.3.0
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
        $temp->title       = $this->getApplication()->getLanguage()->_($item->title);
        $temp->description = $this->getApplication()->getLanguage()->_($item->description);
        $temp->url         = $item->url;

        // Replace 'images/' to '../images/' when using an image from /images in backend.
        $temp->description = preg_replace('*src\=\"(?!administrator\/)images/*', 'src="../images/', $temp->description);

        $tour->steps[] = $temp;

        foreach ($steps as $i => $step) {
            $temp = new \stdClass();

            $temp->id               = $i + 1;
            $temp->title            = $this->getApplication()->getLanguage()->_($step->title);
            $temp->description      = $this->getApplication()->getLanguage()->_($step->description);
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
