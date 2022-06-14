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

// Extract the data. Do not remove until the unset() line.
try
{
	$app          = Factory::getApplication();
	$loggedInUser = $app->getIdentity();

	$app->getDocument()->getWebAssetManager()
		->registerAndUseStyle('plg_system_webauthn.backend', 'plg_system_webauthn/backend.css');
}
catch (Exception $e)
{
	$loggedInUser = new User;
}

$defaultDisplayData = [
	'user'        => $loggedInUser,
	'allow_add'   => false,
	'credentials' => [],
	'error'       => '',
];
extract(array_merge($defaultDisplayData, $displayData));

if ($displayData['allow_add'] === false)
{
	$error = Text::_('PLG_SYSTEM_WEBAUTHN_CANNOT_ADD_FOR_A_USER');
	$allow_add = false;
}

// Ensure the GMP or BCmath extension is loaded in PHP - as this is required by third party library
if ($allow_add && function_exists('gmp_intval') === false && function_exists('bccomp') === false)
{
	$error = Text::_('PLG_SYSTEM_WEBAUTHN_REQUIRES_GMP');
	$allow_add = false;
}

/**
 * Why not push these configuration variables directly to JavaScript?
 *
 * We need to reload them every time we return from an attempt to authorize an authenticator. Whenever that
 * happens we push raw HTML to the page. However, any SCRIPT tags in that HTML do not get parsed, i.e. they
 * do not replace existing values. This causes any retries to fail. By using a data storage object we circumvent
 * that problem.
 */
$randomId    = 'plg_system_webauthn_' . UserHelper::genRandomPassword(32);
// phpcs:ignore
$publicKey   = $allow_add ? base64_encode(CredentialsCreation::createPublicKey($user)) : '{}';
$postbackURL = base64_encode(rtrim(Uri::base(), '/') . '/index.php?' . Joomla::getToken() . '=1');

?>
<div class="plg_system_webauthn" id="plg_system_webauthn-management-interface">
	<span id="<?php echo $randomId ?>"
		  data-public_key="<?php echo $publicKey ?>"
		  data-postback_url="<?php echo $postbackURL ?>"
	></span>

	<?php // phpcs:ignore
	if (is_string($error) && !empty($error)): ?>
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
			<th scope="col"><?php echo Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_FIELD_KEYLABEL_LABEL') ?></th>
			<th scope="col"><?php echo Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_HEADER_ACTIONS_LABEL') ?></th>
		</tr>
		</thead>
		<tbody>
		<?php // phpcs:ignore
		foreach ($credentials as $method): ?>
			<tr data-credential_id="<?php echo $method['id'] ?>">
				<th scope="row" class="webauthnManagementCell"><?php echo htmlentities($method['label']) ?></th>
				<td class="webauthnManagementCell">
					<button data-random-id="<?php echo $randomId; ?>" class="plg_system_webauthn-manage-edit btn btn-secondary">
						<span class="icon-edit" aria-hidden="true"></span>
						<?php echo Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_EDIT_LABEL') ?>
					</button>
					<button data-random-id="<?php echo $randomId; ?>" class="plg_system_webauthn-manage-delete btn btn-danger">
						<span class="icon-minus" aria-hidden="true"></span>
						<?php echo Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_DELETE_LABEL') ?>
					</button>
				</td>
			</tr>
		<?php endforeach; ?>
		<?php // phpcs:ignore
		if (empty($credentials)): ?>
			<tr>
				<td colspan="2">
					<?php echo Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_HEADER_NOMETHODS_LABEL') ?>
				</td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>

	<?php // phpcs:ignore
	if ($allow_add): ?>
		<p class="plg_system_webauthn-manage-add-container">
			<button
				type="button"
				id="plg_system_webauthn-manage-add"
				class="btn btn-success w-100"
				data-random-id="<?php echo $randomId; ?>">
				<span class="icon-plus" aria-hidden="true"></span>
				<?php echo Text::_('PLG_SYSTEM_WEBAUTHN_MANAGE_BTN_ADD_LABEL') ?>
			</button>
		</p>
	<?php endif; ?>
</div>
