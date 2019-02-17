<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_submenu
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var  \Joomla\CMS\Menu\MenuItem  $root */
?>

<div class="com-cpanel-system">
	<?php foreach ($root->getChildren() as $child) : ?>
        <?php if ($child->hasChildren()) : ?>
		<div class="com-cpanel-system__category">
			<h2 class="com-cpanel-system__header">
				<span class="fa fa-<?php echo $child->icon; ?>" aria-hidden="true"></span>
				<?php echo Text::_($child->title); ?>
			</h2>
			<ul class="list-group list-group-flush">
				<?php foreach ($child->getChildren() as $item) : ?>
					<li class="list-group-item">
						<a href="<?php echo $item->link; ?>"><?php echo Text::_($item->title); ?>
							<?php if (false && !empty($item->getBadge())) : ?>
								<span class="pull-right badge badge-pill badge-warning">
							<?php echo '&#x200E;' . Text::_($item->getBadge()); ?>
						</span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
        <?php endif; ?>
	<?php endforeach; ?>
</div>
