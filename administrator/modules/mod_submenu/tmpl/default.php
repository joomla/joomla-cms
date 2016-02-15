<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_submenu
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div id="sidebar">
	<div class="sidebar-nav">
		<?php if ($displayMenu) : ?>
		<ul id="submenu" class="nav nav-list">
			<?php foreach ($list as $item) : ?>
			<?php if (isset ($item[2]) && $item[2] == 1) : ?>
				<li class="active">
			<?php else : ?>
				<li>
			<?php endif; ?>
			<?php if ($hide) : ?>
				<a class="nolink"><?php echo $item[0]; ?></a>
			<?php else : ?>
				<?php if (strlen($item[1])) : ?>
					<a href="<?php echo JFilterOutput::ampReplace($item[1]); ?>"><?php echo $item[0]; ?></a>
				<?php else : ?>
					<?php echo $item[0]; ?>
				<?php endif; ?>
			<?php endif; ?>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
		<?php if ($displayMenu && $displayFilters) : ?>
		<hr />
		<?php endif; ?>
		<?php if ($displayFilters) : ?>
		<div class="filter-select hidden-phone">
			<h4 class="page-header"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></h4>
			<form action="<?php echo JRoute::_($action); ?>" method="post">
				<?php foreach ($filters as $filter) : ?>
					<label for="<?php echo $filter['name']; ?>" class="element-invisible"><?php echo $filter['label']; ?></label>
					<select name="<?php echo $filter['name']; ?>" id="<?php echo $filter['name']; ?>" class="span12 small" onchange="this.form.submit()">
						<?php if (!$filter['noDefault']) : ?>
							<option value=""><?php echo $filter['label']; ?></option>
						<?php endif; ?>
						<?php echo $filter['options']; ?>
					</select>
					<hr class="hr-condensed" />
				<?php endforeach; ?>
			</form>
		</div>
		<?php endif; ?>
	</div>
</div>
