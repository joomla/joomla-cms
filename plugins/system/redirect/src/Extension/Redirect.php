<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.redirect
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Redirect\Extension;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Application\AfterInitialiseDocumentEvent;
use Joomla\CMS\Event\ErrorEvent;
use Joomla\CMS\Event\Model\AfterSaveEvent;
use Joomla\CMS\Event\Model\BeforeSaveEvent;
use Joomla\CMS\Event\Model\PrepareDataEvent;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\HelperRedirectAwareInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\MenuFactoryInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\SiteRouter;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin class for redirect handling.
 *
 * @since  1.6
 */
final class Redirect extends CMSPlugin implements CurrentUserInterface, SubscriberInterface
{
    use DatabaseAwareTrait;
    use CurrentUserTrait;

    /**
     * Holds the SEF link of the item before it's saved
     *
     * @var Uri|null
     *
     * @since   __DEPLOY_VERSION__
     */
    private ?Uri $oldLink = null;

    /**
     * The Site Application for the special redirect handling
     *
     * @var  SiteApplication
     *
     * @since   __DEPLOY_VERSION__
     */
    private SiteApplication $siteApp;

    /**
     * Language Factory to create a language for the item
     *
     * @var  LanguageFactoryInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    private LanguageFactoryInterface $languageFactory;

    /**
     * Menu Factory to create a menu in guest context
     *
     * @var  MenuFactoryInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    private MenuFactoryInterface $menuFactory;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialiseDocument' => 'onAfterInitialiseDocument',
            'onContentAfterSave'        => 'onContentAfterSave',
            'onContentBeforeSave'       => 'onContentBeforeSave',
            'onContentPrepareData'      => 'onContentPrepareData',
            'onContentPrepareForm'      => 'onContentPrepareForm',
            'onError'                   => 'handleError',
        ];
    }

    /**
     * The save event.
     *
     * @param   BeforeSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onContentBeforeSave(BeforeSaveEvent $event): void
    {
        // No need to redirect if the item is new, so early return
        if ($event->getIsNew()) {
            return;
        }

        // Check if the extension supports auto redirect handling
        $context = $event->getContext();

        [$component, $view] = explode('.', $context);

        // If we have categories, we have to look at the extension
        if ($component === 'com_categories' && $view === 'category') {
            $data = $event->getData();

            $component = ArrayHelper::getValue($data, 'extension', '');
        }

        if (empty($component)) {
            return;
        }

        $app = $this->getApplication();

        $extension = $app->bootComponent($component);

        if (!$extension || !$extension instanceof HelperRedirectAwareInterface) {
            return;
        }

        $componentParams = ComponentHelper::getParams($component);

        if (
            (!$componentParams->get('redirect_on_save_admin', 1) || !$app->isClient('administrator'))
            && (!$componentParams->get('redirect_on_save_site', 1) || ! $app->isClient('site'))
        ) {
            return;
        }

        $table = $event->getItem();

        if (!$table instanceof Table) {
            return;
        }

        // We need to load the old data to get the old alias etc.
        $tempTable = clone $table;
        $tempTable->load();

        $link = $extension->getLinkForRedirect($tempTable);

        if (empty($link)) {
            return;
        }

        $lang = '*';

        if ($table->hasField('language')) {
            $lang = $table->{$table->getColumnAlias('language')};
        }

        $router = $this->getRouter($lang);

        $this->oldLink = $router->build($link);
    }

    /**
     * After the save, so no changes will be saved.
     * Method is called right after the content is saved
     *
     * @param   AfterSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function onContentAfterSave(AfterSaveEvent $event): void
    {
        // No need to redirect if the item is new, so early return
        $isNew = $event->getIsNew();

        if ($isNew) {
            return;
        }

        // Check if the extension supports auto redirect handling
        $context = $event->getContext();

        [$component, $view] = explode('.', $context);

        if ($component === 'com_categories' && $view === 'category') {
            $data = $event->getData();

            [$component] = explode('.', ArrayHelper::getValue($data, 'extension', ''));
        }

        $app = $this->getApplication();

        $extension = $this->getApplication()->bootComponent($component);

        if (!$extension || !$extension instanceof HelperRedirectAwareInterface) {
            return;
        }

        // Check if the redirect on save is enabled
        $componentParams = ComponentHelper::getParams($component);

        if (
            (!$componentParams->get('redirect_on_save_admin', 1) || !$app->isClient('administrator'))
            && (!$componentParams->get('redirect_on_save_site', 1) || ! $app->isClient('site'))
        ) {
            return;
        }

        $table = $event->getItem();

        if (!$table instanceof Table) {
            return;
        }

        // Load translations
        $this->loadLanguage();

        $link = $extension->getLinkForRedirect($table);

        if (empty($link) || empty($this->oldLink)) {
            return;
        }

        $router = $this->getRouter();

        $newLink = $router->build($link);

        if ((string) $this->oldLink !== (string) $newLink) {
            $user = $this->getCurrentUser();

            // In admin we have to create the redirect manually if seletected
            if ($app->isClient('administrator') && (int) $componentParams->get('redirect_on_save_admin', 1) === 2) {
                // Check for com_redirect permissions
                $canCreateRedirect = $user->authorise('core.create', 'com_redirect');

                $langString = Text::sprintf('PLG_SYSTEM_REDIRECT_AFTER_SAVE_LINK_CHANGED_NO_PERMISSION');

                if ($canCreateRedirect) {
                    $button = 'index.php?option=com_redirect&task=link.add&layout=modal&tmpl=component&old_url=' . base64_encode((string) $this->oldLink) . '&new_url=' . base64_encode((string) $link);

                    $langString = Text::sprintf('PLG_SYSTEM_REDIRECT_AFTER_SAVE_LINK_CHANGED', HTMLHelper::_('link', $button, Text::_('PLG_SYSTEM_REDIRECT_AFTER_SAVE_LINK_CHANGED_CREATE_REDIRECT'), ['class' => 'btn btn-success btn-sm', 'data-joomla-dialog' => '', 'data-close-on-message' => 'true']));
                }

                $this->getApplication()->enqueueMessage($langString, 'info');

                return;
            } elseif (
                ($app->isClient('administrator') && (int) $componentParams->get('redirect_on_save_admin', 1))
                || ($app->isClient('site') && (int) $componentParams->get('redirect_on_save_site', 1))
            ) {
                $redirectExtension = $app->bootComponent('com_redirect')->getMVCFactory();

                $redirectModel = $redirectExtension->createModel('Link', 'Administrator', ['ignore_request' => true]);

                $redirectTable = $redirectModel->getTable();

                // If it already exists, just publish it
                if ($redirectTable->load(['old_url' => (string) $this->oldLink, 'new_url' => (string) $newLink])) {
                    $redirectTable->published = 1;

                    $redirectTable->store();

                    return;
                }

                // Create new redirect
                $data = [
                    'old_url'      => (string) $this->oldLink,
                    'new_url'      => (string) $link,
                    'header'       => 301,
                    'published'    => 1,
                    'comment'      => Text::sprintf('PLG_SYSTEM_REDIRECT_AUTOMATICALLY_CREATED_ON_BY', Factory::getDate()->format(Text::_('DATE_FORMAT_LC2')), $user->name, $user->id),
                    'referer'      => '',
                    'created_date' => Factory::getDate()->toSql(),
                ];

                try {
                    $redirectModel->save($data);
                } catch (\Throwable $th) {
                    // Do nothing
                }
            }
        }
    }

    /**
     * Extract old/new url and store it in the session
     *
     * @param AfterInitialiseDocumentEvent $event
     *
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onAfterInitialiseDocument(AfterInitialiseDocumentEvent $event): void
    {
        if (!$this->getApplication()->isClient('administrator')) {
            return;
        }

        $input = $this->getApplication()->getInput();

        $option = $input->getCmd('option', '');
        $task   = $input->getCmd('task', '');

        if ($option === 'com_redirect' && $task === 'link.add') {
            $oldUrl = $input->getBase64('old_url');
            $newUrl = $input->getBase64('new_url');

            $oldUrlDecoded = base64_decode($oldUrl, true);
            $newUrlDecoded = base64_decode($newUrl, true);

            $data = array_filter([
                'old_url' => $oldUrl ? $oldUrlDecoded : '',
                'new_url' => $newUrl ? $newUrlDecoded : '',
            ]);

            $this->getApplication()->setUserState('plg_system_redirect.create.link', $data);
        }
    }

    /**
     * Set new/old link when redirect is created
     *
     * @param PrepareDataEvent $event
     *
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onContentPrepareData(PrepareDataEvent $event)
    {
        $context = $event->getContext();

        if ($context !== 'com_redirect.link' || !$this->getApplication()->isClient('administrator')) {
            return;
        }

        $data = $event->getData();

        if (!\is_array($data)) {
            $data = (array) $data;
        }

        $id = ArrayHelper::getValue($data, 'id', 0, 'int');

        // We need to create it
        if (!empty($id)) {
            return;
        }

        $storedData = $this->getApplication()->getUserState('plg_system_redirect.create.link', []);

        $this->getApplication()->setUserState('plg_system_redirect.create.link', []);

        $data = array_merge($data, $storedData);

        $event->setArgument('data', $data);
    }

    /**
     * Adds additional fields to the com_config editing form for components supporting the redirect interface
     *
     * @param   PrepareFormEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION_
     *
     * @throws  \Exception
     */
    public function onContentPrepareForm(PrepareFormEvent $event): void
    {
        $form     = $event->getForm();
        $data     = $event->getData();
        $formName = $form->getName();

        $app = $this->getApplication();

        if (!$app->isClient('administrator') || $formName !== 'com_config.component') {
            return;
        }

        $input     = $app->getInput();
        $component = $input->getCmd('component', '');

        if (empty($component)) {
            return;
        }

        $extension = $app->bootComponent($component);

        if (!$extension instanceof HelperRedirectAwareInterface) {
            return;
        }

        $this->loadLanguage();

        $form->loadFile(__DIR__ . '/../../form/config.xml');
    }

    /**
     * Internal processor for all error handlers
     *
     * @param   ErrorEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.5
     */
    public function handleError(ErrorEvent $event)
    {
        /** @var \Joomla\CMS\Application\CMSApplication $app */
        $app = $event->getApplication();

        if ($app->isClient('administrator') || ((int) $event->getError()->getCode() !== 404)) {
            return;
        }

        // Load translations
        $this->loadLanguage();

        $uri = Uri::getInstance();

        // These are the original URLs
        $orgurl                = rawurldecode($uri->toString(['scheme', 'host', 'port', 'path', 'query', 'fragment']));
        $orgurlRel             = rawurldecode($uri->toString(['path', 'query', 'fragment']));

        // The above doesn't work for sub directories, so do this
        $orgurlRootRel         = str_replace(Uri::root(), '', $orgurl);

        // For when users have added / to the url
        $orgurlRootRelSlash    = str_replace(Uri::root(), '/', $orgurl);
        $orgurlWithoutQuery    = rawurldecode($uri->toString(['scheme', 'host', 'port', 'path', 'fragment']));
        $orgurlRelWithoutQuery = rawurldecode($uri->toString(['path', 'fragment']));

        // These are the URLs we save and use
        $url                = StringHelper::strtolower(rawurldecode($uri->toString(['scheme', 'host', 'port', 'path', 'query', 'fragment'])));
        $urlRel             = StringHelper::strtolower(rawurldecode($uri->toString(['path', 'query', 'fragment'])));

        // The above doesn't work for sub directories, so do this
        $urlRootRel         = str_replace(Uri::root(), '', $url);

        // For when users have added / to the url
        $urlRootRelSlash    = str_replace(Uri::root(), '/', $url);
        $urlWithoutQuery    = StringHelper::strtolower(rawurldecode($uri->toString(['scheme', 'host', 'port', 'path', 'fragment'])));
        $urlRelWithoutQuery = StringHelper::strtolower(rawurldecode($uri->toString(['path', 'fragment'])));

        $excludes = (array) $this->params->get('exclude_urls');

        $skipUrl = false;

        foreach ($excludes as $exclude) {
            if (empty($exclude->term)) {
                continue;
            }

            if (!empty($exclude->regexp)) {
                // Only check $url, because it includes all other sub urls
                if (preg_match('/' . $exclude->term . '/i', $orgurlRel)) {
                    $skipUrl = true;
                    break;
                }
            } else {
                if (StringHelper::strpos($orgurlRel, $exclude->term) !== false) {
                    $skipUrl = true;
                    break;
                }
            }
        }

        /**
         * Why is this (still) here?
         * Because hackers still try urls with mosConfig_* and Url Injection with =http[s]:// and we dont want to log/redirect these requests
         */
        if ($skipUrl || (str_contains($url, 'mosConfig_')) || (str_contains($url, '=http'))) {
            return;
        }

        $query = $this->getDatabase()->createQuery();

        $query->select('*')
            ->from($this->getDatabase()->quoteName('#__redirect_links'))
            ->whereIn(
                $this->getDatabase()->quoteName('old_url'),
                [
                    $url,
                    $urlRel,
                    $urlRootRel,
                    $urlRootRelSlash,
                    $urlWithoutQuery,
                    $urlRelWithoutQuery,
                    $orgurl,
                    $orgurlRel,
                    $orgurlRootRel,
                    $orgurlRootRelSlash,
                    $orgurlWithoutQuery,
                    $orgurlRelWithoutQuery,
                ],
                ParameterType::STRING
            );

        $this->getDatabase()->setQuery($query);

        $redirect = null;

        try {
            $redirects = $this->getDatabase()->loadAssocList();
        } catch (\Exception $e) {
            $event->setError(new \Exception($this->getApplication()->getLanguage()->_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));

            return;
        }

        $possibleMatches = array_unique(
            [
                $url,
                $urlRel,
                $urlRootRel,
                $urlRootRelSlash,
                $urlWithoutQuery,
                $urlRelWithoutQuery,
                $orgurl,
                $orgurlRel,
                $orgurlRootRel,
                $orgurlRootRelSlash,
                $orgurlWithoutQuery,
                $orgurlRelWithoutQuery,
            ]
        );

        foreach ($possibleMatches as $match) {
            if (($index = array_search($match, array_column($redirects, 'old_url'))) !== false) {
                $redirect = (object) $redirects[$index];

                if ((int) $redirect->published === 1) {
                    break;
                }
            }
        }

        // A redirect object was found and, if published, will be used
        if ($redirect !== null && ((int) $redirect->published === 1)) {
            if (!$redirect->header || (bool) ComponentHelper::getParams('com_redirect')->get('mode', false) === false) {
                $redirect->header = 301;
            }

            if ($redirect->header < 400 && $redirect->header >= 300) {
                $urlQuery = $uri->getQuery();

                $oldUrlParts = parse_url($redirect->old_url);

                $newUrl = $redirect->new_url;

                if ($urlQuery !== '' && empty($oldUrlParts['query'])) {
                    $newUrl .= '?' . $urlQuery;
                }

                $dest = Uri::isInternal($newUrl) || !str_contains($newUrl, 'http') ?
                    Route::_($newUrl) : $newUrl;

                // In case the url contains double // lets remove it
                $destination = str_replace(Uri::root() . '/', Uri::root(), $dest);

                // Always count redirect hits
                $redirect->hits++;

                try {
                    $this->getDatabase()->updateObject('#__redirect_links', $redirect, 'id');
                } catch (\Exception) {
                    // We don't log issues for now
                }

                $app->redirect($destination, (int) $redirect->header);
            }

            $event->setError(new \RuntimeException($event->getError()->getMessage(), $redirect->header, $event->getError()));
        } elseif ($redirect === null) {
            // No redirect object was found so we create an entry in the redirect table
            if ((bool) $this->params->get('collect_urls', 1)) {
                if (!$this->params->get('includeUrl', 1)) {
                    $url = $urlRel;
                }

                $nowDate = Factory::getDate()->toSql();

                $data = (object) [
                    'id'            => 0,
                    'old_url'       => $url,
                    'referer'       => $app->getInput()->server->getString('HTTP_REFERER', ''),
                    'hits'          => 1,
                    'published'     => 0,
                    'created_date'  => $nowDate,
                    'modified_date' => $nowDate,
                ];

                try {
                    $this->getDatabase()->insertObject('#__redirect_links', $data, 'id');
                } catch (\Exception $e) {
                    $event->setError(new \Exception($this->getApplication()->getLanguage()->_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));

                    return;
                }
            }
        } else {
            // We have an unpublished redirect object, increment the hit counter
            $redirect->hits++;

            try {
                $this->getDatabase()->updateObject('#__redirect_links', $redirect, ['id']);
            } catch (\Exception $e) {
                $event->setError(new \Exception($this->getApplication()->getLanguage()->_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));

                return;
            }
        }
    }

    /**
     * Get the router prepared by ourself
     *
     * @return  SiteRouter
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getRouter($language = '*'): ?SiteRouter
    {
        if (Multilanguage::isEnabled() && $language !== '*') {
            $lang = $this->languageFactory->createLanguage($language);
        }

        if (!isset($lang)) {
            $lang = $this->siteApp->getLanguage();
        }

        // We need to build our own menu in guest context
        $options = [
            'app'      => $this->siteApp,
            'db'       => $this->getDatabase(),
            'language' => $lang,
            'user'     => new User(),
        ];

        $menu = $this->menuFactory->createMenu('site', $options);

        $router = clone Factory::getContainer()->get(SiteRouter::class);

        $router->setMenu($menu);

        return $router;
    }

    /**
     * Sets the internal site application.
     *
     * @param  SiteApplication  $siteApp  The site application
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setSiteApplication(SiteApplication $siteApp): void
    {
        $this->siteApp = $siteApp;
    }

    /**
     * Sets the internal menu factory.
     *
     * @param  MenuFactoryInterface  $menuFactory  The menu factory
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setMenuFactory(MenuFactoryInterface $menuFactory): void
    {
        $this->menuFactory = $menuFactory;
    }

    /**
     * Sets the internal language factory.
     *
     * @param  LanguageFactoryInterface  $languageFactory  The language factory
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setLanguageFactory(LanguageFactoryInterface $languageFactory): void
    {
        $this->languageFactory = $languageFactory;
    }
}
