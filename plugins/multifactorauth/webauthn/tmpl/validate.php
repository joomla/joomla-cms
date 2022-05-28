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
use Joomla\CMS\Uri\Uri;

// This method is only available on HTTPS
if (Uri::getInstance()->getScheme() !== 'https')
{
	include PluginHelper::getLayoutPath('multifactorauth', 'webauthn', 'nothttps');

	return;
}

include PluginHelper::getLayoutPath('multifactorauth', 'webauthn', 'error');

$this->app->getDocument()->getWebAssetManager()->useScript('plg_multifactorauth_webauthn.webauthn');

?>
<div id="multifactorauth-webauthn-controls" style="margin: 0.5em 0">
	<input name="code" value="" id="multifactorauthCode" class="form-control input-lg" type="hidden">

	<a id="plg_multifactorauth_webauthn_validate_button"
	   class="btn btn-primary btn-lg"
	>
		<span class="icon icon-lock" aria-hidden="true"></span>
		<?php echo Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_LBL_VALIDATEKEY'); ?>
	</a>
</div>
