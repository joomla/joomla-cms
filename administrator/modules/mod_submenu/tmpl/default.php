<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_submenu
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('script', 'com_cpanel/admin-system-loader.js', ['version' => 'auto', 'relative' => true]);
$bootstrapSize = (int) $params->get('bootstrap_size', 6);
$columns = (int) ($bootstrapSize ? $bootstrapSize : 3) / 3;
$columnSize = 12 / $columns;
$columnsSmall = (int) ($bootstrapSize ? $bootstrapSize : 4) / 4;
$columnSizeSmall = 12 / $columnsSmall;
$app = JFactory::getApplication();
$user = $app->getIdentity();

/** @var  \Joomla\CMS\Menu\MenuItem  $root */
?>
<div class="col-md-<?php echo $bootstrapSize; ?>">
	<?php if (Factory::getUser()->authorise('core.edit', 'com_modules')) : ?>
	<div class="module-actions">
		<a href="<?php echo 'index.php?option=com_modules&task=module.edit&id=' . (int) $module->id; ?>">
			<span class="fa fa-edit"><span class="sr-only"><?php echo Text::_('JACTION_EDIT') . ' ' . $module->title; ?></span></span>
		</a>
	</div>
	<?php endif; ?>
	<div class="row">
	<?php foreach ($root->getChildren() as $child) : ?>
		<?php if ($child->hasChildren()) : ?>
			<div class="card mb-3 col-lg-<?php echo $columnSize; ?> col-md-<?php echo $columnSizeSmall; ?>">
				<h2 class="card-header">
					<?php if ($child->icon) : ?><span class="fa fa-<?php echo $child->icon; ?>" aria-hidden="true"></span><?php endif; ?>
					<?php echo Text::_($child->title); ?>
				</h2>
				<ul class="list-group list-group-flush">
					<?php foreach ($child->getChildren() as $item) : ?>
						<li class="list-group-item d-flex">
							<?php $params = $item->getParams(); ?>
							<?php // Only if Menu-show = true
								if ($params->get('menu_show', 1)) : ?>
								<?php
								if (!empty($params->get('menu_image'))) :
									$image = htmlspecialchars($params->get('menu_image'), ENT_QUOTES, 'UTF-8');
									$class = htmlspecialchars($params->get('menu_image_css'), ENT_QUOTES, 'UTF-8');
									$alt = $params->get('menu_text') ? '' : htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8');
								endif;
								?>
								<a class="flex-grow-1" href="<?php echo $item->link; ?>">
									<?php if (!empty($params->get('menu_image'))) : ?>
										<?php echo HTMLHelper::_('image', $image, $alt, 'class="' . $class . '"'); ?>
									<?php endif; ?>
									<?php echo ($params->get('menu_text', 1)) ? Text::_($item->title) : ''; ?>
									<?php if ($params->get('menu-quicktask', false)) : ?>
										<span class="menu-quicktask">
											<?php
											$link = $params->get('menu-quicktask-link');
											$icon = $params->get('menu-quicktask-icon', 'plus');
											$title = $params->get('menu-quicktask-title', 'MOD_MENU_QUICKTASK_NEW');
											$permission = $params->get('menu-quicktask-permission');
											$scope = $item->scope !== 'default' ? $item->scope : null;
											?>
											<?php if (!$permission || $user->authorise($permission, $scope)) : ?>
												<a href="<?php echo $link; ?>">
													<span class="fa fa-<?php echo $icon; ?>" title="<?php echo htmlentities(Text::_($title)); ?>" aria-hidden="true"></span>
													<span class="sr-only"><?php echo Text::_($title); ?></span>
												</a>
											<?php endif; ?>
										</span>
									<?php endif; ?>
									<?php if ($item->ajaxbadge) : ?>
										<span class="menu-badge">
											<span class="fa fa-spin fa-spinner mt-1 system-counter" data-url="<?php echo $item->ajaxbadge; ?>"></span>
										</span>
									<?php endif; ?>
								</a>
								<?php if ($item->dashboard) : ?>
									<span class="menu-dashboard">
										<a href="<?php echo JRoute::_('index.php?option=com_cpanel&view=cpanel&dashboard=' . $item->dashboard); ?>">
											<span class="fa fa-th-large" title="<?php echo htmlentities(Text::_('MOD_MENU_DASHBOARD_LINK')); ?>"></span>
										</a>
									</span>
								<?php endif; ?>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
	</div>
</div>
