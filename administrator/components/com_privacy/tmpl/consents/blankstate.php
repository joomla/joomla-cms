<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div class="px-4 py-5 my-5 text-center">
	<span class="fa-8x icon-lock mb-4" aria-hidden="true"></span>
	<h1 class="display-5 fw-bold"><?php echo Text::_('COM_PRIVACY_CONSENTS_BLANKSTATE_TITLE'); ?></h1>
	<div class="col-lg-6 mx-auto">
		<p class="lead mb-4">
			<?php echo Text::_('COM_PRIVACY_CONSENTS_BLANKSTATE_CONSENTS'); ?>
		</p>
		<div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
			<a href="https://docs.joomla.org/Special:MyLanguage/Help40:Privacy:_Consents" class="btn btn-outline-secondary btn-lg px-4"><?php echo Text::_('COM_PRIVACY_BLANKSTATE_BUTTON_LEARNMORE'); ?></a>
		</div>
	</div>
</div>
