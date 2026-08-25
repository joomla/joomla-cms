<?php

/**
 * @package         Joomla.Administrator
 * @subpackage      com_config
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\Component\Config\Administrator\Model\ApplicationModel;
use Joomla\OAuth2\Client;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Mail Controller
 *
 * @since  __DEPLOY_VERSION__
 */
class MailController extends BaseController
{
    /**
     * Redirects an authorized administrator to the provider authorization endpoint.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function oauth2auth(): void
    {
        if (!$this->app->getIdentity()->authorise('core.admin')) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::_('JERROR_ALERTNOAUTHOR'),
                'error',
            );

            return;
        }

        $providerConfig = $this->resolveProviderConfig();

        if (empty($providerConfig['authorize_url']) || empty($providerConfig['client_id'])) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::_('COM_CONFIG_MAIL_OAUTH2_AUTHORIZE_CONFIG_MISSING'),
                'warning',
            );

            return;
        }

        $state = UserHelper::genRandomPassword(32);

        $this->app->getSession()->set('com_config.oauth2_state', $state);

        try {
            $oauth2 = $this->createOAuth2Client($providerConfig, $state);

            $this->setRedirect($oauth2->createUrl());
        } catch (\Throwable $e) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                $e->getMessage(),
                'error',
            );
        }
    }


    /**
     * Processes the OAuth2 callback and stores the received refresh token.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function oauth2callback(): void
    {
        if (!$this->app->getIdentity()->authorise('core.admin')) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::_('JERROR_ALERTNOAUTHOR'),
                'error',
            );

            return;
        }

        $session           = $this->app->getSession();
        $stateFromProvider = $this->input->getString('state');
        $stateFromSession  = $session->get('com_config.oauth2_state');

        if (
            !$stateFromProvider
            || !$stateFromSession
            || !hash_equals($stateFromSession, $stateFromProvider)
        ) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::_('JINVALID_TOKEN'),
                'error',
            );

            return;
        }

        $session->set('com_config.oauth2_state', null);

        $error = $this->input->getString('error');

        if ($error !== '') {
            $description = $this->input->getString('error_description');

            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                $description ?: $error,
                'error'
            );

            return;
        }

        if (!$this->input->getString('code')) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'error',
            );

            return;
        }

        $providerConfig = $this->resolveProviderConfig();

        if (
            empty($providerConfig['token_url'])
            || empty($providerConfig['client_id'])
            || empty($providerConfig['client_secret'])
        ) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::_('COM_CONFIG_MAIL_OAUTH2_TOKEN_CONFIG_MISSING'),
                'warning',
            );

            return;
        }

        try {
            $oauth2 = $this->createOAuth2Client($providerConfig);

            $token = $oauth2->authenticate();

            if (
                !\is_array($token)
                || empty($token['refresh_token'])
            ) {
                throw new \RuntimeException(
                    Text::_('COM_CONFIG_MAIL_OAUTH2_TOKEN_CREATE_FAILED'),
                );
            }

            $refreshToken = (string)$token['refresh_token'];
        } catch (\Throwable $e) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                $e->getMessage(),
                'error',
            );

            return;
        }

        $issuedAt = gmdate('Y-m-d H:i:s') . ' UTC';

        $formData = (array)$this->app->getUserState(
            'com_config.config.global.data',
            [],
        );

        $formData['smtp_oauth2_refresh_token']   = $refreshToken;
        $formData['smtp_oauth2_token_issued_at'] = $issuedAt;

        $this->app->setUserState(
            'com_config.config.global.data',
            $formData,
        );

        try {
            $model    = new ApplicationModel();
            $saveData = $model->getData();

            $saveData['smtp_oauth2_refresh_token']   = $refreshToken;
            $saveData['smtp_oauth2_token_issued_at'] = $issuedAt;

            if (!$model->save($saveData)) {
                throw new \RuntimeException(
                    Text::_('COM_CONFIG_ERROR_WRITE_FAILED'),
                );
            }
        } catch (\Throwable $e) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::sprintf(
                    'COM_CONFIG_MAIL_OAUTH2_TOKEN_SAVE_FAILED',
                    $e->getMessage(),
                ),
                'warning',
            );

            return;
        }

        $this->setRedirect(
            Route::_('index.php?option=com_config&view=application', false),
            Text::_('COM_CONFIG_MAIL_OAUTH2_TOKEN_SAVED'),
            'message',
        );
    }

    /**
     * Checks whether the configured OAuth2 refresh token can be exchanged successfully.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function oauth2checktoken(): void
    {
        if (!$this->app->getIdentity()->authorise('core.admin')) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::_('JERROR_ALERTNOAUTHOR'),
                'error',
            );

            return;
        }

        $providerConfig = $this->resolveProviderConfig();

        if (
            empty($providerConfig['token_url'])
            || empty($providerConfig['client_id'])
            || empty($providerConfig['client_secret'])
            || empty($providerConfig['refresh_token'])
        ) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::_('COM_CONFIG_MAIL_OAUTH2_TOKEN_CHECK_MISSING'),
                'warning',
            );

            return;
        }

        try {
            $oauth2 = $this->createOAuth2Client($providerConfig);

            $token = $oauth2->refreshToken(
                $providerConfig['refresh_token'],
            );

            if (
                !\is_array($token)
                || empty($token['access_token'])
            ) {
                throw new \RuntimeException(
                    Text::_('COM_CONFIG_MAIL_OAUTH2_TOKEN_CHECK_INVALID'),
                );
            }

            $expiresIn = isset($token['expires_in'])
                ? (int)$token['expires_in']
                : 0;

            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::sprintf(
                    'COM_CONFIG_MAIL_OAUTH2_TOKEN_CHECK_VALID',
                    $expiresIn,
                ),
                'message',
            );
        } catch (\Throwable $e) {
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=application', false),
                Text::sprintf(
                    'COM_CONFIG_MAIL_OAUTH2_TOKEN_CHECK_FAILED',
                    $e->getMessage(),
                ),
                'error',
            );
        }
    }

    /**
     * Creates the OAuth2 client.
     *
     * @param array<string, string>  $providerConfig OAuth2 provider configuration.
     * @param string                 $state          OAuth2 state value.
     *
     * @return  Client
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createOAuth2Client(array  $providerConfig, string $state = ''): Client
    {
        $requestParams = [
            'response_mode' => 'query',
            'prompt'        => 'consent',
            'access_type'   => 'offline',
        ];

        $options = [
            'authurl'       => $providerConfig['authorize_url'],
            'tokenurl'      => $providerConfig['token_url'],
            'clientid'      => $providerConfig['client_id'],
            'clientsecret'  => $providerConfig['client_secret'],
            'redirecturi'   => Uri::base() . 'index.php?option=com_config&task=mail.oauth2callback&format=raw',
            'scope'         => $providerConfig['scope'],
            'state'         => $state,
            'requestparams' => $requestParams,
            'userefresh'    => true,
            'sendheaders'   => false,
        ];

        return new Client(
            $options,
            null,
            $this->input,
        );
    }

    /**
     * Build OAuth2 configuration from global configuration.
     *
     * @return  array<string, string>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function resolveProviderConfig(): array
    {
        $params = $this->app->getConfig();

        return [
            'client_id'     => $params->get('smtp_oauth2_client_id', ''),
            'client_secret' => $params->get('smtp_oauth2_client_secret', ''),
            'refresh_token' => $params->get('smtp_oauth2_refresh_token', ''),
            'scope'         => $params->get('smtp_oauth2_scope', ''),
            'authorize_url' => $params->get('smtp_oauth2_authorize_url', ''),
            'token_url'     => $params->get('smtp_oauth2_token_url', ''),
        ];
    }

    /**
     * Method to send the test mail.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function sendtestmail()
    {
        // Send json mime type.
        $this->app->mimeType = 'application/json';
        $this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
        $this->app->sendHeaders();

        // Check if user token is valid.
        if (!Session::checkToken()) {
            $this->app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
            echo new JsonResponse();
            $this->app->close();
        }

        // Check if the user is authorized to do this.
        if (!$this->app->getIdentity()->authorise('core.admin')) {
            $this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            echo new JsonResponse();
            $this->app->close();
        }

        /** @var \Joomla\Component\Config\Administrator\Model\ApplicationModel $model */
        $model = $this->getModel('Application', 'Administrator');

        echo new JsonResponse($model->sendTestMail());

        $this->app->close();
    }

}
