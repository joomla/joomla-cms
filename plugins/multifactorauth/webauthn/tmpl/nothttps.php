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

?>
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
