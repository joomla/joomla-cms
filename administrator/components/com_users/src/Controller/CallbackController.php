<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Event\MultiFactor\Callback;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Input\Input;
use RuntimeException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Multi-factor Authentication plugins' AJAX callback controller
 *
 * @since 4.2.0
 */
class CallbackController extends BaseController
{
    /**
     * Public constructor
     *
     * @param   array                     $config   Plugin configuration
     * @param   MVCFactoryInterface|null  $factory  MVC Factory for the com_users component
     * @param   CMSApplication|null       $app      CMS application object
     * @param   Input|null                $input    Joomla CMS input object
     *
     * @since 4.2.0
     */
    public function __construct(array $config = [], MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        $this->registerDefaultTask('callback');
    }

    /**
     * Implement a callback feature, typically used for OAuth2 authentication
     *
     * @param   bool         $cachable    Can this view be cached
     * @param   array|bool   $urlparams   An array of safe url parameters and their variable types, for valid values see
     *                                    {@link JFilterInput::clean()}.
     *
     * @return  void
     * @since 4.2.0
     */
    public function callback($cachable = false, $urlparams = false): void
    {
        $app = $this->app;

        // Get the Method and make sure it's non-empty
        $method = $this->input->getCmd('method', '');

        if (empty($method)) {
            throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        PluginHelper::importPlugin('multifactorauth');

        $event = new Callback($method);
        $this->app->getDispatcher()->dispatch($event->getName(), $event);

        /**
         * The first plugin to handle the request should either redirect or close the application. If we are still here
         * no plugin handled the request successfully. Show an error.
         */
        throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
    }
}
