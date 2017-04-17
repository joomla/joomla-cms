<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_product_files_main" class="hikashop_product_files_main">
	<?php
	if (!empty ($this->element->files)) {
		$skip = true;
		foreach ($this->element->files as $file) {
			if ($file->file_free_download)
				$skip = false;
		}
		if (!$skip) {
			global $Itemid;
			$url_itemid='';
			if(!empty($Itemid)){
				$url_itemid='&Itemid='.$Itemid;
			}
		?>
			<fieldset class="hikashop_product_files_fieldset">
			<?php
			$html = array ();
			echo '<legend>' . JText :: _('DOWNLOADS') . '</legend>';
			foreach ($this->element->files as $file) {
				if (empty ($file->file_name)) {
					$file->file_name = $file->file_path;
				}
				$fileHtml = '';
				if (!empty ($file->file_free_download)) {
					$fileHtml = '<a class="hikashop_product_file_link" href="' . hikashop_completeLink('product&task=download&file_id=' . $file->file_id.$url_itemid) . '">' . $file->file_name . '</a><br/>';
				}
				$html[] = $fileHtml;
			}
			echo implode('<br/>', $html);
			?>
			</fieldset>
			<?php
		}
	}
	?>
</div>
