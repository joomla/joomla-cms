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

?>
<div id="twofactorauth-webauthn-missing" class="my-2">
	<div class="alert alert-danger">
		<h4>
			<?php echo Text::_('PLG_TWOFACTORAUTH_WEBAUTHN_ERR_NOTAVAILABLE_HEAD'); ?>
		</h4>
		<p>
			<?php echo Text::_('PLG_TWOFACTORAUTH_WEBAUTHN_ERR_NOTAVAILABLE_BODY'); ?>
		</p>
	</div>
</div>
