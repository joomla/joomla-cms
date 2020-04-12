<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_post_installation_messages
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Load Bootstrap JS for dropdowns.
HTMLHelper::_('bootstrap.framework');

$hideLinks = $app->input->getBool('hidemainmenu');
?>
<?php if ($app->getIdentity()->authorise('core.manage', 'com_postinstall')) : ?>
	<div class="header-item-content dropdown d-flex">
		<button class="dropdown-toggle d-flex flex-column align-items-stretch <?php echo ($hideLinks ? 'disabled' : ''); ?>" data-toggle="dropdown" type="button" <?php echo ($hideLinks ? 'disabled' : ''); ?>
			title="<?php echo Text::_('MOD_POST_INSTALLATION_MESSAGES'); ?>">
			<div class="d-flex align-items-end mx-auto">
				<span class="fas fa-bell" aria-hidden="true"></span>
			</div>
			<div class="tiny">
				<?php echo Text::_('MOD_POST_INSTALLATION_MESSAGES'); ?>
			</div>
			<span class="fas fa-angle-down" aria-hidden="true"></span>
			<?php if (count($messages) > 0) : ?>
				<span class="badge badge-danger"><?php echo count($messages); ?></span>
			<?php endif; ?>
		</button>
		<?php if (!$hideLinks) : ?>
		<div class="dropdown-menu dropdown-menu-right dropdown-notifications border-0">
			<div class="dropdown-header">
				<?php echo Text::_('MOD_POST_INSTALLATION_MESSAGES'); ?>
			</div>
			<?php if (empty($messages)) : ?>
				<a class="dropdown-item" href="<?php echo Route::_('index.php?option=com_postinstall&eid=' . $joomlaFilesExtensionId); ?>">
					<?php echo Text::_('COM_POSTINSTALL_LBL_NOMESSAGES_TITLE'); ?>
				</a>
			<?php endif; ?>
			<?php foreach ($messages as $message) : ?>
				<?php $route = 'index.php?option=com_postinstall&amp;eid=' . $joomlaFilesExtensionId; ?>
				<a class="dropdown-item" href="<?php echo Route::_($route); ?>">
					<?php echo Text::_($message->title_key); ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
<?php endif; ?>
