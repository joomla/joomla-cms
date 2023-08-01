<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event;

use Joomla\CMS\Event\Plugin\System\Webauthn\Ajax as PlgSystemWebauthnAjax;
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxChallenge as PlgSystemWebauthnAjaxChallenge;
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxCreate as PlgSystemWebauthnAjaxCreate;
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxDelete as PlgSystemWebauthnAjaxDelete;
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxInitCreate as PlgSystemWebauthnAjaxInitCreate;
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxLogin as PlgSystemWebauthnAjaxLogin;
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxSaveLabel as PlgSystemWebauthnAjaxSaveLabel;
use Joomla\Event\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Returns the most suitable event class for a Joomla core event name
 *
 * @since 4.2.0
 */
trait CoreEventAware
{
    /**
     * Maps event names to concrete Event classes.
     *
     * This is only for events with invariable names. Events with variable names are handled with
     * PHP logic in the getEventClassByEventName class.
     *
     * @var   array
     * @since 4.2.0
     */
    private static $eventNameToConcreteClass = [
        // Application
        'onBeforeExecute'     => Application\BeforeExecuteEvent::class,
        'onAfterExecute'      => Application\AfterExecuteEvent::class,
        'onAfterInitialise'   => Application\AfterInitialiseEvent::class,
        'onAfterRoute'        => Application\AfterRouteEvent::class,
        'onBeforeApiRoute'    => Application\BeforeApiRouteEvent::class,
        'onAfterApiRoute'     => Application\AfterApiRouteEvent::class,
        'onAfterDispatch'     => Application\AfterDispatchEvent::class,
        'onBeforeRender'      => Application\BeforeRenderEvent::class,
        'onAfterRender'       => Application\AfterRenderEvent::class,
        'onBeforeCompileHead' => Application\BeforeCompileHeadEvent::class,
        'onAfterCompress'     => Application\AfterCompressEvent::class,
        'onBeforeRespond'     => Application\BeforeRespondEvent::class,
        'onAfterRespond'      => Application\AfterRespondEvent::class,
        'onError'             => ErrorEvent::class,
        // Model
        'onBeforeBatch' => Model\BeforeBatchEvent::class,
        // Quickicon
        'onGetIcon' => QuickIcon\GetIconEvent::class,
        // Table
        'onTableAfterBind'      => Table\AfterBindEvent::class,
        'onTableAfterCheckin'   => Table\AfterCheckinEvent::class,
        'onTableAfterCheckout'  => Table\AfterCheckoutEvent::class,
        'onTableAfterDelete'    => Table\AfterDeleteEvent::class,
        'onTableAfterHit'       => Table\AfterHitEvent::class,
        'onTableAfterLoad'      => Table\AfterLoadEvent::class,
        'onTableAfterMove'      => Table\AfterMoveEvent::class,
        'onTableAfterPublish'   => Table\AfterPublishEvent::class,
        'onTableAfterReorder'   => Table\AfterReorderEvent::class,
        'onTableAfterReset'     => Table\AfterResetEvent::class,
        'onTableAfterStore'     => Table\AfterStoreEvent::class,
        'onTableBeforeBind'     => Table\BeforeBindEvent::class,
        'onTableBeforeCheckin'  => Table\BeforeCheckinEvent::class,
        'onTableBeforeCheckout' => Table\BeforeCheckoutEvent::class,
        'onTableBeforeDelete'   => Table\BeforeDeleteEvent::class,
        'onTableBeforeHit'      => Table\BeforeHitEvent::class,
        'onTableBeforeLoad'     => Table\BeforeLoadEvent::class,
        'onTableBeforeMove'     => Table\BeforeMoveEvent::class,
        'onTableBeforePublish'  => Table\BeforePublishEvent::class,
        'onTableBeforeReorder'  => Table\BeforeReorderEvent::class,
        'onTableBeforeReset'    => Table\BeforeResetEvent::class,
        'onTableBeforeStore'    => Table\BeforeStoreEvent::class,
        'onTableCheck'          => Table\CheckEvent::class,
        'onTableObjectCreate'   => Table\ObjectCreateEvent::class,
        'onTableSetNewTags'     => Table\SetNewTagsEvent::class,
        // View
        'onBeforeDisplay' => View\DisplayEvent::class,
        'onAfterDisplay'  => View\DisplayEvent::class,
        // Workflow
        'onWorkflowFunctionalityUsed' => Workflow\WorkflowFunctionalityUsedEvent::class,
        'onWorkflowAfterTransition'   => Workflow\WorkflowTransitionEvent::class,
        'onWorkflowBeforeTransition'  => Workflow\WorkflowTransitionEvent::class,
        // Plugin: System, WebAuthn
        'onAjaxWebauthn'           => PlgSystemWebauthnAjax::class,
        'onAjaxWebauthnChallenge'  => PlgSystemWebauthnAjaxChallenge::class,
        'onAjaxWebauthnCreate'     => PlgSystemWebauthnAjaxCreate::class,
        'onAjaxWebauthnDelete'     => PlgSystemWebauthnAjaxDelete::class,
        'onAjaxWebauthnInitcreate' => PlgSystemWebauthnAjaxInitCreate::class,
        'onAjaxWebauthnLogin'      => PlgSystemWebauthnAjaxLogin::class,
        'onAjaxWebauthnSavelabel'  => PlgSystemWebauthnAjaxSaveLabel::class,
        // Extensions
        'onBeforeExtensionBoot' => BeforeExtensionBootEvent::class,
        'onAfterExtensionBoot'  => AfterExtensionBootEvent::class,
    ];

    /**
     * Get the concrete event class name for the given event name.
     *
     * This method falls back to the generic Joomla\Event\Event class if the event name is unknown
     * to this trait.
     *
     * @param   string  $eventName  The event name
     *
     * @return  string The event class name
     * @since 4.2.0
     */
    protected static function getEventClassByEventName(string $eventName): string
    {
        return self::$eventNameToConcreteClass[$eventName] ?? Event::class;
    }
}
