<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_user
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

// Load the Bootstrap Dropdown
HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle');

$hideLinks = $app->input->getBool('hidemainmenu');
?>
<div class="header-item-content dropdown header-profile d-flex">
	<button class="dropdown-toggle d-flex flex-column align-items-stretch <?php echo ($hideLinks ? 'disabled' : ''); ?>"  data-bs-toggle="dropdown" type="button" <?php echo ($hideLinks ? 'disabled' : ''); ?>
		title="<?php echo Text::_('MOD_USER_MENU'); ?>">
		<div class="d-flex align-items-end mx-auto">
			<span class="icon-user-circle" aria-hidden="true"></span>
		</div>
		<div class="tiny">
			<?php echo Text::_('MOD_USER_MENU'); ?>
		</div>
		<span class="icon-angle-down" aria-hidden="true"></span>
	</button>
	<div class="dropdown-menu dropdown-menu-end">
		<div class="dropdown-header">
			<span class="icon-user-circle icon-fw" aria-hidden="true"></span>
			<?php echo Text::sprintf('MOD_USER_TITLE', $user->name); ?>
		</div>
		<?php $uri   = Uri::getInstance(); ?>
		<?php $route = 'index.php?option=com_admin&task=profile.edit&id=' . $user->id . '&return=' . base64_encode($uri) . '#attrib-user_details'; ?>
		<a class="dropdown-item" href="<?php echo Route::_($route); ?>">
			<span class="icon-user icon-fw" aria-hidden="true"></span>
			<?php echo Text::_('MOD_USER_EDIT_ACCOUNT'); ?>
		</a>
		<?php $route = 'index.php?option=com_admin&task=profile.edit&id=' . $user->id . '&return=' . base64_encode($uri) . '#attrib-accessibility'; ?>
		<a class="dropdown-item" href="<?php echo Route::_($route); ?>">
			<span class="icon-universal-access icon-fw" aria-hidden="true"></span>
			<?php echo Text::_('MOD_USER_ACCESSIBILITY_SETTINGS'); ?>
		</a>
		<?php $route = 'index.php?option=com_login&task=logout&amp;' . Session::getFormToken() . '=1'; ?>
		<a class="dropdown-item" href="<?php echo Route::_($route); ?>">
			<span class="icon-power-off icon-fw" aria-hidden="true"></span>
			<?php echo Text::_('JLOGOUT'); ?>
		</a>
	</div>
</div>
