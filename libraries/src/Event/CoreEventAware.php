<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event;

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
        // Application configuration
        'onApplicationBeforeSave' => Application\BeforeSaveConfigurationEvent::class,
        'onApplicationAfterSave'  => Application\AfterSaveConfigurationEvent::class,
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
        // Plugin: System, Schemaorg
        'onSchemaBeforeCompileHead' => Plugin\System\Schemaorg\BeforeCompileHeadEvent::class,
        'onSchemaPrepareData'       => Plugin\System\Schemaorg\PrepareDataEvent::class,
        'onSchemaPrepareForm'       => Plugin\System\Schemaorg\PrepareFormEvent::class,
        'onSchemaPrepareSave'       => Plugin\System\Schemaorg\PrepareSaveEvent::class,
        // Plugin: Stats
        'onGetStatsData' => Plugin\System\Stats\GetStatsDataEvent::class,
        // Content
        'onContentPrepare'       => Content\ContentPrepareEvent::class,
        'onContentAfterTitle'    => Content\AfterTitleEvent::class,
        'onContentBeforeDisplay' => Content\BeforeDisplayEvent::class,
        'onContentAfterDisplay'  => Content\AfterDisplayEvent::class,
        // Model
        'onContentNormaliseRequestData' => Model\NormaliseRequestDataEvent::class,
        'onContentBeforeValidateData'   => Model\BeforeValidateDataEvent::class,
        'onContentPrepareForm'          => Model\PrepareFormEvent::class,
        'onContentPrepareData'          => Model\PrepareDataEvent::class,
        'onContentBeforeSave'           => Model\BeforeSaveEvent::class,
        'onContentAfterSave'            => Model\AfterSaveEvent::class,
        'onContentBeforeDelete'         => Model\BeforeDeleteEvent::class,
        'onContentAfterDelete'          => Model\AfterDeleteEvent::class,
        'onContentBeforeChangeState'    => Model\BeforeChangeStateEvent::class,
        'onContentChangeState'          => Model\AfterChangeStateEvent::class,
        'onCategoryChangeState'         => Model\AfterCategoryChangeStateEvent::class,
        'onBeforeBatch'                 => Model\BeforeBatchEvent::class,
        // User
        'onUserAuthenticate'         => User\AuthenticationEvent::class,
        'onUserAuthorisation'        => User\AuthorisationEvent::class,
        'onUserAuthorisationFailure' => User\AuthorisationFailureEvent::class,
        'onUserLogin'                => User\LoginEvent::class,
        'onUserAfterLogin'           => User\AfterLoginEvent::class,
        'onUserLoginFailure'         => User\LoginFailureEvent::class,
        'onUserLogout'               => User\LogoutEvent::class,
        'onUserAfterLogout'          => User\AfterLogoutEvent::class,
        'onUserLogoutFailure'        => User\LogoutFailureEvent::class,
        'onUserLoginButtons'         => User\LoginButtonsEvent::class,
        'onUserBeforeSave'           => User\BeforeSaveEvent::class,
        'onUserAfterSave'            => User\AfterSaveEvent::class,
        'onUserBeforeDelete'         => User\BeforeDeleteEvent::class,
        'onUserAfterDelete'          => User\AfterDeleteEvent::class,
        'onUserAfterRemind'          => User\AfterRemindEvent::class,
        'onUserBeforeResetRequest'   => User\BeforeResetRequestEvent::class,
        'onUserAfterResetRequest'    => User\AfterResetRequestEvent::class,
        'onUserBeforeResetComplete'  => User\BeforeResetCompleteEvent::class,
        'onUserAfterResetComplete'   => User\AfterResetCompleteEvent::class,
        // User Group
        'onUserBeforeSaveGroup'   => Model\BeforeSaveEvent::class,
        'onUserAfterSaveGroup'    => Model\AfterSaveEvent::class,
        'onUserBeforeDeleteGroup' => Model\BeforeDeleteEvent::class,
        'onUserAfterDeleteGroup'  => Model\AfterDeleteEvent::class,
        // Modules
        'onRenderModule'         => Module\BeforeRenderModuleEvent::class,
        'onAfterRenderModule'    => Module\AfterRenderModuleEvent::class,
        'onAfterRenderModules'   => Module\AfterRenderModulesEvent::class,
        'onPrepareModuleList'    => Module\PrepareModuleListEvent::class,
        'onAfterModuleList'      => Module\AfterModuleListEvent::class,
        'onAfterCleanModuleList' => Module\AfterCleanModuleListEvent::class,
        // Extension
        'onBeforeExtensionBoot'      => BeforeExtensionBootEvent::class,
        'onAfterExtensionBoot'       => AfterExtensionBootEvent::class,
        'onExtensionBeforeInstall'   => Extension\BeforeInstallEvent::class,
        'onExtensionAfterInstall'    => Extension\AfterInstallEvent::class,
        'onExtensionBeforeUninstall' => Extension\BeforeUninstallEvent::class,
        'onExtensionAfterUninstall'  => Extension\AfterUninstallEvent::class,
        'onExtensionBeforeUpdate'    => Extension\BeforeUpdateEvent::class,
        'onExtensionAfterUpdate'     => Extension\AfterUpdateEvent::class,
        'onExtensionBeforeSave'      => Model\BeforeSaveEvent::class,
        'onExtensionAfterSave'       => Model\AfterSaveEvent::class,
        'onExtensionAfterDelete'     => Model\AfterDeleteEvent::class,
        'onExtensionChangeState'     => Model\BeforeChangeStateEvent::class,
        'onJoomlaBeforeUpdate'       => Extension\BeforeJoomlaUpdateEvent::class,
        'onJoomlaAfterUpdate'        => Extension\AfterJoomlaUpdateEvent::class,
        // Installer
        'onInstallerAddInstallationTab'    => Installer\AddInstallationTabEvent::class,
        'onInstallerBeforeInstallation'    => Installer\BeforeInstallationEvent::class,
        'onInstallerBeforeInstaller'       => Installer\BeforeInstallerEvent::class,
        'onInstallerAfterInstaller'        => Installer\AfterInstallerEvent::class,
        'onInstallerBeforePackageDownload' => Installer\BeforePackageDownloadEvent::class,
        // Finder
        'onFinderCategoryChangeState' => Finder\AfterCategoryChangeStateEvent::class,
        'onFinderChangeState'         => Finder\AfterChangeStateEvent::class,
        'onFinderAfterDelete'         => Finder\AfterDeleteEvent::class,
        'onFinderBeforeSave'          => Finder\BeforeSaveEvent::class,
        'onFinderAfterSave'           => Finder\AfterSaveEvent::class,
        'onFinderResult'              => Finder\ResultEvent::class,
        'onPrepareFinderContent'      => Finder\PrepareContentEvent::class,
        'onBeforeIndex'               => Finder\BeforeIndexEvent::class,
        'onBuildIndex'                => Finder\BuildIndexEvent::class,
        'onStartIndex'                => Finder\StartIndexEvent::class,
        'onFinderGarbageCollection'   => Finder\GarbageCollectionEvent::class,
        // Menu
        'onBeforeRenderMenuItems'   => Menu\BeforeRenderMenuItemsViewEvent::class,
        'onAfterGetMenuTypeOptions' => Menu\AfterGetMenuTypeOptionsEvent::class,
        'onPreprocessMenuItems'     => Menu\PreprocessMenuItemsEvent::class,
        // ActionLog
        'onAfterLogPurge'  => ActionLog\AfterLogPurgeEvent::class,
        'onAfterLogExport' => ActionLog\AfterLogExportEvent::class,
        // Cache
        'onAfterPurge' => Cache\AfterPurgeEvent::class,
        // Contact
        'onValidateContact' => Contact\ValidateContactEvent::class,
        'onSubmitContact'   => Contact\SubmitContactEvent::class,
        // Checkin
        'onAfterCheckin' => Checkin\AfterCheckinEvent::class,
        // Custom Fields
        'onCustomFieldsGetTypes'           => CustomFields\GetTypesEvent::class,
        'onCustomFieldsPrepareDom'         => CustomFields\PrepareDomEvent::class,
        'onCustomFieldsBeforePrepareField' => CustomFields\BeforePrepareFieldEvent::class,
        'onCustomFieldsPrepareField'       => CustomFields\PrepareFieldEvent::class,
        'onCustomFieldsAfterPrepareField'  => CustomFields\AfterPrepareFieldEvent::class,
        // Privacy
        'onPrivacyCollectAdminCapabilities'    => Privacy\CollectCapabilitiesEvent::class,
        'onPrivacyCheckPrivacyPolicyPublished' => Privacy\CheckPrivacyPolicyPublishedEvent::class,
        'onPrivacyExportRequest'               => Privacy\ExportRequestEvent::class,
        'onPrivacyCanRemoveData'               => Privacy\CanRemoveDataEvent::class,
        'onPrivacyRemoveData'                  => Privacy\RemoveDataEvent::class,
        // PageCache
        'onPageCacheSetCaching' => PageCache\SetCachingEvent::class,
        'onPageCacheGetKey'     => PageCache\GetKeyEvent::class,
        'onPageCacheIsExcluded' => PageCache\IsExcludedEvent::class,
        // Sample Data
        'onSampledataGetOverview' => SampleData\GetOverviewEvent::class,
        // Mail
        'onMailBeforeRendering' => Mail\BeforeRenderingMailTemplateEvent::class,
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
