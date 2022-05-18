<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Multifactorauth.webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Prevent direct access
defined('_JEXEC') || die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

include PluginHelper::getLayoutPath('multifactorauth', 'webauthn', 'error');

$this->app->getDocument()->getWebAssetManager()->useScript('plg_multifactorauth_webauthn.webauthn');

?>
<div id="multifactorauth-webauthn-controls" class="my-2">
	<input id="multifactorauth-method-code" name="code" value="" placeholder="" type="hidden">

	<a id="plg_multifactorauth_webauthn_register_button"
		class="btn btn-primary btn-lg btn-big"
	>
		<span class="icon icon-lock" aria-hidden="true"></span>
		<?php echo Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_LBL_REGISTERKEY'); ?>
	</a>
</div>
