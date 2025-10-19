<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.languagefilter
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\LanguageFilter\Extension;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Application\AfterDispatchEvent;
use Joomla\CMS\Event\Application\AfterInitialiseEvent;
use Joomla\CMS\Event\Application\AfterRouteEvent;
use Joomla\CMS\Event\Application\BeforeExecuteEvent;
use Joomla\CMS\Event\Privacy\CollectCapabilitiesEvent;
use Joomla\CMS\Event\User\AfterSaveEvent;
use Joomla\CMS\Event\User\BeforeSaveEvent;
use Joomla\CMS\Event\User\LoginEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Router\SiteRouterAwareTrait;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Language Filter Plugin.
 *
 * @since  1.6
 */
final class LanguageFilter extends CMSPlugin implements SubscriberInterface
{
    use SiteRouterAwareTrait;

    /**
     * The routing mode.
     *
     * @var    boolean
     * @since  2.5
     */
    protected $mode_sef;

    /**
     * Available languages by sef.
     *
     * @var    array
     * @since  1.6
     */
    protected $sefs;

    /**
     * Available languages by language codes.
     *
     * @var    array
     * @since  2.5
     */
    protected $lang_codes;

    /**
     * The current language code.
     *
     * @var    string
     * @since  3.4.2
     */
    protected $current_lang;

    /**
     * The default language code.
     *
     * @var    string
     * @since  2.5
     */
    protected $default_lang;

    /**
     * The logged user language code.
     *
     * @var    string
     * @since  3.3.1
     */
    private $user_lang_code;

    /**
     * The language factory
     *
     * @var   LanguageFactoryInterface
     *
     * @since 4.4.0
     */
    private $languageFactory;

    /**
     * Constructor.
     *
     * @param   array                     $config           An optional associative array of configuration settings
     * @param   CMSApplicationInterface   $app              The language factory
     * @param   LanguageFactoryInterface  $languageFactory  The language factory
     *
     * @since   1.6.0
     */
    public function __construct(
        array $config,
        CMSApplicationInterface $app,
        LanguageFactoryInterface $languageFactory
    ) {
        parent::__construct($config);

        $this->languageFactory = $languageFactory;

        $this->setApplication($app);

        $app = $this->getApplication();

        // Setup language data.
        $this->mode_sef     = $app->get('sef', 0);
        $this->sefs         = LanguageHelper::getLanguages('sef');
        $this->lang_codes   = LanguageHelper::getLanguages('lang_code');
        $this->default_lang = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');

        // If language filter plugin is executed in a site page.
        if ($app->isClient('site')) {
            $levels = $app->getIdentity()->getAuthorisedViewLevels();

            foreach ($this->sefs as $sef => $language) {
                // @todo: In Joomla 2.5.4 and earlier access wasn't set. Non modified Content Languages got 0 as access value
                // we also check if frontend language exists and is enabled
                if (
                    ($language->access && !\in_array($language->access, $levels))
                    || (!\array_key_exists($language->lang_code, LanguageHelper::getInstalledLanguages(0)))
                ) {
                    unset($this->lang_codes[$language->lang_code], $this->sefs[$language->sef]);
                }
            }
        } else {
            // If language filter plugin is executed in an admin page (ex: Route site).
            // Set current language to default site language, fallback to en-GB if there is no content language for the default site language.
            $this->current_lang = isset($this->lang_codes[$this->default_lang]) ? $this->default_lang : 'en-GB';

            foreach ($this->sefs as $sef => $language) {
                if (!\array_key_exists($language->lang_code, LanguageHelper::getInstalledLanguages(0))) {
                    unset($this->lang_codes[$language->lang_code], $this->sefs[$language->sef]);
                }
            }
        }

        if (!\count($this->sefs)) {
            $this->loadLanguage();
            $app->enqueueMessage(Text::_('PLG_SYSTEM_LANGUAGEFILTER_ERROR_NO_CONTENT_LANGUAGE'), 'error');
        }
    }

    /**
     * Returns an array of CMS events this plugin will listen to and the respective handlers.
     *
     * @return  array
     *
     * @since  5.1.0
     */
    public static function getSubscribedEvents(): array
    {
        /**
         * Note that onAfterInitialise must be the first handlers to run for this
         * plugin to operate as expected. These handlers load compatibility code which
         * might be needed by other plugins
         */
        return [
            'onBeforeExecute'                   => 'onBeforeExecute',
            'onAfterInitialise'                 => 'onAfterInitialise',
            'onAfterDispatch'                   => 'onAfterDispatch',
            'onAfterRoute'                      => 'onAfterRoute',
            'onPrivacyCollectAdminCapabilities' => 'onPrivacyCollectAdminCapabilities',
            'onUserAfterSave'                   => 'onUserAfterSave',
            'onUserBeforeSave'                  => 'onUserBeforeSave',
            'onUserLogin'                       => 'onUserLogin',
        ];
    }

    /**
     * Listener for the onBeforeExecute event
     *
     * @param   BeforeExecuteEvent  $event  The Event object
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function onBeforeExecute(BeforeExecuteEvent $event): void
    {
        $app = $event->getApplication();

        if (!$app->isClient('site')) {
            return;
        }

        // If a language was specified it has priority, otherwise use user or default language settings
        $app->setLanguageFilter(true);
        $app->setDetectBrowser($this->params->get('detect_browser', '1') == '1');
    }

    /**
     * After initialise.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onAfterInitialise(AfterInitialiseEvent $event): void
    {
        $router = $this->getSiteRouter();

        // Attach build rules for language SEF.
        $router->attachBuildRule([$this, 'preprocessBuildRule'], Router::PROCESS_BEFORE);

        if ($this->mode_sef) {
            $router->attachBuildRule([$this, 'buildRule'], Router::PROCESS_BEFORE);
            $router->attachBuildRule([$this, 'postprocessSEFBuildRule'], Router::PROCESS_AFTER);
        } else {
            $router->attachBuildRule([$this, 'postprocessNonSEFBuildRule'], Router::PROCESS_AFTER);
        }

        // Attach parse rule.
        $router->attachParseRule([$this, 'parseRule'], Router::PROCESS_BEFORE);
        $router->attachParseRule([$this, 'setLanguageApplicationState'], Router::PROCESS_BEFORE);
    }

    /**
     * After route.
     *
     * @return  void
     *
     * @since   3.4
     */
    public function onAfterRoute(AfterRouteEvent $event): void
    {
        // Add custom site name.
        if ($this->getApplication()->isClient('site') && isset($this->lang_codes[$this->current_lang]) && $this->lang_codes[$this->current_lang]->sitename) {
            $this->getApplication()->set('sitename', $this->lang_codes[$this->current_lang]->sitename);
        }
    }

    /**
     * Add build preprocess rule to router.
     *
     * @param   Router  &$router  Router object.
     * @param   Uri     &$uri     Uri object.
     *
     * @return  void
     *
     * @since   3.4
     */
    public function preprocessBuildRule(&$router, &$uri)
    {
        $lang = $uri->getVar('lang', $this->current_lang);

        if (isset($this->sefs[$lang])) {
            $lang = $this->sefs[$lang]->lang_code;
        }

        $uri->setVar('lang', $lang);
    }

    /**
     * Add build rule to router.
     *
     * @param   Router  &$router  Router object.
     * @param   Uri     &$uri     Uri object.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function buildRule(&$router, &$uri)
    {
        $lang = $uri->getVar('lang');

        if (isset($this->lang_codes[$lang])) {
            $sef = $this->lang_codes[$lang]->sef;
        } else {
            $sef = $this->lang_codes[$this->current_lang]->sef;
        }

        if (
            !$this->params->get('remove_default_prefix', 0)
            || $lang !== $this->default_lang
        ) {
            $uri->setPath($uri->getPath() . '/' . $sef . '/');
        }
    }

    /**
     * postprocess build rule for SEF URLs
     *
     * @param   Router  &$router  Router object.
     * @param   Uri     &$uri     Uri object.
     *
     * @return  void
     *
     * @since   3.4
     */
    public function postprocessSEFBuildRule(&$router, &$uri)
    {
        $uri->delVar('lang');
    }

    /**
     * postprocess build rule for non-SEF URLs
     *
     * @param   Router  &$router  Router object.
     * @param   Uri     &$uri     Uri object.
     *
     * @return  void
     *
     * @since   3.4
     */
    public function postprocessNonSEFBuildRule(&$router, &$uri)
    {
        $lang = $uri->getVar('lang');

        if (isset($this->lang_codes[$lang])) {
            $uri->setVar('lang', $this->lang_codes[$lang]->sef);
        }
    }

    /**
     * Add parse rule to router.
     *
     * @param   Router  &$router  Router object.
     * @param   Uri     &$uri     Uri object.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function parseRule(&$router, &$uri)
    {
        // Are we in SEF mode or not?
        if ($this->mode_sef) {
            $path  = $uri->getPath();
            $parts = explode('/', $path);
            $sef   = StringHelper::strtolower($parts[0]);
            $lang  = $uri->getVar('lang');

            if (isset($this->sefs[$sef])) {
                // We found a matching language to the lang code
                $uri->setVar('lang', $this->sefs[$sef]->lang_code);
                array_shift($parts);
                $uri->setPath(implode('/', $parts));

                // We were called with the default language code and want to redirect
                if ($this->params->get('remove_default_prefix', 0) && $uri->getVar('lang') == $this->default_lang) {
                    $router->setTainted();
                }
            } elseif ($this->params->get('remove_default_prefix', 0)) {
                // We don't have a prefix for the default language
                $uri->setVar('lang', $this->default_lang);
            } elseif (!isset($this->sefs[$lang])) {
                // No language is set, so we want to redirect to the right language
                $router->setTainted();
            }

            // The language was set both per SEF path and per query parameter. Query parameter takes precedence
            if ($lang && isset($this->sefs[$sef])) {
                $uri->setVar('lang', $lang);
                $router->setTainted();
            }
        } elseif ($uri->hasVar('lang')) {
            // We are not in SEF mode. Do we have a language set?
            $lang_code = $uri->getVar('lang');

            if (isset($this->sefs[$lang_code])) {
                // We found a matching language to the lang code
                $uri->setVar('lang', $this->sefs[$lang_code]->lang_code);
            } else {
                // The language is not installed on our site
                $uri->delVar('lang');
            }
        }
    }

    /**
     * Parse rule to set the applications language state.
     * This rule is removed after being executed the first time, since
     * it does redirects and thus disallows parsing more than one URL per page call
     *
     * @param   Router  &$router  Router object.
     * @param   Uri     &$uri     Uri object.
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setLanguageApplicationState(&$router, &$uri)
    {
        // We check if the parseRule is still attached to keep this b/c
        if (!\in_array([$this, 'parseRule'], $router->getRules()['parsepreprocess'])) {
            $router->detachRule('parse', [$this, 'setLanguageApplicationState'], $router::PROCESS_BEFORE);

            return;
        }

        $lang_code = false;

        // Our parse rule discovered a language
        if ($uri->hasVar('lang')) {
            $uri_lang_code = $uri->getVar('lang');

            // Check whether the tag exists, first check for full language tag, then for short tag
            if (isset($this->lang_codes[$uri_lang_code])) {
                $lang_code = $uri_lang_code;
            } elseif (isset($this->sefs[$uri_lang_code])) {
                // Check for short language tag
                $lang_code = $this->sefs[$uri_lang_code]->lang_code;
            }
        }

        if (!$lang_code) {
            /**
             * We don't know the language yet and want to discover it.
             * If we remove the default prefix, call by POST or have nolangfilter set,
             * we simply take the default language.
             */
            if (
                $this->params->get('remove_default_prefix', 0)
                || $this->getApplication()->getInput()->getMethod() === 'POST'
                || $this->getApplication()->getInput()->get('nolangfilter', 0) == 1
                || \count($this->getApplication()->getInput()->post) > 0
                || \count($this->getApplication()->getInput()->files) > 0
            ) {
                $lang_code = $this->default_lang;
            } else {
                $lang_code = $this->getLanguageCookie();

                // No language code. Try using browser settings or default site language
                if (!$lang_code && $this->params->get('detect_browser', 0) == 1) {
                    $lang_code = LanguageHelper::detectLanguage();
                }

                if (!$lang_code) {
                    $lang_code = $this->default_lang;
                }

                if (!$this->params->get('remove_default_prefix', 0) && $uri->getPath() == '') {
                    if ($this->mode_sef) {
                        $path = $this->lang_codes[$lang_code]->sef . '/' . $uri->getPath();

                        if (!$this->getApplication()->get('sef_rewrite')) {
                            $path = 'index.php/' . $path;
                        }

                        $uri->setPath($path);
                    } else {
                        $uri->setPath('index.php');
                        $uri->setVar('lang', $this->lang_codes[$lang_code]->sef);
                    }
                    $redirectHttpCode = 301;
                    $redirectUri      = $uri->base() . $uri->toString(['path', 'query', 'fragment']);

                    // We cannot cache this redirect in browser. 301 is cacheable by default so we need to force to not cache it in browsers.
                    $this->getApplication()->setHeader('Expires', 'Wed, 17 Aug 2005 00:00:00 GMT', true);
                    $this->getApplication()->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT', true);
                    $this->getApplication()->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate', false);
                    $this->getApplication()->sendHeaders();

                    // Redirect to language.
                    $this->getApplication()->redirect($redirectUri, $redirectHttpCode);
                }
            }
        }

        // We have found our language and now need to set the cookie and the language value in our system
        $this->current_lang = $lang_code;

        // Set the request var.
        $app = $this->getApplication();
        $app->getInput()->set('language', $lang_code);
        $app->set('language', $lang_code);
        $language = $app->getLanguage();

        if ($language->getTag() !== $lang_code) {
            $language_new = $this->languageFactory->createLanguage($lang_code, (bool) $app->get('debug_lang'));

            foreach ($language->getPaths() as $extension => $files) {
                if (str_starts_with($extension, 'plg_system')) {
                    $extension_name = substr($extension, 11);

                    $language_new->load($extension, JPATH_ADMINISTRATOR)
                    || $language_new->load($extension, JPATH_PLUGINS . '/system/' . $extension_name);

                    continue;
                }

                $language_new->load($extension);
            }

            Factory::$language = $language_new;
            $app->loadLanguage($language_new);
        }

        // Create a cookie.
        $this->setLanguageCookie($lang_code);

        $router->detachRule('parse', [$this, 'setLanguageApplicationState'], $router::PROCESS_BEFORE);
    }

    /**
     * Reports the privacy related capabilities for this plugin to site administrators.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onPrivacyCollectAdminCapabilities(CollectCapabilitiesEvent $event): void
    {
        $this->loadLanguage();

        $language = $this->getApplication()->getLanguage();

        $event->addResult([
            $language->_('PLG_SYSTEM_LANGUAGEFILTER') => [
                $language->_(
                    'PLG_SYSTEM_LANGUAGEFILTER_PRIVACY_CAPABILITY_LANGUAGE_COOKIE'
                ),
            ],
        ]);
    }

    /**
     * Before store user method.
     *
     * Method is called before user data is stored in the database.
     *
     * @param   array    $user   Holds the old user data.
     * @param   boolean  $isnew  True if a new user is stored.
     * @param   array    $new    Holds the new user data.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onUserBeforeSave(BeforeSaveEvent $event): void
    {
        $user = $event->getUser();

        if (\array_key_exists('params', $user) && $this->params->get('automatic_change', 1) == 1) {
            $registry             = new Registry($user['params']);
            $this->user_lang_code = $registry->get('language');

            if (empty($this->user_lang_code)) {
                $this->user_lang_code = $this->current_lang;
            }
        }
    }

    /**
     * After store user method.
     *
     * Method is called after user data is stored in the database.
     *
     * @param   array    $user     Holds the new user data.
     * @param   boolean  $isnew    True if a new user is stored.
     * @param   boolean  $success  True if user was successfully stored in the database.
     * @param   string   $msg      Message.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onUserAfterSave(AfterSaveEvent $event): void
    {
        $user    = $event->getUser();
        $success = $event->getSavingResult();

        if ($success && \array_key_exists('params', $user) && $this->params->get('automatic_change', 1) == 1) {
            $registry  = new Registry($user['params']);
            $lang_code = $registry->get('language');

            if (empty($lang_code)) {
                $lang_code = $this->current_lang;
            }

            $app = $this->getApplication();

            if ($lang_code === $this->user_lang_code || !isset($this->lang_codes[$lang_code])) {
                if ($app->isClient('site')) {
                    $app->setUserState('com_users.edit.profile.redirect', null);
                }
            } else {
                if ($app->isClient('site')) {
                    $app->setUserState('com_users.edit.profile.redirect', 'index.php?Itemid='
                        . $app->getMenu()->getDefault($lang_code)->id . '&lang=' . $this->lang_codes[$lang_code]->sef);

                    // Create a cookie.
                    $this->setLanguageCookie($lang_code);
                }
            }
        }
    }

    /**
     * Method to handle any login logic and report back to the subject.
     *
     * @param   array  $user     Holds the user data.
     * @param   array  $options  Array holding options (remember, autoregister, group).
     *
     * @return  void
     *
     * @since   1.5
     */
    public function onUserLogin(LoginEvent $event): void
    {
        $user = $event->getArgument('subject');

        $app = $this->getApplication();

        if ($app->isClient('site')) {
            $menu = $app->getMenu();

            if ($this->params->get('automatic_change', 1)) {
                $assoc     = Associations::isEnabled();
                $lang_code = $user['language'];

                // If no language is specified for this user, we set it to the site default language
                if (empty($lang_code)) {
                    $lang_code = $this->default_lang;
                }

                // The language has been deleted/disabled or the related content language does not exist/has been unpublished
                // or the related home page does not exist/has been unpublished
                if (
                    !\array_key_exists($lang_code, $this->lang_codes)
                    || !\array_key_exists($lang_code, Multilanguage::getSiteHomePages())
                    || !is_dir(JPATH_SITE . '/language/' . $lang_code)
                ) {
                    $lang_code = $this->current_lang;
                }

                // Try to get association from the current active menu item
                $active = $menu->getActive();

                $foundAssociation = false;

                /**
                 * Looking for associations.
                 * If the login menu item form contains an internal URL redirection,
                 * This will override the automatic change to the user preferred site language.
                 * In that case we use the redirect as defined in the menu item.
                 *  Otherwise we redirect, when available, to the user preferred site language.
                 */
                if ($active && !$active->getParams()->get('login_redirect_url')) {
                    if ($assoc) {
                        $associations = MenusHelper::getAssociations($active->id);
                    }

                    // Retrieves the Itemid from a login form.
                    $uri = new Uri($app->getUserState('users.login.form.return'));

                    if ($uri->getVar('Itemid')) {
                        // The login form contains a menu item redirection. Try to get associations from that menu item.
                        // If any association set to the user preferred site language, redirect to that page.
                        if ($assoc) {
                            $associations = MenusHelper::getAssociations($uri->getVar('Itemid'));
                        }

                        if (isset($associations[$lang_code]) && $menu->getItem($associations[$lang_code])) {
                            $associationItemid = $associations[$lang_code];
                            $app->setUserState('users.login.form.return', 'index.php?Itemid=' . $associationItemid);
                            $foundAssociation = true;
                        }
                    } elseif ($this->mode_sef) {
                        if ($app->getUserState('users.login.form.return')) {
                            $app->setUserState(
                                'users.login.form.return',
                                Route::_(
                                    $app->getUserState('users.login.form.return'),
                                    false
                                )
                            );
                            $foundAssociation = true;
                        }
                    } elseif (isset($associations[$lang_code]) && $menu->getItem($associations[$lang_code])) {
                        /**
                         * The login form does not contain a menu item redirection.
                         * The active menu item has associations.
                         * We redirect to the user preferred site language associated page.
                         */
                        $associationItemid = $associations[$lang_code];
                        $app->setUserState('users.login.form.return', 'index.php?Itemid=' . $associationItemid);
                        $foundAssociation = true;
                    } elseif ($active->home) {
                        // We are on a Home page, we redirect to the user preferred site language Home page.
                        $item = $menu->getDefault($lang_code);

                        if ($item && $item->language !== $active->language && $item->language !== '*') {
                            $app->setUserState('users.login.form.return', 'index.php?Itemid=' . $item->id);
                            $foundAssociation = true;
                        }
                    }
                }

                if ($foundAssociation && $lang_code !== $this->current_lang) {
                    // Change language.
                    $this->current_lang = $lang_code;

                    // Create a cookie.
                    $this->setLanguageCookie($lang_code);

                    // Change the language code.
                    $this->languageFactory->createLanguage($lang_code);
                }
            } else {
                if ($app->getUserState('users.login.form.return')) {
                    $app->setUserState(
                        'users.login.form.return',
                        Route::_(
                            $app->getUserState('users.login.form.return'),
                            false
                        )
                    );
                }
            }
        }
    }

    /**
     * Method to add alternative meta tags for associated menu items.
     *
     * @return  void
     *
     * @since   1.7
     */
    public function onAfterDispatch(AfterDispatchEvent $event): void
    {
        $app = $this->getApplication();
        $doc = $app->getDocument();

        if ($app->isClient('site') && $this->params->get('alternate_meta', 1) && $doc->getType() === 'html') {
            $languages             = $this->lang_codes;
            $homes                 = Multilanguage::getSiteHomePages();
            $menu                  = $app->getMenu();
            $active                = $menu->getActive();
            $levels                = $app->getIdentity()->getAuthorisedViewLevels();
            $remove_default_prefix = $this->params->get('remove_default_prefix', 0);
            $server                = Uri::getInstance()->toString(['scheme', 'host', 'port']);
            $is_home               = false;

            // Router can be injected when turned into a DI built plugin
            $currentInternalUrl    = 'index.php?' . http_build_query($this->getSiteRouter()->getVars());

            if ($active) {
                $active_link  = Route::_($active->link . '&Itemid=' . $active->id);
                $current_link = Route::_($currentInternalUrl);

                // Load menu associations
                if ($active_link === $current_link) {
                    $associations = MenusHelper::getAssociations($active->id);
                }

                // Check if we are on the home page
                $is_home = ($active->home
                    && ($active_link === $current_link || $active_link === $current_link . 'index.php' || $active_link . '/' === $current_link));
            }

            // Load component associations.
            $option = $app->getInput()->get('option');

            $component = $app->bootComponent($option);

            if ($component instanceof AssociationServiceInterface) {
                $cassociations = $component->getAssociationsExtension()->getAssociationsForItem();
            } else {
                $cName = ucfirst(substr($option, 4)) . 'HelperAssociation';
                \JLoader::register($cName, Path::clean(JPATH_SITE . '/components/' . $option . '/helpers/association.php'));

                if (class_exists($cName) && \is_callable([$cName, 'getAssociations'])) {
                    $cassociations = \call_user_func([$cName, 'getAssociations']);
                }
            }

            // For each language...
            foreach ($languages as $i => $language) {
                switch (true) {
                    case !\array_key_exists($i, LanguageHelper::getInstalledLanguages(0)):
                    case !isset($homes[$i]):
                    case isset($language->access) && $language->access && !\in_array($language->access, $levels):
                        // Language without frontend UI || Language without specific home menu || Language without authorized access level
                        unset($languages[$i]);
                        break;

                    case $is_home:
                        // Home page
                        $language->link = Route::_('index.php?lang=' . $language->sef . '&Itemid=' . $homes[$i]->id);
                        break;

                    case $i === $this->current_lang:
                        // Current language link
                        $language->link = Route::_($currentInternalUrl);
                        break;

                    case isset($cassociations[$i]):
                        // Component association
                        $language->link = Route::_($cassociations[$i]);
                        break;

                    case isset($associations[$i]) && ($item = $menu->getItem($associations[$i])):
                        // Menu items association
                        // Heads up! "$item = $menu" here below is an assignment, *NOT* comparison
                        $language->link = Route::_('index.php?Itemid=' . $item->id . '&lang=' . $language->sef);
                        break;

                    default:
                        // Too bad...
                        unset($languages[$i]);
                }
            }

            // If there are at least 2 of them, add the rel="alternate" links to the <head>
            if (\count($languages) > 1) {
                // Remove the sef from the default language if "Remove URL Language Code" is on
                if ($remove_default_prefix && isset($languages[$this->default_lang])) {
                    $languages[$this->default_lang]->link
                                    = preg_replace('|/' . $languages[$this->default_lang]->sef . '/|', '/', $languages[$this->default_lang]->link, 1);
                }

                foreach ($languages as $i => $language) {
                    $doc->addHeadLink($server . $language->link, 'alternate', 'rel', ['hreflang' => $i]);
                }

                // Add x-default language tag
                if ($this->params->get('xdefault', 1)) {
                    $xdefault_language = $this->params->get('xdefault_language', $this->default_lang);
                    $xdefault_language = ($xdefault_language === 'default') ? $this->default_lang : $xdefault_language;

                    if (isset($languages[$xdefault_language])) {
                        // Use a custom tag because addHeadLink is limited to one URI per tag
                        $doc->addCustomTag('<link href="' . $server . $languages[$xdefault_language]->link . '" rel="alternate" hreflang="x-default">');
                    }
                }
            }
        }
    }

    /**
     * Set the language cookie
     *
     * @param   string  $languageCode  The language code for which we want to set the cookie
     *
     * @return  void
     *
     * @since   3.4.2
     */
    private function setLanguageCookie($languageCode)
    {
        // If is set to use language cookie for a year in plugin params, save the user language in a new cookie.
        if ((int) $this->params->get('lang_cookie', 0) === 1) {
            // Create a cookie with one year lifetime.
            $app = $this->getApplication();
            $app->getInput()->cookie->set(
                ApplicationHelper::getHash('language'),
                $languageCode,
                [
                    'expires'  => time() + 365 * 86400,
                    'path'     => $app->get('cookie_path', '/'),
                    'domain'   => $app->get('cookie_domain', ''),
                    'secure'   => $app->isHttpsForced(),
                    'httponly' => true,
                ]
            );
        } else {
            // If not, set the user language in the session (that is already saved in a cookie).
            $this->getApplication()->getSession()->set('plg_system_languagefilter.language', $languageCode);
        }
    }

    /**
     * Get the language cookie
     *
     * @return  string
     *
     * @since   3.4.2
     */
    private function getLanguageCookie()
    {
        // Is is set to use a year language cookie in plugin params, get the user language from the cookie.
        if ((int) $this->params->get('lang_cookie', 0) === 1) {
            $languageCode = $this->getApplication()->getInput()->cookie->get(ApplicationHelper::getHash('language'));
        } else {
            // Else get the user language from the session.
            $languageCode = $this->getApplication()->getSession()->get('plg_system_languagefilter.language');
        }

        // Let's be sure we got a valid language code. Fallback to null.
        if (!\array_key_exists($languageCode, $this->lang_codes)) {
            $languageCode = null;
        }

        return $languageCode;
    }
}
