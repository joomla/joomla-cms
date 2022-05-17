<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Prevent direct access
defined('_JEXEC') || die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

include PluginHelper::getLayoutPath('twofactorauth', 'webauthn', 'error');

$this->app->getDocument()->getWebAssetManager()->useScript('plg_twofactorauth_webauthn.webauthn');

?>
<div id="twofactorauth-webauthn-controls" style="margin: 0.5em 0">
	<input name="code" value="" id="twofactorauthCode" class="form-control input-lg" type="hidden">

	<a id="plg_twofactorauth_webauthn_validate_button"
	   class="btn btn-primary btn-lg btn-big"
	>
		<span class="icon icon-lock" aria-hidden="true"></span>
		<?php echo Text::_('PLG_TWOFACTORAUTH_WEBAUTHN_LBL_VALIDATEKEY'); ?>
	</a>
</div>
