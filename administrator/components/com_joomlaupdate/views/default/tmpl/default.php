<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       2.5.4
 */

defined('_JEXEC') or die;
JHtml::_('behavior.switcher');
$ftpFieldsDisplay = $this->ftp['enabled'] ? '' : 'style = "display: none"';

?>
<?php if (is_null($this->updateInfo['object'])): ?>

<div class="joomla_no_update">
	<h3><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATES') ?></h3>
	<p>
		<?php echo JText::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATESNOTICE', JVERSION); ?>
	</p>
</div>
<?php else: ?>
<form action="index.php" method="post" id="adminForm">
	<input type="hidden" name="option" value="com_joomlaupdate" />
	<input type="hidden" name="task" value="update.download" />
	<div class="joomla_check">
		<div class="row-fluid">
			<div class="span12">
				<h3>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATEFOUND') ?>
				</h3>
				<hr class="hr-condensed" />
				<table class="table table-striped table-condensed">
					<tbody>
						<tr>
							<td>
								<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLED') ?>
							</td>
							<td>
								<?php echo $this->updateInfo['installed'] ?>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_LATEST') ?>
							</td>
							<td>
								<?php echo $this->updateInfo['latest'] ?>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PACKAGE') ?>
							</td>
							<td>
								<a href="<?php echo $this->updateInfo['object']->downloadurl->_data ?>">
									<?php echo $this->updateInfo['object']->downloadurl->_data ?>
								</a>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD') ?>
							</td>
							<td>
								<?php echo $this->methodSelect ?>
							</td>
						</tr>
						<tr id="row_ftp_hostname" <?php echo $ftpFieldsDisplay ?>>
							<td>
								<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_HOSTNAME') ?>
							</td>
							<td>
								<input type="text" name="ftp_host" value="<?php echo $this->ftp['host'] ?>" />
							</td>
						</tr>
						<tr id="row_ftp_port" <?php echo $ftpFieldsDisplay ?>>
							<td>
								<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PORT') ?>
							</td>
							<td>
								<input type="text" name="ftp_port" value="<?php echo $this->ftp['port'] ?>" />
							</td>
						</tr>
						<tr id="row_ftp_username" <?php echo $ftpFieldsDisplay ?>>
							<td>
								<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_USERNAME') ?>
							</td>
							<td>
								<input type="text" name="ftp_user" value="<?php echo $this->ftp['username'] ?>" />
							</td>
						</tr>
						<tr id="row_ftp_password" <?php echo $ftpFieldsDisplay ?>>
							<td>
								<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PASSWORD') ?>
							</td>
							<td>
								<input type="text" name="ftp_pass" value="<?php echo $this->ftp['password'] ?>" />
							</td>
						</tr>
						<tr id="row_ftp_directory" <?php echo $ftpFieldsDisplay ?>>
							<td>
								<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_DIRECTORY') ?>
							</td>
							<td>
								<input type="text" name="ftp_root" value="<?php echo $this->ftp['directory'] ?>" />
							</td>
						</tr>
						<tr>
							<td>&nbsp;
							</td>
							<td>
                            	<?php
									if(isset($this->extensions['not_compatible']) && !empty($this->extensions['not_compatible'])){
										$compatibility_css_tag = "not_compatible";
									}elseif(isset($this->extensions['na']) && !empty($this->extensions['na'])){
										$compatibility_css_tag = "no_xmltag";
									}else{
										$compatibility_css_tag = "";									
									}
								?>
								<button class="submit <?php echo $compatibility_css_tag; ?>" <?php echo $compatibility_css_tag != "" ? 'disabled="disabled"' : ''; ?> type="submit">
									<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLUPDATE') ?>
								</button>
                                <?php if($compatibility_css_tag != ""){ ?>
                                	<label class="checkbox"><input id="enable_update" type="checkbox" />
									<?php echo JText::_('COM_JOOMLAUPDATE_ENABLE_UPDATE') ?>
									<span id="dontrecom"><?php echo sprintf(JText::_('COM_JOOMLAUPDATE_UPDATE_WARNING_MSG'),$this->updateInfo['latest']); ?></span>
									</label>
									
                                <?php }?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</form>
<?php endif; ?>
<div class="download_message" style="display: none">
	<p>
	</p>
	<p class="nowarning">
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DOWNLOAD_IN_PROGRESS'); ?>
	</p>
	<div class="joomlaupdate_spinner"></div>
</div>
<?php if( (isset($this->options) && !empty($this->options)) || (isset($this->settings) && !empty($this->settings)) ): ?>
<div class="joomla_check">
	<div class="row-fluid">
		<div class="span12">
			<h1><?php echo sprintf(JText::_('COM_JOOMLAUPDATE_COMPATIBILITY_CHECK'),$this->updateInfo['latest']); ?></h1>
			<hr class="hr-condensed" />
		</div>
		<div class="span6">
			<br />
			<h3><?php echo JText::_('COM_JOOMLAUPDATE_PRE_CHECK'); ?></h3>
			<hr class="hr-condensed" />
			<table class="table table-striped table-condensed">
				<tbody>
					<?php if(isset($this->options) && !empty($this->options)): ?>
					<?php foreach ($this->options as $option): ?>
					<tr>
						<td class="item">
							<?php echo $option->label; ?>
						</td>
						<td>
							<span class="label label-<?php echo ($option->state) ? 'success' : 'important'; ?>">
							<?php echo JText::_(($option->state) ? 'JYES' : 'JNO'); ?>
							<?php if ($option->notice):?>
							<i class="icon-info-sign icon-white hasTooltip" title="<?php echo $option->notice; ?>"></i>
							<?php endif;?>
							</span>
						</td>
					</tr>
					<?php endforeach; ?>
					<?php else: ?>
					<tr>
						<td colspan="2"><?php echo JText::_('COM_JOOMLAUPDATE_NO_DATA_AVAILABLE'); ?></td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<!-- close span -->
		<div class="span6">
			<br />
			<h3><?php echo JText::_('COM_JOOMLAUPDATE_INSTL_PRECHECK_RECOMMENDED_SETTINGS_TITLE'); ?></h3>
			<hr class="hr-condensed" />
			<p class="install-text">
				<?php echo JText::_('COM_JOOMLAUPDATE_INSTL_PRECHECK_RECOMMENDED_SETTINGS_DESC'); ?>
			</p>
			<table class="table table-striped table-condensed">
				<thead>
					<tr>
						<th>
							<?php echo JText::_('COM_JOOMLAUPDATE_INSTL_PRECHECK_DIRECTIVE'); ?>
						</th>
						<th>
							<?php echo JText::_('COM_JOOMLAUPDATE_INSTL_PRECHECK_RECOMMENDED'); ?>
						</th>
						<th>
							<?php echo JText::_('COM_JOOMLAUPDATE_INSTL_PRECHECK_ACTUAL'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php if(isset($this->settings) && !empty($this->settings)): ?>
					<?php foreach ($this->settings as $setting) : ?>
					<tr>
						<td>
							<?php echo $setting->label; ?>
						</td>
						<td><span class="label label-success disabled">
							<?php echo JText::_(($setting->recommended) ? 'JON' : 'JOFF'); ?>
							</span></td>
						<td><span class="label label-<?php echo ($setting->state === $setting->recommended) ? 'success' : 'warning'; ?>">
							<?php echo JText::_(($setting->state) ? 'JON' : 'JOFF'); ?>
							</span></td>
					</tr>
					<?php endforeach; ?>
					<?php else: ?>
					<tr>
						<td colspan="3"><?php echo JText::_('COM_JOOMLAUPDATE_NO_DATA_AVAILABLE'); ?></td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<!-- close span -->
		
		<div class="span12">
			<br />
			<h3><?php echo JText::_('COM_JOOMLAUPDATE_EXTENSIONS_PRE_CHECK'); ?></h3>
			<hr class="hr-condensed" />
			<div id="submenu-box">
				<div class="submenu-box">
					<div class="submenu-pad">
						<ul id="submenu" class="information">
							<li>
								<a href="#" onclick="return false;" id="compatibile" class="active">
									<?php echo JText::_('COM_JOOMLAUPDATE_COMPATIBLE'); ?>
								</a>
							</li>
							<li>
								<a href="#" onclick="return false;" id="not_compatibile">
									<?php echo JText::_('COM_JOOMLAUPDATE_NOT_COMPATIBLE'); ?>
								</a>
							</li>
							<li>
								<a href="#" onclick="return false;" id="na">
									<?php echo JText::_('COM_JOOMLAUPDATE_NA'); ?>
								</a>
							</li>
						</ul>
						<div class="clr"></div>
					</div>
				</div>
				<div class="clr"></div>
			</div>
			<?php if(isset($this->extensions['compatible']) && isset($this->extensions['not_compatible']) && isset($this->extensions['na'])): ?>
                <div id="config-document">
                    <div id="page-compatibile" class="tab">
                        <div class="noshow">
                            <div class="width-100">
                                <table class="table table-striped table-condensed">
                                    <thead>
                                        <tr>
                                            <th  width="280px">
                                                <?php echo JText::_('COM_JOOMLAUPDATE_EXTENSION_NAME'); ?>
                                            </th>
                                            <th>
                                                <?php echo JText::_('COM_JOOMLAUPDATE_COMPATIBLE'); ?>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($this->extensions['compatible'])){ ?>
                                            <?php foreach($this->extensions['compatible'] as $extension){ ?>
                                            <tr>
                                                <td>
                                                    <?php echo $extension->name; ?>
                                                </td>
                                                <td><span class="label label-success">
                                                    <?php echo JText::_('JYES'); ?>
                                                    </span></td>
                                            </tr>
                                            <?php } ?>                                        
                                        <?php }else{ ?>
                                            <tr>
                                                <td colspan="2">
                                                    <?php echo JText::_('COM_JOOMLAUPDATE_NO_DATA_AVAILABLE'); ?>
                                                </td>
                                            </tr>                                    
                                        <?php } ?> 
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="page-not_compatibile" class="tab">
                        <div class="noshow">
                            <div class="width-100">
                                <table class="table table-striped table-condensed">
                                    <thead>
                                        <tr>
                                            <th width="280px">
                                                <?php echo JText::_('COM_JOOMLAUPDATE_EXTENSION_NAME'); ?>
                                            </th>
                                            <th>
                                                <?php echo JText::_('COM_JOOMLAUPDATE_NOT_COMPATIBLE'); ?>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($this->extensions['not_compatible'])){ ?>                                
                                            <?php foreach($this->extensions['not_compatible'] as $extension){ ?>
                                            <tr>
                                                <td>
                                                    <?php echo $extension->name; ?>
                                                </td>
                                                <td><span class="label label-important"><?php echo JText::_('JNO'); ?></span></td>
                                            </tr>
                                            <?php } ?>
                                        <?php }else{ ?>
                                            <tr>
                                                <td colspan="2">
                                                    <?php echo JText::_('COM_JOOMLAUPDATE_NO_DATA_AVAILABLE'); ?>
                                                </td>
                                            </tr>                                    
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="page-na" class="tab">
                        <div class="noshow">
                            <div class="width-100">
                                <table class="table table-striped table-condensed">
                                    <thead>
                                        <tr>
                                            <th width="280px">
                                                <?php echo JText::_('COM_JOOMLAUPDATE_EXTENSION_NAME'); ?>
                                            </th>
                                            <th>
                                                <?php echo JText::_('COM_JOOMLAUPDATE_NA'); ?>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($this->extensions['na'])){ ?>                                
                                            <?php foreach($this->extensions['na'] as $extension){ ?>
                                            <tr>
                                                <td>
                                                    <?php echo $extension->name; ?>
                                                </td>
                                                <td><span class="label label-warning">
                                                    <?php echo JText::_('COM_JOOMLAUPDATE_NA'); ?></span></td>
                                            </tr>
                                            <?php } ?>
                                        <?php }else{ ?>
                                            <tr>
                                                <td colspan="2">
                                                    <?php echo JText::_('COM_JOOMLAUPDATE_NO_DATA_AVAILABLE'); ?>
                                                </td>
                                            </tr>                                    
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php echo JText::_('COM_JOOMLAUPDATE_MARKED_DESCRIPTION_FIRST'); ?>
                <span class="label label-important"><?php echo JText::_('JNO'); ?></span> <?php echo JText::_('COM_JOOMLAUPDATE_OR'); ?> <span class="label label-warning"><?php echo JText::_('COM_JOOMLAUPDATE_MISSING_TAG_MARK'); ?></span>
                <?php echo JText::_('COM_JOOMLAUPDATE_MARKED_DESCRIPTION_LAST'); ?>
                <br />
                <br />
                <?php echo JText::_('COM_JOOMLAUPDATE_MARKED_DESCRIPTION_FIRST'); ?>
                <span class="label label-warning"><?php echo JText::_('COM_JOOMLAUPDATE_MISSING_TAG_MARK'); ?></span>
                <?php echo JText::_('COM_JOOMLAUPDATE_MISSING_TAG_DESCRIPTION'); ?>
			<?php endif;?>
		</div>
		<!-- close span -->
	</div>
	<!-- close row-fluid -->
</div>
<!-- close joomla_check -->

<?php endif;?>
