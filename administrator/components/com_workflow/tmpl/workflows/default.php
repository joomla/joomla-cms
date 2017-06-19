<?php
/**
 * Items Model for a Prove Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_prove
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */
defined('_JEXEC') or die('Restricted Access');

JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_workflow'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php if (empty($this->workflows)) : ?>
					<div class="alert alert-warning alert-no-items">
						<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else: ?>
					<table class="table table-striped" id="emailList">
						<thead><?php echo $this->loadTemplate('head');?></thead>
						<tbody class="js-draggable"><?php echo $this->loadTemplate('body');?></tbody>
					</table>
				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
