<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */

defined('JPATH_PLATFORM') or die;

// Load tooltips behavior
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

// Load JS message titles
JText::script('ERROR');
JText::script('WARNING');
JText::script('NOTICE');
JText::script('MESSAGE');
?>
<div class="ui-jce loading">
	<div class="donut"></div>
	<form action="<?php echo JRoute::_('index.php?option=com_jce&view=profile&layout=edit&id=' . (int) $this->item->id); ?>" id="adminForm" method="post" name="adminForm" class="form-validate">
		
	<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif; ?>
	
			<div class="row row-fluid">
					<!-- Begin Content -->
					<div class="span12 col-md-12">
						<?php echo JHtml::_('bootstrap.startTabSet', 'profile', array('active' => 'profile-setup'));?>
						<?php foreach(array('setup', 'features', 'editor', 'plugins') as $item) :?>
							<?php echo JHtml::_('bootstrap.addTab', 'profile', 'profile-' . $item, JText::_('WF_PROFILES_' . strtoupper($item), true));?>

							<div class="row-fluid">
								<?php echo $this->loadTemplate($item); ?>
							</div>

							<?php echo JHtml::_('bootstrap.endTab');?>
						<?php endforeach;?>

						<?php echo JHtml::_('bootstrap.endTabSet'); ?>
					</div>
					<!-- End Content -->
			</div>
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>