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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Config\Administrator\Helper\ConfigHelper;

class Oauth2TokenField extends FormField
{
    protected $type = 'Oauth2Token';

    protected function getInput()
    {
        $formData      = $this->form->getData();
        $clientId      = (string) $formData->get('oauth2_client_id');
        $refreshToken  = (string) $formData->get('oauth2_refresh_token');
        $tokenIssuedAt = (string) $formData->get('oauth2_token_issued_at');

        if (!$clientId) {
            return '<div class="alert alert-warning">' . Text::_('COM_CONFIG_MAIL_OAUTH2_CLIENT_ID_REQUIRED') . '</div>';
        }

        $callbackUrl  = ConfigHelper::getOAuth2CallbackUrl();
        $issueUrl     = Route::_('index.php?option=com_config&task=request.oauth2auth', false);
        $checkUrl     = Route::_('index.php?option=com_config&task=request.oauth2checktoken', false);
        $buttonLabel  = $refreshToken !== '' ? Text::_('COM_CONFIG_MAIL_OAUTH2_BUTTON_TITLE_REISSUE') : Text::_('COM_CONFIG_MAIL_OAUTH2_BUTTON_TITLE');
        $issuedAtHtml = '';

        if ($tokenIssuedAt !== '') {
            $issuedAtHtml = '<div style="font-size: 11px; margin-top: 10px">'
                . Text::sprintf('COM_CONFIG_MAIL_OAUTH2_TOKEN_ISSUED_AT', htmlspecialchars($tokenIssuedAt, ENT_QUOTES, 'UTF-8'))
                . '</div>';
        }

        return '<div class="d-flex gap-2 flex-wrap"><a class="btn btn-info" href="'
            . $issueUrl
            . '">'
            . $buttonLabel
            . '</a>'
            . ($refreshToken !== '' ? '<a class="btn btn-secondary" href="' . $checkUrl . '">' . Text::_('COM_CONFIG_MAIL_OAUTH2_BUTTON_TITLE_VALIDATE') . '</a>' : '')
            . '</div><div style="font-size: 11px; margin-top: 10px">'
            . sprintf(Text::_('COM_CONFIG_MAIL_OAUTH2_CALLBACK_HINT'), $callbackUrl)
            . '</div>'
            . $issuedAtHtml;
    }
}
