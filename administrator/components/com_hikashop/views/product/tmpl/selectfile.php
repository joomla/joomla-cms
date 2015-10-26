<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="submitbutton('addfile');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('product'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<table width="100%">
		<tr>
			<td class="key">
				<label for="file_name"><?php
					echo JText::_( 'HIKA_NAME' );
				?></label>
			</td>
			<td>
				<input type="text" name="data[file][file_name]" value="<?php echo $this->escape(@$this->element->file_name); ?>"/>
			</td>
		</tr>
		<tr>
<?php
	if(empty($this->element->file_path)){
?>
		<tr>
			<td class="key">
				<label for="files"><?php
					echo JText::_('HIKA_FILE_MODE');
				?></label>
			</td>
			<td><?php
				$values = array(
					JHTML::_('select.option', 'upload', JText::_('HIKA_FILE_MODE_UPLOAD')),
					JHTML::_('select.option', 'path', JText::_('HIKA_FILE_MODE_PATH'))
				);
				echo JHTML::_('hikaselect.genericlist', $values, "data[filemode]", 'class="inputbox" size="1" onchange="hikashop_switchmode(this);"', 'value', 'text', 'upload');
			?>
			<script type="text/javascript">
			function hikashop_switchmode(el) {
				var d = document, v = el.value, modes = ['upload','path'], e = null;
				for(var i = 0; i < modes.length; i++) {
					mode = modes[i];
					e = d.getElementById('hikashop_'+mode+'_section');
					if(!e) continue;
					if(v == mode) {
						e.style.display = '';
					} else {
						e.style.display = 'none';
					}
				}
			}
			</script>
			</td>
		</tr>
		<tr id="hikashop_upload_section">
			<td class="key">
				<label for="files"><?php
					echo JText::_('HIKA_FILE');
				?></label>
			</td>
			<td>
				<input type="file" name="files[]" size="30" />
				<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
			</td>
		</tr>
		<tr id="hikashop_path_section" style="display:none;">
			<td class="key">
				<label for="files"><?php
					echo JText::_('HIKA_PATH');
				?></label>
			</td>
			<td>
				<input type="text" name="data[filepath]" size="60" value=""/>
			</td>
		</tr>
<?php
	}else{
?>
			<td class="key">
				<label for="files"><?php
					echo JText::_('FILENAME');
				?></label>
			</td>
			<td>
				<input type="text" name="data[file][file_path]" size="60" value="<?php echo $this->element->file_path;?>"/>
			</td>
<?php
	}
?>
		</tr>
		<tr>
			<td class="key">
				<label for="file_limit"><?php
					echo JText::_('DOWNLOAD_NUMBER_LIMIT');
				?></label>
			</td>
			<td>
				<input type="text" name="data[file][file_limit]" value="<?php
					if(isset($this->element->file_limit)) {
						if($this->element->file_limit < 0)
							echo JText::_('UNLIMITED');
						else
							echo $this->element->file_limit;
					}
				?>"/><br/>
			</td>
		</tr>
		<tr>
			<td class="key"></td>
			<td>
				0: <?php echo JText::_('DEFAULT_PARAMS_FOR_PRODUCTS');?> (<?php echo $this->config->get('download_number_limit');?>)<br/>
				-1: <?php echo JText::_('UNLIMITED');?><br/>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="file_free_download"><?php
					echo JText::_('FREE_DOWNLOAD');
				?></label>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[file][file_free_download]" , '', @$this->element->file_free_download); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="file_description"><?php
					echo JText::_( 'HIKA_DESCRIPTION' );
				?></label>
			</td>
			<td>
				<textarea name="data[file][file_description]"><?php echo $this->escape(@$this->element->file_description); ?></textarea>
			</td>
		</tr>
	</table>
	<div class="clr"></div>
	<input type="hidden" name="data[file][file_type]" value="file" />
	<input type="hidden" name="data[file][file_ref_id]" value="<?php echo JRequest::getInt('product_id'); ?>" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->cid; ?>" />
	<input type="hidden" name="id" value="<?php echo JRequest::getInt('id');?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="selectfile" />
	<input type="hidden" name="ctrl" value="product" />
<?php if(JRequest::getInt('legacy', 0)) { ?>
	<input type="hidden" name="legacy" value="1" />
<?php } ?>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
