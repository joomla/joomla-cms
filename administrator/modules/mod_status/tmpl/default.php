<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('bootstrap.framework');

$hideLinks = $app->input->getBool('hidemainmenu');
?>
<div class="ml-auto">
	<ul class="nav text-center">
		<?php echo $multilanguageStatusModuleOutput; ?>
		<li class="nav-item">
			<a class="nav-link" href="<?php echo Uri::root(); ?>" title="<?php echo Text::sprintf('MOD_STATUS_PREVIEW', $sitename); ?>" target="_blank">
				<span class="fa fa-external-link-alt" aria-hidden="true"></span>
				<span class="sr-only"><?php echo HTMLHelper::_('string.truncate', $sitename, 28, false, false); ?></span>
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
			<a class="nav-link <?php echo ($hideLinks ? 'disabled' : 'dropdown-toggle'); ?>" <?php echo ($hideLinks ? '' : 'data-toggle="dropdown" href="#"'); ?> title="<?php echo Text::_('MOD_STATUS_POST_INSTALLATION_MESSAGES'); ?>">
				<span class="fa fa-bell" aria-hidden="true"></span>
				<span class="sr-only"><?php echo Text::_('MOD_STATUS_POST_INSTALLATION_MESSAGES'); ?></span>
				<?php if (count($messages) > 0) : ?>
					<span class="badge badge-pill badge-danger"><?php echo count($messages); ?></span>
				<?php endif; ?>
			</a>
			<?php if (!$hideLinks) : ?>
				<div class="dropdown-menu dropdown-menu-right dropdown-notifications border-0">
					<div class="list-group">
						<?php if (empty($messages)) : ?>
						<p class="list-group-item text-center">
							<strong><?php echo Text::_('COM_POSTINSTALL_LBL_NOMESSAGES_TITLE'); ?></strong>
						</p>
						<?php endif; ?>
						<?php foreach ($messages as $message) : ?>
						<a href="<?php echo Route::_('index.php?option=com_postinstall&amp;eid=' . $joomlaFilesExtensionId); ?>" class="list-group-item list-group-item-action">
							<h5 class="list-group-item-heading"><?php echo HTMLHelper::_('string.truncate', Text::_($message->title_key), 28, false, false); ?></h5>
							<p class="list-group-item-text small">
								<?php echo HTMLHelper::_('string.truncate', Text::_($message->description_key), 120, false, false); ?>
							</p>
						</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</li>
		<?php endif; ?>

		<li class="nav-item dropdown header-profile">
			<a class="nav-link <?php echo ($hideLinks ? 'disabled' : 'dropdown-toggle'); ?>" <?php echo ($hideLinks ? '' : 'data-toggle="dropdown" href="#"'); ?> title="<?php echo Text::_('MOD_STATUS_USER_MENU'); ?>">
				<span class="fa fa-user" aria-hidden="true"></span>
				<span class="sr-only"><?php echo Text::_('MOD_STATUS_USER_MENU'); ?></span>
			</a>
			<?php if (!$hideLinks) : ?>
				<div class="dropdown-menu dropdown-menu-right">
					<div class="dropdown-header">
						<span class="fa fa-user" aria-hidden="true"></span>
						<?php echo $user->name; ?>
					</div>
					<?php $uri   = Uri::getInstance(); ?>
					<?php $route = 'index.php?option=com_users&task=user.edit&id=' . $user->id . '&return=' . base64_encode($uri); ?>
					<a class="dropdown-item" href="<?php echo Route::_($route); ?>">
						<?php echo Text::_('MOD_STATUS_EDIT_ACCOUNT'); ?></a>
					<a class="dropdown-item" href="<?php echo Route::_('index.php?option=com_login&task=logout&'
						. Session::getFormToken() . '=1'); ?>"><?php echo Text::_('JLOGOUT'); ?></a>
				</div>
			<?php endif; ?>
		</li>

	</ul>
</div>
