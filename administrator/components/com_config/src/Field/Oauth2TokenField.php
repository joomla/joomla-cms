<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Router\Route;
use Joomla\Component\Config\Administrator\Helper\ConfigHelper;

/**
 * Renders OAuth2 token actions for Global Configuration mail settings.
 *
 * @since  __DEPLOY_VERSION__
 */
class Oauth2TokenField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $type = 'Oauth2Token';

    /**
     * The layout to render.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $layout = 'joomla.form.field.oauth2token';

    /**
     * Build the OAuth2 token action UI.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getInput()
    {
        $formData      = $this->form->getData();
        $clientId      = (string) $formData->get('oauth2_client_id');
        $refreshToken  = (string) $formData->get('oauth2_refresh_token');
        $tokenIssuedAt = (string) $formData->get('oauth2_token_issued_at');

        $data = $this->getLayoutData();

        $data['clientId']      = $clientId;
        $data['refreshToken']  = $refreshToken;
        $data['tokenIssuedAt'] = $tokenIssuedAt;
        $data['callbackUrl']   = ConfigHelper::getOAuth2CallbackUrl();
        $data['issueUrl']      = Route::_('index.php?option=com_config&task=request.oauth2auth', false);
        $data['checkUrl']      = Route::_('index.php?option=com_config&task=request.oauth2checktoken', false);

        return $this->getRenderer($this->layout)->render($data);
    }
}
