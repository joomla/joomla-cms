<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_submenu
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var  \Joomla\CMS\Menu\MenuItem  $root */
?>
<?php foreach ($root->getChildren() as $child) : ?>
	<?php if ($child->hasChildren()) : ?>
		<div class="module-wrapper">
			<div class="card">
				<?php
				if (substr($child->img, 0, 6) === 'class:')
					{
						$iconImage = '<span class="fas fa-' . substr($child->img, 6) . '" aria-hidden="true"></span>';
					}
					elseif (substr($child->img, 0, 6) === 'image:')
					{
						$iconImage = '<img src="' . substr($child->img, 6) . '" aria-hidden="true">';
					}
					elseif (!empty($child->img))
					{
						$iconImage = '<img src="' . $child->img . '" aria-hidden="true">';
					}
					elseif ($child->icon)
					{
						$iconImage = '<span class="fas fa-' . $child->icon . '" aria-hidden="true"></span>';
					}
					else
					{
						$iconImage = '';
					}
				?>
				<h2 class="card-header">
					<?php echo $iconImage; ?>
					<?php echo Text::_($child->title); ?>
				</h2>
				<ul class="list-group list-group-flush">
					<?php foreach ($child->getChildren() as $item) : ?>
						<li class="list-group-item d-flex align-items-center">
							<?php $params = $item->getParams(); ?>
							<?php // Only if Menu-show = true
								if ($params->get('menu_show', 1)) : ?>
								<a class="flex-grow-1" href="<?php echo $item->link; ?>"
									<?php echo $item->target === '_blank' ? ' title="' . Text::sprintf('JBROWSERTARGET_NEW_TITLE', Text::_($item->title)) . '"' : ''; ?>
									<?php echo $item->target ? ' target="' . $item->target . '"' : ''; ?>>
									<?php if (!empty($params->get('menu_image'))) : ?>
										<?php
										$image = htmlspecialchars($params->get('menu_image'), ENT_QUOTES, 'UTF-8');
										$class = htmlspecialchars($params->get('menu_image_css'), ENT_QUOTES, 'UTF-8');
										$alt   = $params->get('menu_text') ? '' : htmlspecialchars(Text::_($item->title), ENT_QUOTES, 'UTF-8');
										?>
										<?php echo HTMLHelper::_('image', $image, $alt, 'class="' . $class . '"'); ?>
									<?php endif; ?>
									<?php echo ($params->get('menu_text', 1)) ? htmlspecialchars(Text::_($item->title), ENT_QUOTES, 'UTF-8') . $item->iconImage : ''; ?>
									<?php if ($item->ajaxbadge) : ?>
										<span class="menu-badge">
											<span class="fas fa-spin fa-spinner mt-1 system-counter float-right" data-url="<?php echo $item->ajaxbadge; ?>"></span>
										</span>
									<?php endif; ?>
								</a>
								<?php if ($params->get('menu-quicktask')) : ?>
									<?php $permission = $params->get('menu-quicktask-permission'); ?>
									<?php $scope = $item->scope !== 'default' ? $item->scope : null; ?>
									<?php if (!$permission || $user->authorise($permission, $scope)) : ?>
										<span class="menu-quicktask">
											<?php
											$link = $params->get('menu-quicktask');
											$icon = $params->get('menu-quicktask-icon', 'plus');

											$title = Text::_($params->get('menu-quicktask-title'));

											if (empty($params->get('menu-quicktask-title')))
											{
												$title = Text::_('MOD_MENU_QUICKTASK_NEW');
											}

											$sronly = Text::_($item->title) . ' - ' . $title;
											?>
											<a href="<?php echo $link; ?>">
												<span class="fas fa-<?php echo $icon; ?> fa-xs" title="<?php echo htmlentities($title); ?>" aria-hidden="true"></span>
												<span class="sr-only"><?php echo htmlentities($sronly); ?></span>
											</a>
										</span>
									<?php endif; ?>
								<?php endif; ?>
								<?php if ($item->dashboard) : ?>
									<span class="menu-dashboard">
										<a href="<?php echo Route::_('index.php?option=com_cpanel&view=cpanel&dashboard=' . $item->dashboard); ?>">
											<span class="fas fa-th-large" title="<?php echo htmlentities(Text::_('MOD_MENU_DASHBOARD_LINK')); ?>"></span>
										</a>
									</span>
								<?php endif; ?>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>
<?php endforeach; ?>
