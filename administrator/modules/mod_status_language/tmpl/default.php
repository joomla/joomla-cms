<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status_language
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
<div class="header-element-content d-flex">

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

</div>
