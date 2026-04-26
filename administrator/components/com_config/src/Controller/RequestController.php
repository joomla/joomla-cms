<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Controller;

use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;
use Joomla\Component\Config\Administrator\Helper\ConfigHelper;
use Joomla\Component\Config\Administrator\Model\ApplicationModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Handles OAuth2 request actions for Global Configuration.
 *
 * @since  4.0.0
 */
class RequestController extends BaseController
{
    /**
     * Redirects an authorized administrator to the provider authorization endpoint.
     *
     * @return  void
     *
     * @since   6.2.0
     */
    public function oauth2auth(): void
    {
        if (!$this->app->getIdentity()->authorise('core.admin')) {
            $this->setRedirect(Route::_('index.php?option=com_config&view=application', false), Text::_('JERROR_ALERTNOAUTHOR'), 'error');

            return;
        }

        $params  = $this->app->getConfig();
        $session = $this->app->getSession();
        $state   = Session::getFormToken();
        $session->set('com_config.oauth2_state', $state);

        $providerConfig = $this->resolveProviderConfig();

        if (empty($providerConfig['authorize_url']) || empty($providerConfig['client_id'])) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::_('COM_CONFIG_MAIL_OAUTH2_AUTHORIZE_CONFIG_MISSING'),
                'warning'
            );

            return;
        }

        $query = [
            'client_id'     => $providerConfig['client_id'],
            'response_type' => 'code',
            'redirect_uri'  => ConfigHelper::getOAuth2CallbackUrl(),
            'response_mode' => 'query',
            'scope'         => $providerConfig['scope'],
            'state'         => $state,
        ];

        $authUrl = $providerConfig['authorize_url'] . '?' . http_build_query($query);

        $this->setRedirect($authUrl);
    }

    /**
     * Processes the OAuth2 callback and stores the received refresh token.
     *
     * @return  void
     *
     * @since   6.2.0
     */
    public function oauth2callback(): void
    {
        if (!$this->app->getIdentity()->authorise('core.admin')) {
            $this->setRedirect(Route::_('index.php?option=com_config&view=application', false), Text::_('JERROR_ALERTNOAUTHOR'), 'error');

            return;
        }

        $stateFromProvider = $this->input->getString('state');
        $stateFromSession  = $this->app->getSession()->get('com_config.oauth2_state');
        $code              = $this->input->getString('code');

        if (!$stateFromProvider || !$stateFromSession || !hash_equals($stateFromSession, $stateFromProvider)) {
            $this->setRedirect(Route::_('index.php?option=com_config&view=application', false), Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $this->app->getSession()->set('com_config.oauth2_state', null);

        if (!$code) {
            $this->setRedirect(Route::_('index.php?option=com_config&view=application', false), Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return;
        }

        $refreshToken   = '';
        $providerConfig = $this->resolveProviderConfig();

        if (empty($providerConfig['token_url']) || empty($providerConfig['client_id']) || empty($providerConfig['client_secret'])) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::_('COM_CONFIG_MAIL_OAUTH2_TOKEN_CONFIG_MISSING'),
                'warning'
            );

            return;
        }

        try {
            $http     = HttpFactory::getHttp();
            $body     = http_build_query(
                array_filter(
                    [
                    'client_id'     => $providerConfig['client_id'],
                    'client_secret' => $providerConfig['client_secret'],
                    'code'          => $code,
                    'grant_type'    => 'authorization_code',
                    'redirect_uri'  => ConfigHelper::getOAuth2CallbackUrl(),
                    'scope'         => $providerConfig['scope'],
                    ],
                    static fn ($value) => $value !== ''
                )
            );
            $response = $http->post($providerConfig['token_url'], $body, ['Content-Type' => 'application/x-www-form-urlencoded']);
            $data     = json_decode((string) $response->body, true);

            if (!\is_array($data) || empty($data['refresh_token'])) {
                throw new \RuntimeException(Text::_('COM_CONFIG_MAIL_OAUTH2_TOKEN_CREATE_FAILED'));
            }

            $refreshToken = (string) $data['refresh_token'];
        } catch (\Throwable $e) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                $e->getMessage(),
                'error'
            );

            return;
        }

        $formData                         = (array) $this->app->getUserState('com_config.config.global.data', []);

        if (!\in_array((string) ($formData['mailer'] ?? 'mail'), ['mail', 'sendmail', 'smtp', 'smtpoauth2'], true)) {
            $formData['mailer'] = 'mail';
        }

        $formData['oauth2_refresh_token']   = $refreshToken;
        $formData['oauth2_token_issued_at'] = gmdate('Y-m-d H:i:s') . ' UTC';
        $this->app->setUserState('com_config.config.global.data', $formData);

        // Persist token immediately to avoid losing it when the form is submitted without the hidden field value.
        try {
            if (!$this->app->getIdentity()->authorise('core.admin')) {
                throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'));
            }

            $model                              = new ApplicationModel();
            $saveData                           = ArrayHelper::fromObject(new \JConfig());
            $saveData['oauth2_refresh_token']   = $refreshToken;
            $saveData['oauth2_token_issued_at'] = gmdate('Y-m-d H:i:s') . ' UTC';

            if (!$model->save($saveData)) {
                throw new \RuntimeException(Text::_('COM_CONFIG_ERROR_WRITE_FAILED'));
            }
        } catch (\Throwable $e) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::sprintf('COM_CONFIG_MAIL_OAUTH2_TOKEN_SAVE_FAILED', $e->getMessage()),
                'warning'
            );

            return;
        }

        $this->setRedirect(
            Route::_('index.php?option=com_config&view=application', false),
            Text::_('COM_CONFIG_MAIL_OAUTH2_TOKEN_SAVED'),
            'message'
        );
    }

    /**
     * Checks whether the configured OAuth2 refresh token can be exchanged successfully.
     *
     * @return  void
     *
     * @since   6.2.0
     */
    public function oauth2checktoken(): void
    {
        if (!$this->app->getIdentity()->authorise('core.admin')) {
            $this->setRedirect(Route::_('index.php?option=com_config&view=application', false), Text::_('JERROR_ALERTNOAUTHOR'), 'error');

            return;
        }

        $providerConfig = $this->resolveProviderConfig();
        $refreshToken   = (string) ($providerConfig['refresh_token'] ?? '');

        if (!$providerConfig['token_url'] || !$providerConfig['client_id'] || !$providerConfig['client_secret'] || !$refreshToken) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::_('COM_CONFIG_MAIL_OAUTH2_TOKEN_CHECK_MISSING'),
                'warning'
            );

            return;
        }

        try {
            $http = HttpFactory::getHttp();
            $body = http_build_query(
                array_filter(
                    [
                    'client_id'     => $providerConfig['client_id'],
                    'client_secret' => $providerConfig['client_secret'],
                    'refresh_token' => $refreshToken,
                    'grant_type'    => 'refresh_token',
                    'scope'         => $providerConfig['scope'],
                    ],
                    static fn ($value) => $value !== ''
                )
            );

            $response = $http->post($providerConfig['token_url'], $body, ['Content-Type' => 'application/x-www-form-urlencoded']);
            $data     = json_decode((string) $response->body, true);

            if (!\is_array($data) || empty($data['access_token'])) {
                throw new \RuntimeException(Text::_('COM_CONFIG_MAIL_OAUTH2_TOKEN_CHECK_INVALID'));
            }

            $expiresIn = isset($data['expires_in']) ? (int) $data['expires_in'] : 0;
            $message   = Text::sprintf('COM_CONFIG_MAIL_OAUTH2_TOKEN_CHECK_VALID', $expiresIn);

            $this->setRedirect(Route::_('index.php?option=com_config&view=application', false), $message, 'message');
        } catch (\Throwable $e) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::sprintf('COM_CONFIG_MAIL_OAUTH2_TOKEN_CHECK_FAILED', $e->getMessage()),
                'error'
            );
        }
    }

    /**
     * Build provider-specific OAuth2 configuration from global configuration.
     *
     * Supports Microsoft, Google and custom endpoint providers.
     *
     * @return  array<string, string>
     *
     * @since   6.2.0
     */
    private function resolveProviderConfig(): array
    {
        $params = $this->app->getConfig();

        $provider     = (string) ($params->get('oauth2_provider') ?: 'microsoft');
        $clientId     = (string) $params->get('oauth2_client_id');
        $clientSecret = (string) $params->get('oauth2_client_secret');
        $refreshToken = (string) $params->get('oauth2_refresh_token');
        $tenantMode   = (string) $params->get('oauth2_tenant_mode', '');
        $tenantId   = (string) $params->get('oauth2_tenant_id', 'common');

        if ($tenantMode === '') {
            $tenantMode = strtolower($tenantId) !== 'common' ? 'tenant' : 'common';
        }

        if ($tenantMode !== 'tenant') {
            $tenantId = 'common';
        } elseif ($tenantId === '') {
            $tenantId = 'common';
        }

        if ($provider === 'google') {
            return [
                'provider'      => 'google',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'scope'         => (string) ($params->get('oauth2_scope') ?: 'https://mail.google.com/'),
                'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
                'token_url'     => 'https://oauth2.googleapis.com/token',
            ];
        }

        if ($provider === 'custom') {
            return [
                'provider'      => 'custom',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'scope'         => (string) ($params->get('oauth2_scope') ?: ''),
                'authorize_url' => (string) $params->get('oauth2_authorize_url'),
                'token_url'     => (string) $params->get('oauth2_token_url'),
            ];
        }

        return [
            'provider'      => 'microsoft',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'scope'         => (string) ($params->get('oauth2_scope') ?: 'https://outlook.office.com/SMTP.Send offline_access'),
            'authorize_url' => 'https://login.microsoftonline.com/' . rawurlencode($tenantId) . '/oauth2/v2.0/authorize',
            'token_url'     => 'https://login.microsoftonline.com/' . rawurlencode($tenantId) . '/oauth2/v2.0/token',
        ];
    }

    /**
     * Execute the controller.
     *
     * @return  mixed  A rendered view or false
     *
     * @since   3.2
     */
    public function getJson()
    {
        $componentFolder = $this->input->getWord('option', 'com_config');

        if ($this->app->isClient('administrator')) {
            $viewName = $this->input->getWord('view', 'application');
        } else {
            $viewName = $this->input->getWord('view', 'config');
        }

        // Register the layout paths for the view
        $paths = new \SplPriorityQueue();

        if ($this->app->isClient('administrator')) {
            $paths->insert(JPATH_ADMINISTRATOR . '/components/' . $componentFolder . '/view/' . $viewName . '/tmpl', 1);
        } else {
            $paths->insert(JPATH_BASE . '/components/' . $componentFolder . '/view/' . $viewName . '/tmpl', 1);
        }

        $model     = new \Joomla\Component\Config\Administrator\Model\ApplicationModel();
        $component = $model->getState()->get('component.option');

        // Access check.
        if (
            !$this->app->getIdentity()->authorise('core.admin', $component)
            && !$this->app->getIdentity()->authorise('core.options', $component)
        ) {
            $this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

            return false;
        }

        try {
            $data = $model->getData();
        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        // Required data
        $requiredData = [
            'sitename'            => null,
            'offline'             => null,
            'access'              => null,
            'list_limit'          => null,
            'MetaDesc'            => null,
            'MetaRights'          => null,
            'sef'                 => null,
            'sitename_pagetitles' => null,
            'debug'               => null,
            'debug_lang'          => null,
            'error_reporting'     => null,
            'mailfrom'            => null,
            'fromname'            => null,
        ];

        $data = array_intersect_key($data, $requiredData);

        return json_encode($data);
    }
}
