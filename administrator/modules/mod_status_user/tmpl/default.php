<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status_user
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

		<div class="header-element-content dropdown header-profile footer-mobil-icon d-flex">
			<button class="dropdown-toggle d-flex flex-column align-items-stretch <?php echo ($hideLinks ? 'disabled' : ''); ?>" data-toggle="dropdown" type="button"
				title="<?php echo Text::_('MOD_STATUS_USER_MENU'); ?>">
                <div class="d-flex align-items-end mx-auto">
                    <span class="fa fa-user-circle" aria-hidden="true"></span>
                </div>
                <div class="d-flex align-items-center tiny">
				   <?php echo Text::_('MOD_STATUS_USER_MENU'); ?>
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
						<?php echo Text::_('MOD_STATUS_USER_EDIT_ACCOUNT'); ?>
					</a>
				</div>
				<div class="dropdown-item">
					<?php // TODO: route to accessibility settings ?>
					<a href="#">
						<span class="fa fa-universal-access"></span>
						<?php echo Text::_('MOD_STATUS_USER_ACCESSIBILITY_SETTINGS'); ?>
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
