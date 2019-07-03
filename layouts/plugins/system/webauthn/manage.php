<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.updatenotification
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Plugin\System\Webauthn\Helper\CredentialsCreation;
use Joomla\Plugin\System\Webauthn\Helper\Joomla;

/**
 * Passwordless Login management interface
 *
 *
 * Generic data
 *
 * @var   FileLayout $this        The Joomla layout renderer
 * @var   array      $displayData The data in array format. DO NOT USE.
 *
 * Layout specific data
 *
 * @var   User       $user        The Joomla user whose passwordless login we are managing
 * @var   bool       $allow_add   Are we allowed to add passwordless login methods
 * @var   array      $credentials The already stored credentials for the user
 * @var   string     $error       Any error messages
 */

/**
 * Note about the use of short echo tags.
 *
 * Starting with PHP 5.4.0, short echo tags are always recognized and parsed regardless of the short_open_tag setting
 * in your php.ini. Since we only support *much* newer versions of PHP we can use this construct instead of regular
 * echos to keep the code easier to read.
 */

// Extract the data. Do not remove until the unset() line.
extract(array_merge([
	'user'        => Factory::getApplication()->getIdentity(),
	'allow_add'   => false,
	'credentials' => [],
	'error'       => '',
], $displayData));

HTMLHelper::_('stylesheet', 'plg_system_webauthn/backend.css', [
	'relative' => true,
]);

/**
 * Why not push these configuration variables directly to JavaScript?
 *
 * We need to reload them every time we return from an attempt to authorize an authenticator. Whenever that
 * happens we push raw HTML to the page. However, any SCRIPT tags in that HTML do not get parsed, i.e. they
 * do not replace existing values. This causes any retries to fail. By using a data storage object we circumvent
 * that problem.
 */
$randomId    = 'plg_system_webauthn_' . UserHelper::genRandomPassword(32);
$publicKey   = $allow_add ? base64_encode(CredentialsCreation::createPublicKey($user)) : '{}';
$postbackURL = base64_encode(rtrim(Uri::base(), '/') . '/index.php?' . Joomla::getToken() . '=1');
?>
<div class="plg_system_webauthn" id="plg_system_webauthn-management-interface">
    <span id="<?= $randomId ?>"
          data-public_key="<?= $publicKey ?>"
          data-postback_url="<?= $postbackURL ?>"
    ></span>

	<?php if (is_string($error) && !empty($error)): ?>
        <div class="alert alert-danger">
			<?= htmlentities($error) ?>
        </div>
	<?php endif; ?>

    <table class="table table-striped">
        <thead class="thead-dark">
        <tr>
            <th><?= Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_FIELD_KEYLABEL_LABEL') ?></th>
            <th><?= Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_HEADER_ACTIONS_LABEL') ?></th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ($credentials as $method): ?>
            <tr data-credential_id="<?= $method['id'] ?>">
                <td><?= htmlentities($method['label']) ?></td>
                <td>
                    <button onclick="return plg_system_webauthn_edit_label(this, '<?= $randomId ?>');"
                       class="btn btn-secondary">
                        <span class="icon-edit icon-white"></span>
						<?= Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_EDIT_LABEL') ?>
                    </button>
                    <button onclick="return plg_system_webauthn_delete(this, '<?= $randomId ?>');"
                       class="btn btn-danger">
                        <span class="icon-minus-sign icon-white"></span>
						<?= Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_DELETE_LABEL') ?>
                    </button>
                </td>
            </tr>
		<?php endforeach; ?>
		<?php if (empty($credentials)): ?>
            <tr>
                <td colspan="2">
					<?= Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_HEADER_NOMETHODS_LABEL') ?>
                </td>
            </tr>
		<?php endif; ?>
        </tbody>
    </table>

	<?php if ($allow_add): ?>
		<p class="plg_system_webauthn-manage-add-container">
			<button
				type="button"
				onclick="plg_system_webauthn_create_credentials('<?= $randomId ?>', '#plg_system_webauthn-management-interface'); return false;"
				class="btn btn-success btn-block">
				<span class="icon-plus icon-white"></span>
				<?= Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_ADD_LABEL') ?>
			</button>
		</p>
	<?php endif; ?>
</div>
