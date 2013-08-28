<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$form = $displayData->get('form');

$title = $form->getField('title') ? 'title' : ($form->getField('name') ? 'name' : '');

?>
<div class="form-inline form-inline-header">
	<?php if ($title) : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $form->getLabel($title); ?>
			</div>
			<div class="controls">
				<?php echo $form->getInput($title); ?>
			</div>
		</div>
	<?php endif; ?>
	<?php if ('alias') : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $form->getLabel('alias'); ?>
			</div>
			<div class="controls">
				<?php echo $form->getInput('alias'); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
