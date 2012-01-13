<?php
/**
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="step">
	<div class="far-right">
<?php if ($this->document->direction == 'ltr') : ?>
		<div class="button1-right"><div class="prev"><a href="index.php?view=filesystem" onclick="return Install.goToPage('filesystem');" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
		<div class="button1-left"><div class="next"><a href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
<?php elseif ($this->document->direction == 'rtl') : ?>
		<div class="button1-right"><div class="prev"><a href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
		<div class="button1-left"><div class="next"><a href="index.php?view=filesystem" onclick="return Install.goToPage('filesystem');" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
<?php endif; ?>
	</div>
	<h2><?php echo JText::_('INSTL_SITE'); ?></h2>
</div>

<div id="installer">
	<div class="m">
		<form action="index.php" method="post" id="adminForm" class="form-validate">
			<h3><?php echo JText::_('INSTL_SITE_NAME_TITLE'); ?></h3>
			<div class="install-text">
				<?php echo JText::_('INSTL_SITE_NAME_DESC'); ?>
			</div>
			<div class="install-body">
				<div class="m">
					<h4 class="title-smenu" title="<?php echo JText::_('INSTL_BASIC_SETTINGS'); ?>">
						<?php echo JText::_('INSTL_BASIC_SETTINGS'); ?>
					</h4>
					<div class="section-smenu">
						<table class="content2">
							<tr>
								<td class="item">
									<?php echo $this->form->getLabel('site_name'); ?>
								</td>
								<td>
									<?php echo $this->form->getInput('site_name'); ?>
								</td>
							</tr>
						</table>
					</div>

					<h4 class="title-smenu moofx-toggler" title="<?php echo JText::_('INSTL_SITE_META_ADVANCED_SETTINGS'); ?>">
						<a href="#"><?php echo JText::_('INSTL_SITE_META_ADVANCED_SETTINGS'); ?></a>
					</h4>
					<div class="section-smenu moofx-slider">
							<table class="content2">
								<tr>
									<td title="<?php echo JText::_('INSTL_SITE_METADESC_TITLE_LABEL'); ?>">
										<?php echo $this->form->getLabel('site_metadesc'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('site_metadesc'); ?>
									</td>
								</tr>
								<tr>
									<td title="<?php echo JText::_('INSTL_SITE_METAKEYS_TITLE_LABEL'); ?>">
										<?php echo $this->form->getLabel('site_metakeys'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('site_metakeys'); ?>
									</td>
								</tr>
								<tr>
									<td title="<?php echo JText::_('INSTL_SITE_OFFLINE_TITLE_LABEL'); ?>">
										<?php echo $this->form->getLabel('site_offline'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('site_offline'); ?>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>

				<div class="newsection"></div>

				<h4><?php echo JText::_('INSTL_SITE_CONF_TITLE'); ?></h4>
				<div class="install-text">
					<?php echo JText::_('INSTL_SITE_CONF_DESC'); ?>
				</div>
				<div class="install-body">
					<div class="m">
						<fieldset>
							<table class="content2">
								<tr>
									<td class="item">
										<?php echo $this->form->getLabel('admin_email'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('admin_email'); ?>
									</td>
								</tr>
								<tr>
									<td class="item">
										<?php echo $this->form->getLabel('admin_user'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('admin_user'); ?>
									</td>
								</tr>
								<tr>
									<td class="item">
										<?php echo $this->form->getLabel('admin_password'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('admin_password'); ?>
									</td>
								</tr>
								<tr>
									<td class="item">
										<?php echo $this->form->getLabel('admin_password2'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('admin_password2'); ?>
									</td>
								</tr>
							</table>
						</fieldset>
					</div>
					<input type="hidden" name="task" value="setup.saveconfig" />
					<?php echo JHtml::_('form.token'); ?>
					<?php echo $this->form->getInput('sample_installed'); ?>
				</div>
			</form>

			<div class="clr"></div>

			<form enctype="multipart/form-data" action="index.php" method="post" id="filename">
				<h3><?php echo JText::_('INSTL_SITE_LOAD_SAMPLE_TITLE'); ?></h3>
				<div class="install-text">
					<p><?php echo JText::_('INSTL_SITE_LOAD_SAMPLE_DESC1'); ?></p>
					<p><?php echo JText::_('INSTL_SITE_LOAD_SAMPLE_DESC2'); ?></p>
					<p><?php echo JText::_('INSTL_SITE_LOAD_SAMPLE_DESC3'); ?></p>
					<p><?php echo JText::_('INSTL_SITE_LOAD_SAMPLE_DESC4'); ?></p>
					<p><?php echo JText::_('INSTL_SITE_LOAD_SAMPLE_DESC8'); ?></p>
				</div>
				<div class="install-body">
					<div class="m">
						<fieldset>
							<table class="content2 sample-data">
								<tr>
									<td><?php echo $this->form->getLabel('sample_file'); ?></td>
									<td><?php echo $this->form->getInput('sample_file'); ?></td>
								</tr>
								<tr>
									<td colspan="2">
										<span id="theDefault"><input class="button" type="button" name="instDefault" value="<?php echo JText::_('INSTL_SITE_INSTALL_SAMPLE_LABEL'); ?>" onclick="Install.sampleData(this, <?php echo $this->form->getField('sample_file')->id;?>);"/></span>
									</td>
								</tr>
								<tr>
									<td>&#160;</td>
									<td>
										<em><?php echo JText::_('INSTL_SITE_INSTALL_SAMPLE_DESC'); ?></em>
									</td>
								</tr>
							</table>
						</fieldset>
						<div class="message inlineError" id="theDefaultError" style="display: none">
							<dl>
								<dt class="error"><?php echo JText::_('JERROR'); ?></dt>
								<dd id="theDefaultErrorMessage"></dd>
							</dl>
						</div>
					</div>
					<?php echo $this->form->getInput('type'); ?>
					<?php echo JHtml::_('form.token'); ?>
				</div>
			</form>

		<div class="clr"></div>
	</div>
</div>
