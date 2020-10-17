<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_post_installation_messages
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$hideLinks = $app->input->getBool('hidemainmenu');
?>
<?php if ($app->getIdentity()->authorise('core.manage', 'com_postinstall')) : ?>
	<div class="header-item-content">
		<a class="d-flex flex-column <?php echo ($hideLinks ? 'disabled' : ''); ?>" 
			href="<?php echo Route::_('index.php?option=com_postinstall&eid=' . $joomlaFilesExtensionId); ?>" title="<?php echo Text::_('MOD_POST_INSTALLATION_MESSAGES'); ?>">
			<div class="d-flex align-items-end mx-auto">
				<span class="fas fa-bell" aria-hidden="true"></span>
				<?php if (count($messages) > 0) : ?>
					<span class="badge badge-danger"><?php echo count($messages); ?></span>
				<?php endif; ?>
			</div>
			<div class="tiny">
				<?php echo Text::_('MOD_POST_INSTALLATION_MESSAGES'); ?>
			</div>
		</a>
	</div>
<?php endif; ?>
