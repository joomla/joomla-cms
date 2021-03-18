<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_user
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

// Load Bootstrap JS for dropdowns.
HTMLHelper::_('bootstrap.framework');

$hideLinks = $app->input->getBool('hidemainmenu');
?>
<div class="header-item-content dropdown header-profile d-flex">
	<button class="dropdown-toggle d-flex flex-column align-items-stretch <?php echo ($hideLinks ? 'disabled' : ''); ?>" data-toggle="dropdown" type="button" <?php echo ($hideLinks ? 'disabled' : ''); ?>
		title="<?php echo Text::_('MOD_USER_MENU'); ?>">
		<div class="d-flex align-items-end mx-auto">
			<span class="fas fa-user-circle" aria-hidden="true"></span>
		</div>
		<div class="tiny">
			<?php echo Text::_('MOD_USER_MENU'); ?>
		</div>
		<span class="fas fa-angle-down" aria-hidden="true"></span>
	</button>
	<div class="dropdown-menu dropdown-menu-right">
		<div class="dropdown-header">
			<span class="fas fa-fw fa-user-circle" aria-hidden="true"></span>
			<?php echo Text::sprintf('MOD_USER_TITLE', $user->name); ?>
		</div>
		<?php $uri   = Uri::getInstance(); ?>
		<?php $route = 'index.php?option=com_users&task=user.edit&id=' . $user->id . '&return=' . base64_encode($uri) . '#details'; ?>
		<a class="dropdown-item" href="<?php echo Route::_($route); ?>">
			<span class="fas fa-fw fa-user" aria-hidden="true"></span>
			<?php echo Text::_('MOD_USER_EDIT_ACCOUNT'); ?>
		</a>
		<?php $route = 'index.php?option=com_users&task=user.edit&id=' . $user->id . '&return=' . base64_encode($uri) . '#attrib-accessibility'; ?>
		<a class="dropdown-item" href="<?php echo Route::_($route); ?>">
			<span class="fas fa-fw fa-universal-access" aria-hidden="true"></span>
			<?php echo Text::_('MOD_USER_ACCESSIBILITY_SETTINGS'); ?>
		</a>
		<?php $route = 'index.php?option=com_login&task=logout&amp;' . Session::getFormToken() . '=1'; ?>
		<a class="dropdown-item" href="<?php echo Route::_($route); ?>">
			<span class="fas fa-fw fa-power-off" aria-hidden="true"></span>
			<?php echo Text::_('JLOGOUT'); ?>
		</a>
	</div>
</div>
