<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');
?>

<div class="container-fluid">
	<div class="row-fluid">
		<div class="control-group span6">
			<div class="controls">
				<?php echo JLayoutHelper::render('joomla.html.batch.language', array()); ?>
			</div>
		</div>
		<div class="control-group span6">
			<div class="controls">
				<?php echo JLayoutHelper::render('joomla.html.batch.access', array()); ?>
			</div>
		</div>
	</div>
</div>
