<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php echo JHtml::_('installation.stepbar'); ?>
<div id="installer">
	<div id="collapseSample" class="collapse">
		<form enctype="multipart/form-data" action="index.php" method="post" class="form-horizontal" id="filename">
			<h3>
				<?php echo JText::_('INSTL_SITE_SAMPLE_LABEL'); ?>
			</h3>
			<hr class="hr-condensed" />
			<?php echo $this->form->getInput('sample_file'); ?>
			<hr class="hr-condensed" />
			 <div class="message inlineError" id="theDefaultError" style="display: none">
				<dl>
					<dt class="error"><?php echo JText::_('JERROR'); ?></dt>
					<dd id="theDefaultErrorMessage"></dd>
				</dl>
			 </div>
			<div class="alert">
				<h4 class="alert-heading"><?php echo JText::_('INSTL_SITE_INSTALL_SAMPLE_LABEL'); ?></h4>
				<p><?php echo JText::_('INSTL_SITE_LOAD_SAMPLE_DESC1'); ?>
				</p>
				<span id="theDefault">
					<button class="btn btn-warning" name="instDefault" onclick="Install.sampleData(this, <?php echo $this->form->getField('sample_file')->id;?>);"><i class="icon-upload icon-white"></i> <?php echo JText::_('INSTL_SITE_INSTALL_SAMPLE_LABEL'); ?></button>
				</span>
			</div>
			<?php echo $this->form->getInput('type'); ?>
			<?php echo JHtml::_('form.token'); ?>
		</form>
		<hr class="hr-condensed" />
	</div>
	<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
		<div class="btn-toolbar">
			<div class="btn-group">
				<a class="btn btn-warning" href="#collapseSample" data-toggle="collapse"><i class="icon-upload icon-white"></i> <?php echo JText::_('INSTL_SITE_INSTALL_SAMPLE_LABEL'); ?></a>
			</div>
			<div class="btn-group pull-right">
				<a class="btn" href="index.php?view=filesystem" onclick="return Install.goToPage('filesystem');" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><i class="icon-arrow-left"></i> <?php echo JText::_('JPrevious'); ?></a>
				<a  class="btn btn-primary" href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNext'); ?>"><i class="icon-arrow-right icon-white"></i> <?php echo JText::_('JNext'); ?></a>
			</div>
		</div>
		<h3><?php echo JText::_('INSTL_SITE'); ?></h3>
		<hr class="hr-condensed" />
		<div class="row-fluid">
			<div class="span6">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('site_name'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('site_name'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('site_metadesc'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('site_metadesc'); ?>
						<p class="help-block">
							<?php echo JText::_('INSTL_SITE_METADESC_TITLE_LABEL'); ?>
						</p>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('site_metakeys'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('site_metakeys'); ?>
						<p class="help-block">
							<?php echo JText::_('INSTL_SITE_METAKEYS_TITLE_LABEL'); ?>
						</p>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('site_offline'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('site_offline'); ?>
						<p class="help-block">
							<?php echo JText::_('INSTL_SITE_OFFLINE_TITLE_LABEL'); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="span6">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('admin_email'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('admin_email'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('admin_user'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('admin_user'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('admin_password'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('admin_password'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('admin_password2'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('admin_password2'); ?>
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" name="task" value="setup.saveconfig" />
		<?php echo JHtml::_('form.token'); ?>
		<?php echo $this->form->getInput('sample_installed'); ?>
	</form>
</div>
