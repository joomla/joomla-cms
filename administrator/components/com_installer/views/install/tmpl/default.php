<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// MooTools is loaded for B/C for extensions generating JavaScript in their install scripts, this call will be removed at 4.0
JHtml::_('behavior.framework', true);
JHtml::_('bootstrap.tooltip');
?>
<script type="text/javascript">
	// Add spindle-wheel for installations:
	jQuery(document).ready(function($) {
		var outerDiv = $('#installer-install');

		$('<div id="loading"></div>')
			.css("background", "rgba(255, 255, 255, .8) url('../media/jui/img/ajax-loader.gif') 50% 15% no-repeat")
			.css("top", outerDiv.position().top - $(window).scrollTop())
			.css("left", outerDiv.position().left - $(window).scrollLeft())
			.css("width", outerDiv.width())
			.css("height", outerDiv.height())
			.css("position", "fixed")
			.css("opacity", "0.80")
			.css("-ms-filter", "progid:DXImageTransform.Microsoft.Alpha(Opacity = 80)")
			.css("filter", "alpha(opacity = 80)")
			.css("display", "none")
			.appendTo(outerDiv);
	});

</script>

<div id="installer-install" class="clearfix">
	<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_installer&view=install');?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
		<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
			<?php else : ?>
			<div id="j-main-container">
				<?php endif;?>

				<!-- Render messages set by extension install scripts here -->
				<?php if ($this->showMessage) : ?>
					<?php echo $this->loadTemplate('message'); ?>
				<?php elseif ($this->showJedAndWebInstaller) : ?>

				<?php endif; ?>

				<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => '--')); ?>

				<?php JEventDispatcher::getInstance()->trigger('onInstallerViewBeforeFirstTab', array()); ?>
				<!-- Extension fieldset of the plugin installer urlFolderInstaller-->
				<?php JEventDispatcher::getInstance()->trigger('onInstallerViewAfterLastTab', array()); ?>

				<?php if ($this->ftp) : ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'ftp', JText::_('COM_INSTALLER_MSG_DESCFTPTITLE', true)); ?>
					<?php echo $this->loadTemplate('ftp'); ?>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php endif; ?>

				<input type="hidden" name="type" value="" />
				<input type="hidden" name="installtype" value="upload" />
				<input type="hidden" name="task" value="install.install" />
				<?php echo JHtml::_('form.token'); ?>

				<?php echo JHtml::_('bootstrap.endTabSet'); ?>

	</form>
</div>
