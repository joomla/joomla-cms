<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// JLayout for standard handling of the details sidebar in administrator edit screens.

?>
<div class="span2">
<h4><?php echo JText::_('JDETAILS');?></h4>
			<hr />
			<fieldset class="form-vertical">
				<?php if (!$displayData->get('form')->getValue('title')) : ?>
					<div class="control-group">
						<div class="controls">
							<?php echo $displayData->get('form')->getValue('name'); ?>
						</div>
					</div>
				<?php endif; ?>
				<?php if ($displayData->get('form')->getValue('title')) : ?>
				<div class="control-group">
					<div class="controls">
						<?php echo $displayData->get('form')->getValue('title'); ?>
					</div>
				</div>
				<?php endif; ?>

				<?php if ($displayData->get('form')->getValue('published')) : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $displayData->get('form')->getLabel('published'); ?>
						</div>
						<div class="controls">
							<?php echo $displayData->get('form')->getInput('published'); ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if (!$displayData->get('form')->getValue('published')) : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $displayData->get('form')->getLabel('state'); ?>
						</div>
						<div class="controls">
							<?php echo $displayData->get('form')->getInput('state'); ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="control-group">
					<div class="control-label">
						<?php echo $displayData->get('form')->getLabel('access'); ?>
					</div>
					<div class="controls">
						<?php echo $displayData->get('form')->getInput('access'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $displayData->get('form')->getLabel('featured'); ?>
					</div>
					<div class="controls">
						<?php echo $displayData->get('form')->getInput('featured'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $displayData->get('form')->getLabel('language'); ?>
					</div>
					<div class="controls">
						<?php echo $displayData->get('form')->getInput('language'); ?>
					</div>
				</div>
				<div class="control-group">
					<?php foreach ($displayData->get('form')->getFieldset('jmetadata') as $field) : ?>
						<?php if ($field->name == 'jform[metadata][tags][]') :?>
						<div class="control-group">
							<div class="control-label"><?php echo $field->label; ?></div>
							<div class="controls"><?php echo $field->input; ?></div>
						</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>

			</fieldset>
		</div>

