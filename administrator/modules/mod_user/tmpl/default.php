<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_user
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$hideLinks = $app->input->getBool('hidemainmenu');
?>

<div class="header-item-content dropdown header-profile d-flex">
	<button class="dropdown-toggle d-flex flex-column align-items-stretch <?php echo ($hideLinks ? 'disabled' : ''); ?>" data-toggle="dropdown" type="button" <?php echo ($hideLinks ? 'disabled' : ''); ?>
		title="<?php echo Text::_('MOD_USER_MENU'); ?>">
		<div class="d-flex align-items-end mx-auto">
			<span class="fa fa-user-circle" aria-hidden="true"></span>
		</div>
		<div class="tiny">
			<?php echo Text::_('MOD_USER_MENU'); ?>
		</div>
		<span class="fa fa-angle-down" aria-hidden="true"></span>
	</button>
	<div class="dropdown-menu dropdown-menu-right icons-left">
		<div class="dropdown-header"><?php echo $user->name; ?></div>
		<?php $uri   = Uri::getInstance(); ?>
		<?php $route = 'index.php?option=com_users&task=user.edit&id=' . $user->id . '&return=' . base64_encode($uri); ?>
		<div class="dropdown-item">
			<a href="<?php echo Route::_($route); ?>">
				<span class="fa fa-user"></span>
				<?php echo Text::_('MOD_USER_EDIT_ACCOUNT'); ?>
			</a>
		</div>
		<div class="dropdown-item">
			<?php $route = 'index.php?option=com_users&task=user.edit&id=' . $user->id . '&return=' . base64_encode($uri); ?>
			<a href="<?php echo Route::_($route); ?>">
				<span class="fa fa-universal-access"></span>
				<?php echo Text::_('MOD_USER_ACCESSIBILITY_SETTINGS'); ?>
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
</div>
