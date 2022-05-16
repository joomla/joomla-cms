<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Controller;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\Helper\Tfa as TfaHelper;
use Joomla\Component\Users\Administrator\Model\MethodsModel;
use Joomla\Input\Input;
use ReflectionObject;
use RuntimeException;

/**
 * Two Factor Authentication methods selection and management controller
 *
 * @since __DEPLOY_VERSION__
 */
class MethodsController extends BaseController
{
	/**
	 * Public constructor
	 *
	 * @param   array                     $config   Plugin configuration
	 * @param   MVCFactoryInterface|null  $factory  MVC Factory for the com_users component
	 * @param   CMSApplication|null       $app      CMS application object
	 * @param   Input|null                $input    Joomla CMS input object
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct($config = [], MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null)
	{
		// We have to tell Joomla what is the name of the view, otherwise it defaults to the name of the *component*.
		$config['default_view'] = 'Methods';

		parent::__construct($config, $factory, $app, $input);
	}

	/**
	 * Disable Two Factor Authentication for the current user
	 *
	 * @param   bool   $cachable     Can this view be cached
	 * @param   array  $urlparams    An array of safe url parameters and their variable types, for valid values see
	 *                               {@link JFilterInput::clean()}.
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function disable($cachable = false, $urlparams = []): void
	{
		$this->assertLoggedInUser();

		$this->checkToken($this->input->getMethod());

		// Make sure I am allowed to edit the specified user
		$userId = $this->input->getInt('user_id', null);
		$user   = ($userId === null)
			? $this->app->getIdentity()
			: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);
		$user   = $user ?? Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);

		if (!TfaHelper::canEditUser($user))
		{
			throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		// Delete all TFA Methods for the user
		/** @var MethodsModel $model */
		$model   = $this->getModel('Methods');
		$type    = null;
		$message = null;

		$this->app->triggerEvent(
			'onComUsersControllerMethodsBeforeDisable',
			new GenericEvent('onComUsersControllerMethodsBeforeDisable', [$user])
		);

		try
		{
			$model->deleteAll($user);
		}
		catch (Exception $e)
		{
			$message = $e->getMessage();
			$type    = 'error';
		}

		// Redirect
		// phpcs:ignore
		$url       = Route::_('index.php?option=com_users&task=methods.display&user_id=' . $userId, false);
		$returnURL = $this->input->getBase64('returnurl');

		if (!empty($returnURL))
		{
			$url = base64_decode($returnURL);
		}

		$this->setRedirect($url, $message, $type);
	}

	/**
	 * List all available Two Step Validation Methods available and guide the user to setting them up
	 *
	 * @param   bool   $cachable     Can this view be cached
	 * @param   array  $urlparams    An array of safe url parameters and their variable types, for valid values see
	 *                               {@link JFilterInput::clean()}.
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($cachable = false, $urlparams = []): void
	{
		$this->assertLoggedInUser();
		$this->setSiteTemplateStyle();

		// Make sure I am allowed to edit the specified user
		$userId  = $this->input->getInt('user_id', null);
		$user    = ($userId === null)
			? $this->app->getIdentity()
			: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);
		$user    = $user ?? Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);

		if (!TfaHelper::canEditUser($user))
		{
			throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$returnURL  = $this->input->getBase64('returnurl');
		$viewLayout = $this->input->get('layout', 'default', 'string');
		$view       = $this->getView('Methods', 'html');
		$view->setLayout($viewLayout);
		$view->returnURL = $returnURL;
		$view->user      = $user;

		$backupCodesModel = $this->getModel('Backupcodes');
		$view->setModel($backupCodesModel, false);

		parent::display($cachable, $urlparams);
	}

	/**
	 * Disable Two Factor Authentication for the current user
	 *
	 * @param   bool   $cachable     Can this view be cached
	 * @param   array  $urlparams    An array of safe url parameters and their variable types, for valid values see
	 *                               {@link JFilterInput::clean()}.
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function dontshowthisagain($cachable = false, $urlparams = []): void
	{
		$this->assertLoggedInUser();

		$this->checkToken($this->input->getMethod());

		// Make sure I am allowed to edit the specified user
		$userId  = $this->input->getInt('user_id', null);
		$user    = ($userId === null)
			? $this->app->getIdentity()
			: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);
		$user    = $user ?? Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);

		if (!TfaHelper::canEditUser($user))
		{
			throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$this->app->triggerEvent(
			'onComUsersControllerMethodsBeforeDontshowthisagain',
			new GenericEvent('onComUsersControllerMethodsBeforeDontshowthisagain', [$user])
		);

		/** @var MethodsModel $model */
		$model = $this->getModel('Methods');
		$model->setFlag($user, true);

		// Redirect
		$url       = Uri::base();
		$returnURL = $this->input->getBase64('returnurl');

		if (!empty($returnURL))
		{
			$url = base64_decode($returnURL);
		}

		$this->setRedirect($url);
	}

	/**
	 * Assert that there is a user currently logged in
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	private function assertLoggedInUser(): void
	{
		$user = $this->app->getIdentity()
			?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);

		if ($user->guest)
		{
			throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}

	/**
	 * Set a specific site template style in the frontend application
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	private function setSiteTemplateStyle(): void
	{
		$itemId = $this->app->input->get('Itemid');

		if (!empty($itemId))
		{
			return;
		}

		$templateStyle = (int) ComponentHelper::getParams('com_users')
			->get('captive_template', '');

		if (empty($templateStyle) || !$this->app->isClient('site'))
		{
			return;
		}

		$this->app->input->set('templateStyle', $templateStyle);

		$refApp      = new ReflectionObject($this->app);
		$refTemplate = $refApp->getProperty('template');
		$refTemplate->setAccessible(true);
		$refTemplate->setValue($this->app, null);

		$template = $this->app->getTemplate(true);

		$this->app->set('theme', $template->template);
		$this->app->set('themeParams', $template->params);
	}

}
