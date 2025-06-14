<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\GuidedTours\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Component\Guidedtours\Administrator\Extension\GuidedtoursComponent;
use Joomla\Component\Guidedtours\Administrator\Model\TourModel;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
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
    use DatabaseAwareTrait;

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
        GuidedtoursComponent::STEP_INTERACTIVETYPE_FORM_SUBMIT    => 'submit',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_TEXT           => 'text',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_OTHER          => 'other',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_BUTTON         => 'button',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_CHECKBOX_RADIO => 'checkbox_radio',
        GuidedtoursComponent::STEP_INTERACTIVETYPE_SELECT         => 'select',
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
     * @param   DispatcherInterface  $dispatcher  The object to observe
     * @param   array                $config      An optional associative array of configuration settings.
     * @param   boolean              $enabled     An internal flag whether plugin should listen any event.
     *
     * @since   4.3.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config = [], bool $enabled = false)
    {
        self::$enabled = $enabled;

        parent::__construct($dispatcher, $config);
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
        $tourId  = (int) $this->getApplication()->getInput()->getInt('id');
        $tourUid = $this->getApplication()->getInput()->getString('uid', '');
        $tourUid = $tourUid !== '' ? urldecode($tourUid) : '';

        $tour = null;

        // Load plugin language files
        $this->loadLanguage();

        if ($tourId > 0) {
            $tour = $this->getTour($tourId);
        } elseif ($tourUid !== '') {
            $tour = $this->getTour($tourUid);
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
            // Load plugin language files.
            $this->loadLanguage();

            Text::script('JCANCEL');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_BACK');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_COMPLETE');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_COULD_NOT_LOAD_THE_TOUR');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_HIDE_FOREVER');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_NEXT');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_STEP_NUMBER_OF');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_TOUR_ERROR');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_TOUR_ERROR_RESPONSE');
            Text::script('PLG_SYSTEM_GUIDEDTOURS_TOUR_INVALID_RESPONSE');

            $doc->addScriptOptions('com_guidedtours.token', Session::getFormToken());
            $doc->addScriptOptions('com_guidedtours.autotour', '');

            // Load required assets.
            $doc->getWebAssetManager()
                ->usePreset('plg_system_guidedtours.guidedtours');

            $params = ComponentHelper::getParams('com_guidedtours');

            // Check if the user has opted out of auto-start
            $userAuthorizedAutostart = $user->getParam('allowTourAutoStart', $params->get('allowTourAutoStart', 1));
            if (!$userAuthorizedAutostart) {
                return;
            }

            // The following code only relates to the auto-start functionality.
            // First, we get the tours for the context.

            $factory = $app->bootComponent('com_guidedtours')->getMVCFactory();

            $toursModel = $factory->createModel(
                'Tours',
                'Administrator',
                ['ignore_request' => true]
            );

            $toursModel->setState('filter.extension', $app->getInput()->getCmd('option', 'com_cpanel'));
            $toursModel->setState('filter.published', 1);
            $toursModel->setState('filter.access', $user->getAuthorisedViewLevels());

            if (Multilanguage::isEnabled()) {
                $toursModel->setState('filter.language', ['*', $app->getLanguage()->getTag()]);
            }

            $tours = $toursModel->getItems();
            foreach ($tours as $tour) {
                // Look for the first autostart tour, if any.
                if ($tour->autostart) {
                    $db         = $this->getDatabase();
                    $profileKey = 'guidedtour.id.' . $tour->id;

                    // Check if the tour state has already been saved some time before.
                    $query = $db->getQuery(true)
                        ->select($db->quoteName('profile_value'))
                        ->from($db->quoteName('#__user_profiles'))
                        ->where($db->quoteName('user_id') . ' = :user_id')
                        ->where($db->quoteName('profile_key') . ' = :profileKey')
                        ->bind(':user_id', $user->id, ParameterType::INTEGER)
                        ->bind(':profileKey', $profileKey, ParameterType::STRING);

                    try {
                        $result = $db->setQuery($query)->loadResult();
                    } catch (\Exception) {
                        // Do not start the tour.
                        continue;
                    }

                    // A result has been found in the user profiles table
                    if (!\is_null($result)) {
                        $values = json_decode($result, true);

                        if (empty($values)) {
                            // Do not start the tour.
                            continue;
                        }

                        if ($values['state'] === 'skipped' || $values['state'] === 'completed') {
                            // Do not start the tour.
                            continue;
                        }

                        if ($values['state'] === 'delayed') {
                            $delay       = $params->get('delayed_time', '60');
                            $currentTime = Date::getInstance();
                            $loggedTime  = new Date($values['time']['date']);

                            if ($loggedTime->add(new \DateInterval('PT' . $delay . 'M')) > $currentTime) {
                                // Do not start the tour.
                                continue;
                            }
                        }
                    }

                    // We have a tour to auto start. No need to go any further.
                    $doc->addScriptOptions('com_guidedtours.autotour', $tour->id);
                    break;
                }
            }
        }
    }

    /**
     * Get a tour and its steps or null if not found
     *
     * @param   integer|string  $tourId  The ID or Uid of the tour to load
     *
     * @return null|object
     *
     * @since   4.3.0
     */
    private function getTour($tourId)
    {
        $app = $this->getApplication();

        $factory = $app->bootComponent('com_guidedtours')->getMVCFactory();

        /** @var TourModel $tourModel */
        $tourModel = $factory->createModel(
            'Tour',
            'Administrator',
            ['ignore_request' => true]
        );

        $item = $tourModel->getItem($tourId);

        return $this->processTour($item);
    }

    /**
     * Return a tour and its steps or null if not found
     *
     * @param   CMSObject  $item  The tour to load
     *
     * @return null|object
     *
     * @since   5.0.0
     */
    private function processTour($item)
    {
        $app = $this->getApplication();

        $user    = $app->getIdentity();
        $factory = $app->bootComponent('com_guidedtours')->getMVCFactory();

        if (empty($item->id) || $item->published < 1 || !\in_array($item->access, $user->getAuthorisedViewLevels())) {
            return null;
        }

        // We don't want to show all parameters, so take only a subset of the tour attributes
        $tour = new \stdClass();

        $tour->id        = $item->id;
        $tour->autostart = $item->autostart;

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
        $temp->description = $this->fixImagePaths($temp->description);
        $temp->url         = $item->url;

        // Set the start label for the tour.
        $temp->start_label = Text::_('PLG_SYSTEM_GUIDEDTOURS_START');
        // What's new tours have a different label.
        if (str_contains($item->uid, 'joomla-whatsnew')) {
            $temp->start_label = Text::_('PLG_SYSTEM_GUIDEDTOURS_NEXT');
        }

        $tour->steps[] = $temp;

        foreach ($steps as $i => $step) {
            $temp = new \stdClass();

            $temp->id               = $i + 1;
            $temp->title            = $this->getApplication()->getLanguage()->_($step->title);
            $temp->description      = $this->getApplication()->getLanguage()->_($step->description);
            $temp->description      = $this->fixImagePaths($temp->description);
            $temp->position         = $step->position;
            $temp->target           = $step->target;
            $temp->type             = $this->stepType[$step->type];
            $temp->interactive_type = $this->stepInteractiveType[$step->interactive_type];
            $temp->params           = $step->params;
            $temp->url              = $step->url;
            $temp->tour_id          = $step->tour_id;
            $temp->step_id          = $step->id;

            $tour->steps[] = $temp;
        }

        return $tour;
    }

    /**
     * Return a modified version of a given string with usable image paths for tours
     *
     * @param   string  $description  The string to fix
     *
     * @return  string
     *
     * @since  5.2.0
     */
    private function fixImagePaths($description)
    {
        return preg_replace(
            [
                '*src="(?!administrator\/)images/*',
                '*src="media/*',
            ],
            [
                'src="../images/',
                'src="../media/',
            ],
            $description
        );
    }
}
