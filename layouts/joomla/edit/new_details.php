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
$title = $displayData->getForm()->getValue('title');
$published = $displayData->getForm()->getField('published');
?>
<div class="span2">
<h4><?php echo JText::_('JDETAILS');?></h4>
			<hr />
			<fieldset class="form-vertical">
				<?php if (empty($title)) : ?>
					<div class="control-group">
						<div class="controls">
							<?php echo $displayData->getForm()->getValue('name'); ?>
						</div>
					</div>
				<?php else : ?>
				<div class="control-group">
					<div class="controls">
						<?php echo $displayData->getForm()->getValue('title'); ?>
					</div>
				</div>
				<?php endif; ?>

				<div class="control-group">
					<div class="control-label">
						<?php echo $displayData->getForm()->getLabel('access'); ?>
					</div>
					<div class="controls">
						<?php echo $displayData->getForm()->getInput('access'); ?>
					</div>
				</div>
			</fieldset>
		</div>

