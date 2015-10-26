<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="config_plugins">
	<fieldset class="adminform">
		<legend><?php echo JText::_('PLUGINS') ?></legend>
		<table class="adminlist table table-striped table-hover" cellpadding="1">
			<thead>
				<tr>
					<th class="title titlenum"><?php
						echo JText::_('HIKA_NUM');
					?></th>
					<th class="title"><?php
						echo JText::_('HIKA_NAME');
					?></th>
					<th class="title titletoggle"><?php
						echo JText::_('HIKA_ENABLED');
					?></th>
					<th class="title titleid"><?php
						echo JText::_('ID');
					?></th>
				</tr>
			</thead>
			<tbody>
<?php
	$k = 0;
	if(!HIKASHOP_J16) {
		$publishedid = 'published-';
		$url = 'index.php?option=com_plugins&amp;view=plugin&amp;client=site&amp;task=edit&amp;cid[]=';
	}else{
		$publishedid = 'enabled-';
		$url = 'index.php?option=com_plugins&amp;task=plugin.edit&amp;extension_id=';
	}
	foreach($this->plugins as $i => &$row) {
?>
				<tr class="row<?php echo $k; ?>">
					<td align="center"><?php
						echo $i + 1
					?></td>
					<td>
						<a target="_blank" href="<?php echo $url.$row->id; ?>"><?php echo $row->name; ?></a>
					</td>
					<td align="center"><?php
						if($this->manage){
?>
						<span id="<?php echo $publishedid.$row->id; ?>" class="loading"><?php echo $this->toggleClass->toggle($publishedid.$row->id, $row->published, 'plugins') ?></span>
<?php
						} else {
							echo $this->toggleClass->display('activate', $row->published);
						}
					?></td>
					<td align="center"><?php
						echo $row->id;
					?></td>
				</tr>
<?php
		$k = 1-$k;
	}
	unset($row);
?>
			</tbody>
		</table>
	</fieldset>
</div>
