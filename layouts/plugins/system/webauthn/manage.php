<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\User\User;
use Webauthn\PublicKeyCredentialSource;

/**
 * Passwordless Login management interface
 *
 * Generic data
 *
 * @var   FileLayout $this        The Joomla layout renderer
 * @var   array      $displayData The data in array format. DO NOT USE.
 *
 * Layout specific data
 *
 * @var   User       $user                The Joomla user whose passwordless login we are managing
 * @var   bool       $allow_add           Are we allowed to add passwordless login methods
 * @var   array      $credentials         The already stored credentials for the user
 * @var   string     $error               Any error messages
 * @var   array      $knownAuthenticators Known authenticator metadata
 * @var   boolean    $attestationSupport  Is authenticator attestation supported in the plugin?
 */

// Extract the data. Do not remove until the unset() line.
try {
    $app          = Factory::getApplication();
    $loggedInUser = $app->getIdentity();

    $app->getDocument()->getWebAssetManager()
        ->registerAndUseStyle('plg_system_webauthn.backend', 'plg_system_webauthn/backend.css');
} catch (Exception $e) {
    $loggedInUser = new User();
}

$defaultDisplayData = [
        'user'                => $loggedInUser,
        'allow_add'           => false,
        'credentials'         => [],
        'error'               => '',
        'knownAuthenticators' => [],
        'attestationSupport'  => true,
];
extract(array_merge($defaultDisplayData, $displayData));

if ($displayData['allow_add'] === false) {
    $error = Text::_('PLG_SYSTEM_WEBAUTHN_CANNOT_ADD_FOR_A_USER');
    $allow_add = false;
}

// Ensure the GMP or BCmath extension is loaded in PHP - as this is required by third party library
if ($allow_add && function_exists('gmp_intval') === false && function_exists('bccomp') === false) {
    $error = Text::_('PLG_SYSTEM_WEBAUTHN_REQUIRES_GMP');
    $allow_add = false;
}

Text::script('JGLOBAL_CONFIRM_DELETE');

HTMLHelper::_('bootstrap.tooltip', '.plg_system_webauth-has-tooltip');
?>
<div class="plg_system_webauthn" id="plg_system_webauthn-management-interface">
    <?php
    if (is_string($error) && !empty($error)) : ?>
        <div class="alert alert-danger">
            <?php echo htmlentities($error) ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped">
        <caption class="visually-hidden">
            <?php echo Text::_('PLG_SYSTEM_WEBAUTHN_TABLE_CAPTION'); ?>,
        </caption>
        <thead class="table-dark">
        <tr>
            <th <?php if ($attestationSupport) :
                ?>colspan="2"<?php
                endif; ?> scope="col">
                <?php echo Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_FIELD_KEYLABEL_LABEL') ?>
            </th>
            <th scope="col"><?php echo Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_HEADER_ACTIONS_LABEL') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($credentials as $method) : ?>
            <tr data-credential_id="<?php echo $method['id'] ?>">
                <?php
                if ($attestationSupport) :
                    $aaguid = ($method['credential'] instanceof PublicKeyCredentialSource) ? $method['credential']->getAaguid() : '';
                    $authMetadata = $knownAuthenticators[$aaguid->toString()] ?? $knownAuthenticators[''];
                    ?>
                <td class="text-center">
                    <img class="plg_system_webauth-has-tooltip bg-secondary"
                         style="max-width: 6em; max-height: 3em"
                         src="<?php echo $authMetadata->icon ?>"
                         alt="<?php echo $authMetadata->description ?>"
                         title="<?php echo $authMetadata->description ?>">
                </td>
                <?php endif; ?>
                <th scope="row" class="webauthnManagementCell"><?php echo htmlentities($method['label']) ?></th>
                <td class="webauthnManagementCell">
                    <button class="plg_system_webauthn-manage-edit btn btn-secondary">
                        <span class="icon-edit" aria-hidden="true"></span>
                        <?php echo Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_EDIT_LABEL') ?>
                    </button>
                    <button class="plg_system_webauthn-manage-delete btn btn-danger">
                        <span class="icon-minus" aria-hidden="true"></span>
                        <?php echo Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_DELETE_LABEL') ?>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php
        if (empty($credentials)) : ?>
            <tr>
                <td colspan="<?php echo $attestationSupport ? '3' : '2'; ?>">
                    <?php echo Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_HEADER_NOMETHODS_LABEL') ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php
    if ($allow_add) : ?>
        <p class="plg_system_webauthn-manage-add-container">
            <button
                type="button"
                id="plg_system_webauthn-manage-add"
                class="btn btn-success w-100">
                <span class="icon-plus" aria-hidden="true"></span>
                <?php echo Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_ADD_LABEL') ?>
            </button>
        </p>
    <?php endif; ?>
</div>
