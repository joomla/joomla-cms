<?php
/**
 * @package     Jokte.Administrator
 * @subpackage  com_jokteupdate
 * @copyright   Copyleft 2012-2014 Comunidad Juuntos & Jokte.org
 * @license     GNU General Public License version 3 or later; see LICENSE.txt
 * @since       1.1.9
 */

defined('_JEXEC') or die;

$ftpFieldsDisplay = $this->ftp['enabled'] ? '' : 'style = "display: none"';
?>
<?php if (is_null($this->updateInfo['object'])): ?>

<fieldset>
	<legend>
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATES') ?>
	</legend>
	<p>
		<?php echo JText::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATESNOTICE', VJOKTE); ?>
	</p>
</fieldset>

<?php else: ?>

<form action="index.php" method="post" id="adminForm">
<input type="hidden" name="option" value="com_jokteupdate" />
<input type="hidden" name="task" value="update.download" />

<fieldset>
	<legend>
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATEFOUND') ?>
	</legend>

	<table class="adminlist">
		<tbody>
			<tr class="row0">
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLED') ?>
				</td>
				<td>
					<?php echo $this->updateInfo['installed'] ?>
				</td>
			</tr>
			<tr class="row1">
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_LATEST') ?>
				</td>
				<td>
					<?php echo $this->updateInfo['latest'] ?>
				</td>
			</tr>
			<tr class="row0">
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PACKAGE') ?>
				</td>
				<td>
					<a href="<?php echo $this->updateInfo['object']->downloadurl->_data ?>">
						<?php echo $this->updateInfo['object']->downloadurl->_data ?>
					</a>					
				</td>
			</tr>
			<tr class="row1">
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DESCRIPTION') ?>
				</td>
				<td>
					<span style="font-variant: small-caps; font-weight:bold;font-size:120%">
						<?php echo $this->updateMisc->description ?>
					</span>
				</td>
			</tr>
			<tr class="row0">
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_RANGETYPE') ?>
				</td>
				<?php 
					switch ($this->updateMisc->rangetype)
					{
						case '1':
							$desc = '<span style="color:green">'.JText::_('JOKTEUPDATE_NUEVA_VERSION').'</span>';
							break;
						case '2':
							$desc = '<span style="color:organge">'.JText::_('JOKTEUPDATE_PATCH_MINOR_FAILS').'</span>';
							break;
						case '3':
							$desc = '<span style="color:blue">'.JText::_('JOKTEUPDATE_PATCH_MAYOR_FAILS').'</span>';
							break;
						case '4':
							$desc = '<span style="color:red">'.JText::_('JOKTEUPDATE_PATCH_SECURITY').'</span>';
							break;
						default:
							$desc = '<span style="color:#808080">'.JText::_('JOKTEUPDATE_NO_APLICABLE').'</span>';
					}
				?>
				<td>
					<?php echo $desc ?>
				</td>
			</tr>
			<tr class="row1">
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_RANGEURL') ?>
				</td>
				<td>
					<?php						
						$dom = new DOMDocument();
						$dom->loadHTMLFile($this->updateMisc->rangeurl);
						$title =  $dom->getElementsByTagName('h1'); 
						$date = $dom->getElementsByTagName('h2'); 
						$list = $dom->getElementsByTagName('li');
						$nl = 1;
					?>
					<p>
						<?php echo JText::_('UPDATE_INFORMATION_TITLE').': ' ?>
						<b><?php echo $title->item(0)->nodeValue ?></b><br />
						<?php echo JText::_('UPDATE_INFORMATION_DATE').': ' ?>
						<b><?php echo $date->item(0)->nodeValue ?></b><br />
						<?php
							foreach ($list as $element):
								echo '#'.$nl.'- <i>'.$element->nodeValue.'</i><br/>';
								$nl++;
							endforeach;
						?>
					</p>
				</td>
			</tr>
			<tr class="row1">
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD') ?>
				</td>
				<td>
					<?php echo $this->methodSelect ?>
				</td>
			</tr>
			<tr class="row0" id="row_ftp_hostname" <?php echo $ftpFieldsDisplay ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_HOSTNAME') ?>
				</td>
				<td>
					<input type="text" name="ftp_host" value="<?php echo $this->ftp['host'] ?>" />
				</td>
			</tr>
			<tr class="row1" id="row_ftp_port" <?php echo $ftpFieldsDisplay ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PORT') ?>
				</td>
				<td>
					<input type="text" name="ftp_port" value="<?php echo $this->ftp['port'] ?>" />
				</td>
			</tr>
			<tr class="row0" id="row_ftp_username" <?php echo $ftpFieldsDisplay ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_USERNAME') ?>
				</td>
				<td>
					<input type="text" name="ftp_user" value="<?php echo $this->ftp['username'] ?>" />
				</td>
			</tr>
			<tr class="row1" id="row_ftp_password" <?php echo $ftpFieldsDisplay ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PASSWORD') ?>
				</td>
				<td>
					<input type="text" name="ftp_pass" value="<?php echo $this->ftp['password'] ?>" />
				</td>
			</tr>
			<tr class="row0" id="row_ftp_directory" <?php echo $ftpFieldsDisplay ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_DIRECTORY') ?>
				</td>
				<td>
					<input type="text" name="ftp_root" value="<?php echo $this->ftp['directory'] ?>" />
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td>
					&nbsp;
				</td>
				<td>
					<button class="submit" type="submit">
						<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLUPDATE') ?>
					</button>
				</td>
			</tr>
		</tfoot>
	</table>
</fieldset>

</form>
<?php endif; ?>

<div class="download_message" style="display: none">
	<p></p>
	<p class="nowarning"> <?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DOWNLOAD_IN_PROGRESS'); ?></p>
	<div class="joomlaupdate_spinner" />
</div>
