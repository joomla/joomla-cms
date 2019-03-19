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
$bootstrapSize  = (int) $params->get('bootstrap_size', 6);
$columns = (int) ($bootstrapSize ? $bootstrapSize : 3) / 3;
$columnSize = 12 / $columns;

/** @var  \Joomla\CMS\Menu\MenuItem  $root */
?>
<div class="col-md-<?php echo $bootstrapSize; ?> row">
	<?php if (Factory::getUser()->authorise('core.edit', 'com_modules')) : ?>
        <div class="module-actions">
            <a href="<?php echo 'index.php?option=com_modules&task=module.edit&id=' . (int) $module->id; ?>">
                <span class="fa fa-cog"><span class="sr-only"><?php echo Text::_('JACTION_EDIT') . ' ' . $module->title; ?></span></span></a>
        </div>
	<?php endif; ?>
	<?php foreach ($root->getChildren() as $child) : ?>
		<?php if ($child->hasChildren()) : ?>
			<div class="card mb-3 col-md-<?php echo $columnSize; ?>">
				<h2 class="card-header">
					<?php if ($child->icon) : ?><span class="fa fa-<?php echo $child->icon; ?>" aria-hidden="true"></span><?php endif; ?>
					<?php echo Text::_($child->title); ?>
				</h2>
				<ul class="list-group list-group-flush">
					<?php foreach ($child->getChildren() as $item) : ?>
						<li class="list-group-item">
							<a href="<?php echo $item->link; ?>"><?php echo Text::_($item->title); ?>
								<?php if ($item->ajaxbadge) : ?>
									<span class="fa fa-spin fa-spinner pull-right mt-1 system-counter" data-url="<?php echo $item->ajaxbadge; ?>"></span>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
        	</div>
    	<?php endif; ?>
	<?php endforeach; ?>
</div>
