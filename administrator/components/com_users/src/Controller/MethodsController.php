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
use Joomla\CMS\Event\MultiFactor\NotifyActionLog;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryAwareInterface;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Component\Users\Administrator\Helper\Mfa as MfaHelper;
use Joomla\Component\Users\Administrator\Model\MethodsModel;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Multi-factor Authentication methods selection and management controller
 *
 * @since 4.2.0
 */
class MethodsController extends BaseController implements UserFactoryAwareInterface
{
    use UserFactoryAwareTrait;

    /**
     * Public constructor
     *
     * @param   array                 $config   Plugin configuration
     * @param   ?MVCFactoryInterface  $factory  MVC Factory for the com_users component
     * @param   ?CMSApplication       $app      CMS application object
     * @param   ?Input                $input    Joomla CMS input object
     *
     * @since 4.2.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null)
    {
        // We have to tell Joomla what is the name of the view, otherwise it defaults to the name of the *component*.
        $config['default_view'] = 'Methods';

        parent::__construct($config, $factory, $app, $input);
    }

    /**
     * Disable Multi-factor Authentication for the current user
     *
     * @param   bool   $cachable   Can this view be cached
     * @param   array  $urlparams  An array of safe url parameters and their variable types.
     *                 @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  void
     * @since   4.2.0
     */
    public function disable($cachable = false, $urlparams = []): void
    {
        $this->assertLoggedInUser();

        $this->checkToken($this->input->getMethod());

        // Make sure I am allowed to edit the specified user
        $userId = $this->input->getInt('user_id', null);
        $user   = ($userId === null)
            ? $this->app->getIdentity()
            : $this->getUserFactory()->loadUserById($userId);
        $user   = $user ?? $this->getUserFactory()->loadUserById(0);

        if (!MfaHelper::canDeleteMethod($user)) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        // Delete all MFA Methods for the user
        /** @var MethodsModel $model */
        $model   = $this->getModel('Methods');
        $type    = null;
        $message = null;

        $event = new NotifyActionLog('onComUsersControllerMethodsBeforeDisable', [$user]);
        $this->app->getDispatcher()->dispatch($event->getName(), $event);

        try {
            $model->deleteAll($user);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $type    = 'error';
        }

        // Redirect
        $url       = Route::_('index.php?option=com_users&task=methods.display&user_id=' . $userId, false);
        $returnURL = $this->input->getBase64('returnurl');

        if (!empty($returnURL) && Uri::isInternal(base64_decode($returnURL))) {
            $url = base64_decode($returnURL);
        }

        $this->setRedirect($url, $message, $type);
    }

    /**
     * List all available Multi-factor Authentication Methods available and guide the user to setting them up
     *
     * @param   bool   $cachable   Can this view be cached
     * @param   array  $urlparams  An array of safe url parameters and their variable types.
     *                 @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  void
     * @since   4.2.0
     */
    public function display($cachable = false, $urlparams = []): void
    {
        $this->assertLoggedInUser();

        // Make sure I am allowed to edit the specified user
        $userId = $this->input->getInt('user_id', null);
        $user   = ($userId === null)
            ? $this->app->getIdentity()
            : $this->getUserFactory()->loadUserById($userId);
        $user   = $user ?? $this->getUserFactory()->loadUserById(0);

        if (!MfaHelper::canShowConfigurationInterface($user)) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $returnURL  = $this->input->getBase64('returnurl');
        $viewLayout = $this->input->get('layout', 'default', 'string');
        $view       = $this->getView('Methods', 'html');
        $view->setLayout($viewLayout);
        $view->returnURL = $returnURL;
        $view->user      = $user;
        $view->document  = $this->app->getDocument();

        $methodsModel = $this->getModel('Methods');
        $view->setModel($methodsModel, true);

        $backupCodesModel = $this->getModel('Backupcodes');
        $view->setModel($backupCodesModel, false);

        $view->display();
    }

    /**
     * Disable Multi-factor Authentication for the current user
     *
     * @param   bool   $cachable   Can this view be cached
     * @param   array  $urlparams  An array of safe url parameters and their variable types.
     *                 @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  void
     * @since   4.2.0
     */
    public function doNotShowThisAgain($cachable = false, $urlparams = []): void
    {
        $this->assertLoggedInUser();

        $this->checkToken($this->input->getMethod());

        // Make sure I am allowed to edit the specified user
        $userId = $this->input->getInt('user_id', null);
        $user   = ($userId === null)
            ? $this->app->getIdentity()
            : $this->getUserFactory()->loadUserById($userId);
        $user   = $user ?? $this->getUserFactory()->loadUserById(0);

        if (!MfaHelper::canAddEditMethod($user)) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $event = new NotifyActionLog('onComUsersControllerMethodsBeforeDoNotShowThisAgain', [$user]);
        $this->app->getDispatcher()->dispatch($event->getName(), $event);

        /** @var MethodsModel $model */
        $model = $this->getModel('Methods');
        $model->setFlag($user, true);

        // Redirect
        $url       = Uri::base();
        $returnURL = $this->input->getBase64('returnurl');

        if (!empty($returnURL) && Uri::isInternal(base64_decode($returnURL))) {
            $url = base64_decode($returnURL);
        }

        $this->setRedirect($url);
    }

    /**
     * Assert that there is a user currently logged in
     *
     * @return  void
     * @since   4.2.0
     */
    private function assertLoggedInUser(): void
    {
        $user = $this->app->getIdentity() ?: $this->getUserFactory()->loadUserById(0);

        if ($user->guest) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }
}
