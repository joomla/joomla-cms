<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Cache\CacheControllerFactoryAwareInterface;
use Joomla\CMS\Cache\CacheControllerFactoryAwareTrait;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use stdClass;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for a Joomla Module Dispatcher.
 *
 * @since  4.0.0
 */
abstract class AbstractModuleDispatcher extends Dispatcher implements CacheControllerFactoryAwareInterface
{
    use CacheControllerFactoryAwareTrait;

    /**
     * The module instance
     *
     * @var    \stdClass
     * @since  4.0.0
     */
    protected $module;

    /**
     * Constructor for Dispatcher
     *
     * @param   \stdClass                $module  The module
     * @param   CMSApplicationInterface  $app     The application instance
     * @param   Input                    $input   The input instance
     *
     * @since   4.0.0
     */
    public function __construct(\stdClass $module, CMSApplicationInterface $app, Input $input)
    {
        parent::__construct($app, $input);

        $this->module = $module;
    }

    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function dispatch()
    {
        $this->loadLanguage();

        $displayData = $this->getLayoutData();

        // Abort when display data is false
        if ($displayData === false) {
            return;
        }

        // Execute the layout without the module context
        $loader = static function (array $displayData) {
            // If $displayData doesn't exist in extracted data, unset the variable.
            if (!\array_key_exists('displayData', $displayData)) {
                extract($displayData);
                unset($displayData);
            } else {
                extract($displayData);
            }

            /**
             * Extracted variables
             * -----------------
             * @var   \stdClass  $module
             * @var   Registry   $params
             */

            require ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));
        };

        $loader($displayData);
    }

    /**
     * Returns the layout data. This function can be overridden by subclasses to add more
     * attributes for the layout.
     *
     * If false is returned, then it means that the dispatch process should be aborted.
     *
     * @return  array|false
     *
     * @since   4.0.0
     */
    protected function getLayoutData()
    {
        return [
            'module'   => $this->module,
            'app'      => $this->app,
            'input'    => $this->input,
            'params'   => new Registry($this->module->params),
            'template' => $this->app->getTemplate(),
        ];
    }

    /**
     * Load the language.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function loadLanguage()
    {
        $language = $this->app->getLanguage();

        $coreLanguageDirectory      = JPATH_BASE;
        $extensionLanguageDirectory = JPATH_BASE . '/modules/' . $this->module->module;

        $langPaths = $language->getPaths();

        // Only load the module's language file if it hasn't been already
        if (!$langPaths || (!isset($langPaths[$coreLanguageDirectory]) && !isset($langPaths[$extensionLanguageDirectory]))) {
            // 1.5 or Core then 1.6 3PD
            $language->load($this->module->module, $coreLanguageDirectory) ||
            $language->load($this->module->module, $extensionLanguageDirectory);
        }
    }

    /**
     * Module cache helper function.
     *
     * Caching modes:
     * To be set in XML:
     * 'static'      One cache file for all pages with the same module parameters
     * 'itemid'      Changes on itemid change, to be called from inside the module:
     * 'safeuri'     Id created from $cacheParams->get('modeparams')
     * 'id'          Module sets own cache id's
     *
     * @param   Registry  $cacheParams   The cache parameters - id or URL parameters, depending on the cache mode
     * @param   Registry  $params        The parameters
     *
     * @return  mixed
     *
     * @see     InputFilter::clean()
     * @since   __DEPLOY_VERSION__
     */
    protected function loadFromCache(Registry $params, Registry $cacheParams)
    {
        $user = $this->app->getIdentity();

        /** @var CallbackController $cache */
        $cache = $this->getCacheControllerFactory()->createCacheController('callback', ['defaultgroup' => $cacheParams->get('cachegroup')]);

        // Turn cache off for internal callers if parameters are set to off and for all logged in users
        $ownCacheDisabled = $params->get('owncache') === 0 || $params->get('owncache') === '0';
        $cacheDisabled    = $params->get('cache') === 0 || $params->get('cache') === '0';

        if ($ownCacheDisabled || $cacheDisabled || $this->app->get('caching') == 0 || $user->id) {
            $cache->setCaching(false);
        }

        // Module cache is set in seconds, global cache in minutes, setLifeTime works in minutes
        $cache->setLifeTime($params->get('cache_time', $this->app->get('cachetime') * 60) / 60);

        $workAroundoptions = ['nopathway' => 1, 'nohead' => 0, 'nomodules' => 1, 'modulemode' => 1, 'mergehead' => 1];

        $workArounds  = true;
        $view_levels = md5(serialize($user->getAuthorisedViewLevels()));

        switch ($cacheParams->get('cachemode')) {
            case 'id':
                $ret = $cache->get(
                    [$cacheParams->get('class'), $cacheParams->get('method')],
                    $cacheParams->get('methodparams'),
                    $cacheParams->get('modeparams') . $cacheParams->get('cachesuffix'),
                    $workArounds,
                    $workAroundoptions
                );
                break;

            case 'safeuri':
                $secureid = null;

                if (\is_array($cacheParams->get('modeparams'))) {
                    $uri          = $this->input->getArray();
                    $safeuri      = new \stdClass();
                    $noHtmlFilter = InputFilter::getInstance();

                    foreach ($cacheParams->get('modeparams') as $key => $value) {
                        // Use int filter for id/catid to clean out spamy slugs
                        if (isset($uri[$key])) {
                            $safeuri->$key = $noHtmlFilter->clean($uri[$key], $value);
                        }
                    }
                }

                $secureid = md5(serialize([$safeuri, $cacheParams->get('method'), $params]));
                $ret      = $cache->get(
                    [$cacheParams->get('class'), $cacheParams->get('method')],
                    $cacheParams->get('methodparams'),
                    $this->module->id . $view_levels . $secureid . $cacheParams->get('cachesuffix'),
                    $workArounds,
                    $workAroundoptions
                );
                break;

            case 'static':
                $ret = $cache->get(
                    [$cacheParams->get('class'), $cacheParams->get('method')],
                    $cacheParams->get('methodparams'),
                    $this->module->module . md5(serialize($cacheParams->get('methodparams'))) . $cacheParams->get('cachesuffix'),
                    $workArounds,
                    $workAroundoptions
                );
                break;

            case 'itemid':
            default:
                $ret = $cache->get(
                    [$cacheParams->get('class'), $cacheParams->get('method')],
                    $cacheParams->get('methodparams'),
                    $this->module->id . $view_levels . $this->input->getInt('Itemid', null) . $cacheParams->get('cachesuffix'),
                    $workArounds,
                    $workAroundoptions
                );
                break;
        }

        return $ret;
    }
}
