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

?>
<div id="twofactorauth-webauthn-controls" class="my-2">
	<input id="twofactorauth-method-code" name="code" value="" placeholder="" type="hidden">

	<a id="plg_twofactorauth_webauthn_register_button"
		class="btn btn-primary btn-lg btn-big loginguard-button-primary-large"
	>
		<span class="icon icon-lock" aria-hidden="true"></span>
		<?php echo Text::_('PLG_TWOFACTORAUTH_WEBAUTHN_LBL_REGISTERKEY'); ?>
	</a>
</div>
