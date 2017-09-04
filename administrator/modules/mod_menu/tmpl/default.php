<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$siteName  = \Joomla\CMS\Factory::getApplication()->get('sitename');
$doc       = \Joomla\CMS\Factory::getDocument();
$direction = $doc->direction == 'rtl' ? 'float-right' : '';
$class     = $enabled ? 'nav navbar-nav nav-stacked main-nav clearfix ' . $direction : 'nav navbar-nav nav-stacked main-nav clearfix disabled ' . $direction;

// Recurse through children of root node if they exist
$menuTree = $menu->getTree();
$root     = $menuTree->reset();

JHtml::_('webcomponent', ['joomla-menu' => 'mod_menu_administrator/joomla-admin-menu.min.js'], ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => false]);
?>
<joomla-admin-menu>
	<div id="sidebar-wrapper" class="sidebar-wrapper">
		<div id="main-brand" class="main-brand align-items-center">
			<a href="<?php echo JRoute::_('index.php'); ?>" aria-label="<?php echo JText::_('TPL_BACK_TO_CONTROL_PANEL'); ?>">
				<img src="<?php echo JUri::root(); ?>media/system/images/logo.svg" class="logo" alt="<?php echo $siteName; ?>">
			</a>
		</div>
			<?php if ($root->hasChildren()) : ?>
				<div class="main-nav-container" role="navigation" aria-label="Main menu">
					<ul id="menu" class="<?php echo $class; ?>">

					<?php // WARNING: Do not use direct 'include' or 'require' as it is important to isolate the scope for each call
					$menu->renderSubmenu(JModuleHelper::getLayoutPath('mod_menu', 'default_submenu'));
					?>

					</ul>
				</div>

				<?php if ($css = $menuTree->getCss()) : ?>
					<?php $doc->addStyleDeclaration(implode("\n", $css)); ?>
				<?php endif; ?>
			<?php endif; ?>
	</div>
</joomla-admin-menu>
