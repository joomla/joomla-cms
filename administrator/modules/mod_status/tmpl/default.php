<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Module\Multilangstatus\Administrator\Helper\MultilangstatusAdminHelper;

HTMLHelper::_('bootstrap.framework');

$hideLinks = $app->input->getBool('hidemainmenu');
?>
<div class="ml-auto">

	<ul class="nav text-center">
		<?php // Check if the multilangstatus module is present and enabled in the site ?>
		<?php if (class_exists(MultilangstatusAdminHelper::class)
			&& MultilangstatusAdminHelper::isEnabled()) : ?>
			<?php if (Multilanguage::isEnabled()) : ?>
				<?php // Publish and display the module ?>
				<?php MultilangstatusAdminHelper::publish(); ?>
				<?php $module = ModuleHelper::getModule('mod_multilangstatus'); ?>
				<?php echo ModuleHelper::renderModule($module); ?>
			<?php else : ?>
				<?php // Unpublish the module ?>
				<?php MultilangstatusAdminHelper::publish(); ?>
			<?php endif; ?>
		<?php endif; ?>

		<li class="nav-item footer-mobil-icon">
			<a class="nav-link link-title" href="<?php echo Uri::root(); ?>"
				title="<?php echo Text::sprintf('MOD_STATUS_PREVIEW', $sitename); ?>"
				target="_blank">
				<span class="fa fa-external-link-alt" aria-hidden="true"></span>
				<span class="sr-only"><?php echo HTMLHelper::_('string.truncate', $sitename, 28, false, false); ?></span>
				<?php echo $sitename ?>
			</a>
		</li>

		<li class="nav-item">
			<a class="nav-link <?php echo ($hideLinks ? 'disabled' : 'dropdown-toggle'); ?>" <?php echo ($hideLinks ? '' : 'href="' . Route::_('index.php?option=com_messages') . '"'); ?> title="<?php echo Text::_('MOD_STATUS_PRIVATE_MESSAGES'); ?>">
				<span class="fa fa-envelope" aria-hidden="true"></span>
				<span class="sr-only"><?php echo Text::_('MOD_STATUS_PRIVATE_MESSAGES'); ?></span>
				<?php $countUnread = $app->getSession()->get('messages.unread'); ?>
				<?php if ($countUnread > 0) : ?>
					<span class="badge badge-pill badge-danger"><?php echo $countUnread; ?></span>
				<?php endif; ?>
			</a>
		</li>

		<?php if ($user->authorise('core.manage', 'com_postinstall')) : ?>
			<li class="nav-item dropdown">
				<button class="nav-link dropdown-toggle <?php echo ($hideLinks ? 'disabled' : ''); ?>" data-toggle="dropdown" type="button"
					title="<?php echo Text::_('MOD_STATUS_POST_INSTALLATION_MESSAGES'); ?>">
					<span class="fa fa-bell-o" aria-hidden="true"></span>
					<span class="sr-only"><?php echo Text::_('MOD_STATUS_POST_INSTALLATION_MESSAGES'); ?></span>
					<span class="fa fa-angle-down" aria-hidden="true"></span>
					<?php if (count($messages) > 0) : ?>
						<span class="badge badge-pill badge-danger"><?php echo count($messages); ?></span>
					<?php endif; ?>
				</button>
				<?php if (!$hideLinks) : ?>
				<div class="dropdown-menu dropdown-menu-right dropdown-notifications border-0">
					<div class="dropdown-header">
						<?php echo Text::_('MOD_STATUS_POST_INSTALLATION_MESSAGES'); ?>
					</div>
					<?php if (empty($messages)) : ?>
						<div class="dropdown-item">
							<a href="<?php echo Route::_('index.php?option=com_postinstall&eid=' . $joomlaFilesExtensionId); ?>">
								<?php echo Text::_('COM_POSTINSTALL_LBL_NOMESSAGES_TITLE'); ?>
							</a>
						</div>
					<?php endif; ?>
					<?php foreach ($messages as $message) : ?>
						<div class="dropdown-item">
							<?php $route = 'index.php?option=com_postinstall&amp;eid=' . $joomlaFilesExtensionId; ?>
							<?php $title = Text::_($message->title_key); ?>
							<a href="<?php echo Route::_($route); ?>" title="<?php echo $title; ?>">
								<?php echo $title; ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</li>
		<?php endif; ?>

		<li class="nav-item dropdown header-profile footer-mobil-icon">
			<button class="nav-link dropdown-toggle <?php echo ($hideLinks ? 'disabled' : ''); ?>" data-toggle="dropdown" type="button"
				title="<?php echo Text::_('MOD_STATUS_USER_MENU'); ?>">
				<span class="fa fa-user-circle" aria-hidden="true"></span>
				<span class="sr-only"><?php echo Text::_('MOD_STATUS_USER_MENU'); ?></span>
				<span class="fa fa-angle-down" aria-hidden="true"></span>
			</button>
			<div class="dropdown-menu dropdown-menu-right icons-left">
				<div class="dropdown-header"><?php echo $user->name; ?></div>
				<?php $uri   = Uri::getInstance(); ?>
				<?php $route = 'index.php?option=com_users&task=user.edit&id=' . $user->id . '&return=' . base64_encode($uri); ?>
				<div class="dropdown-item">
					<a href="<?php echo Route::_($route); ?>">
						<span class="fa fa-user-o"></span>
						<?php echo Text::_('MOD_STATUS_EDIT_ACCOUNT'); ?>
					</a>
				</div>
				<div class="dropdown-item">
					<?php // TODO: route to accessibility settings ?>
					<a href="#">
						<span class="fa fa-universal-access"></span>
						<?php echo Text::_('MOD_STATUS_ACCESSIBILITY_SETTINGS'); ?>
					</a>
				</div>
				<div class="dropdown-item">
					<?php $route = 'index.php?option=com_login&task=logout&amp;' . Session::getFormToken() . '=1'; ?>
					<a href="<?php echo Route::_($route); ?>">
						<span class="fa fa-power-off"></span>
						<?php echo Text::_('JLOGOUT'); ?>
					</a>
				</div>
			</div>
		</li>
	</ul>
</div>
