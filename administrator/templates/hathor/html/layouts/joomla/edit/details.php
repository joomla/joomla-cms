<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// JLayout for standard handling of the details sidebar in administrator edit screens.
$title = $displayData->get('form')->getValue('title');
$published = $displayData->get('form')->getValue('published');
$saveHistory = $displayData->get('state')->get('params')->get('save_history', 0);
?>
<div class="span2">
<h4><?php echo JText::_('JDETAILS');?></h4>
			<hr />
			<fieldset class="form-vertical">
				<?php if (empty($title)) : ?>
					<div class="control-group">
						<div class="controls">
							<?php echo $displayData->get('form')->getValue('name'); ?>
						</div>
					</div>
				<?php else : ?>
				<div class="control-group">
					<div class="controls">
						<?php echo $displayData->get('form')->getValue('title'); ?>
					</div>
				</div>
				<?php endif; ?>

				<?php if ($published) : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $displayData->get('form')->getLabel('published'); ?>
						</div>
						<div class="controls">
							<?php echo $displayData->get('form')->getInput('published'); ?>
						</div>
					</div>
				<?php else : ?>
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
				<?php if (JLanguageMultilang::isEnabled()) : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $displayData->get('form')->getLabel('language'); ?>
						</div>
						<div class="controls">
							<?php echo $displayData->get('form')->getInput('language'); ?>
						</div>
					</div>
				<?php else : ?>
				<input type="hidden" name="language" value="<?php echo $displayData->get('form')->getValue('language'); ?>" />
				<?php endif; ?>
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
				<?php if ($saveHistory) : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $displayData->get('form')->getLabel('version_note'); ?>
						</div>
						<div class="controls">
							<?php echo $displayData->get('form')->getInput('version_note'); ?>
						</div>
					</div>
				<?php endif; ?>
			</fieldset>
		</div>
