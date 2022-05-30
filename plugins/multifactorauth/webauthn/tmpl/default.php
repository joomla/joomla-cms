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

//phpcs:ignorefile

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

// This method is only available on HTTPS
if (Uri::getInstance()->getScheme() !== 'https'): ?>
	<div id="multifactorauth-webauthn-nothttps" class="my-2">
		<div class="alert alert-danger">
			<h2 class="alert-heading">
				<span class="fa fa-times-circle" aria-hidden="true"></span>
				<?php echo Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_NOTHTTPS_HEAD'); ?>
			</h2>
			<p>
				<?php echo Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_NOTHTTPS_BODY'); ?>
			</p>
		</div>
	</div>
<?php
	return;
endif;

$this->app->getDocument()->getWebAssetManager()->useScript('plg_multifactorauth_webauthn.webauthn');

?>
<div id="multifactorauth-webauthn-missing" class="my-2">
	<div class="alert alert-danger">
		<h2 class="alert-heading">
			<span class="fa fa-times-circle" aria-hidden="true"></span>
			<?php echo Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_NOTAVAILABLE_HEAD'); ?>
		</h2>
		<p>
			<?php echo Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_NOTAVAILABLE_BODY'); ?>
		</p>
	</div>
</div>
