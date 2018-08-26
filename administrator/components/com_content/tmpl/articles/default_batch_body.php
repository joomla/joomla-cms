<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

$published = $this->state->get('filter.published');

$user = \Joomla\CMS\Factory::getUser();
?>

<div class="container">
	<div class="row">
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo HTMLHelper::_('batch.language'); ?>
			</div>
		</div>
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo HTMLHelper::_('batch.access'); ?>
			</div>
		</div>
	</div>
	<div class="row">
		<?php if ($published >= 0) : ?>
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo HTMLHelper::_('batch.item', 'com_content'); ?>
			</div>
		</div>
		<?php endif; ?>
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo HTMLHelper::_('batch.tag'); ?>
			</div>
		</div>
		<?php if ($user->authorise('core.admin', 'com_content')) : ?>
        <div class="form-group col-md-6">
            <div class="controls">
				<?php
				$displayData = ['extension' => 'com_content'];
				echo LayoutHelper::render('joomla.html.batch.workflowstage', $displayData); ?>
            </div>
        </div>
		<?php endif; ?>
	</div>
</div>
